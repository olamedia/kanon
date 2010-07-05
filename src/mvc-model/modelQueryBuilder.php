<?php
class modelQueryBuilder{
	/**
	 *
	 * @var modelCollection
	 */
	protected $_storageSource = null;
	protected $_joinedTables = array(); // all tables joined by user
	protected $_selectedTables = array(); // all tables in select
	protected $_selected = array();
	protected $_limitFrom = 0;
	protected $_limit = null;

	protected $_joinOptions = array();
	protected $_joinType = array();
	protected $_joinOn = array();
	protected $_joinWhere = array();
	protected $_joinWith = array();
	protected $_join = array();
	protected $_where = array();
	protected $_having = array();
	protected $_order = array();
	protected $_group = array();
	protected $_filters = array();
	public function chunk($chunkLength){
		// TODO
	}
	public function addFilter($filter){
		$this->_filters[] = $filter;
		return $this;
	}
	/**
	 * @return modelStorage
	 */
	public function getStorage(){
		if ($this->_storageSource === null) return false;
		return  $this->_storageSource->getStorage();
	}
	public function e($unescapedString){
		return $this->getStorage()->quote($unescapedString);
	}
	/**
	 * use join table1 with table2
	 * @param modelCollection $table1
	 * @param modelCollection $table2
	 */
	public function joinWith($table1, $table2){
		$this->_joinWith[$table1->getUniqueId()] = $table2->getUniqueId();
		return $this;
	}
	/**
	 * @return modelQueryBuilder
	 */
	public function &select(){
		$args = func_get_args();
		if (!count($args)) return $this;
		foreach ($args as $arg){
			if ($arg instanceof modelAggregation){
				$fields = $arg->getArguments();
				foreach ($fields as $field){
					$a["$field"] = $field;
				}
				$field = $arg;
				$this->_selected[] = $arg;
			}elseif($arg instanceof modelExpression){
				$this->where($arg);
				//}elseif(is_integer($arg)){
				//$this->limit($arg);
			}elseif(is_array($arg)){
				$this->_selected[] = $arg;
			}else{
				$table = null;
				$field = null;
				if ($arg instanceof modelCollection){
					$table = $arg;
				}
				if ($arg instanceof modelField){
					$table = $arg->getCollection();
					$field = $arg;
				}

				if ($this->_storageSource === null) $this->_storageSource = $table;
				$a = array();
				if ($arg instanceof modelCollection){
					foreach ($table->getFieldNames() as $fieldName){
						$field = $table[$fieldName];
						$a["$field"] = $field;
					}
				}
				if ($arg instanceof modelField){
					$a["$field"] = $field;
				}
				if ($table instanceof modelCollection){
					$this->_selected[] = array($table, $a);
					$this->_selectedTables[$table->getUniqueId()] = $table;
					$this->_joinedTables[$table->getUniqueId()] = $table;
				}else{

				}
			}
		}
		//var_dump($this->_selected);
		return $this;
	}
	protected function _constructJoins(){
		$this->getStorage()->registerForeignKeys();
		$this->_join = array(); // reset joins
		$rootTable = $this->_storageSource;
		$rootTableId = $rootTable->getUniqueId();
		$alreadyJoined = array();
		$alreadyJoined[$rootTableId] = true;
		$allJoins = array(); // [target][source] = joinId
		$joinContent = array();
		$joinId = 0;
		if (count($this->_joinWith)){
			foreach ($this->_joinWith as $tableId1 => $tableId2){
				$table1 = modelCollection::getInstanceById($tableId1);
				$table2 = modelCollection::getInstanceById($tableId2);
				$joins = modelStorage::getIndirectTablesJoins($table1, $table2, $this->_joinType, $this->_joinWhere);
				echo '<div><b>'.$table1->getTableName().' JOIN WITH '.$table2->getTableName().'</b></div>';
				if ($joins !== false){
					$joinId++;
					$joinContent[$joinId] = $joins;
					//$allJoins[$tableId1][$tableId2] = $joinId;
					$allJoins[$tableId2][$tableId1] = $joinId;
					$allJoins[$tableId1][$tableId2] = $joinId;
					//foreach ($joins as $uid => $joinString){
					//$alreadyJoined[$tableId1] = true;
					//$alreadyJoined[$tableId2] = true;
					//}
				}
			}
		}
		foreach ($this->_joinedTables as $targetId => $table2){
			if ($rootTableId !== $targetId){ // && (!$alreadyJoined[$targetId]
				$min = null;
				$minJoins = false;
				// Trying to join table
				//echo '<div><b>'.$sourceTable->getTableName().' JOIN '.$table2->getTableName().'</b></div>';
				foreach ($this->_joinedTables as $sourceId => $table1){
					if ($sourceId !== $targetId){
						$joins = modelStorage::getIndirectTablesJoins($table1, $table2, $this->_joinType, $this->_joinWhere);
						if (($joins !== false) && (($min === null) || (count($joins) < $min))){
							$minJoins = $joins;
							$min = count($joins);
						}
					}
				}
				if ($minJoins !== false){
					$joinId++;
					$joinContent[$joinId] = $minJoins;
					if (!isset($allJoins[$targetId][$sourceId])){
						$allJoins[$targetId][$sourceId] = $joinId;
					}
					if (!isset($allJoins[$sourceId][$targetId])){
						$allJoins[$sourceId][$targetId] = $joinId;
					}
					//$alreadyJoined[$targetId] = true;
					//$alreadyJoined[$targetId] = true;
				}
			}
		}
		$this->_orderJoins($allJoins, $joinContent, $sourceTableUid);
		//var_dump($this->_join);
	}
	protected function _orderJoins($allJoins, $joinContent, $id){
		if (isset($allJoins[$id])){
			foreach ($allJoins[$id] as $id2 => $joinId){
				$joins = $joinContent[$joinId];
				foreach ($joins as $uid => $joinString){
					$this->_join[$uid] = $joinString;
				}
				unset($allJoins[$id2]);
				foreach ($allJoins as $xid => $b){
					unset($allJoins[$xid][$id2]);
				}
				unset($joinContent[$joinId]);
				$this->_orderJoins($allJoins, $joinContent, $id);
			}
		}
	}
	/**
	 * @return modelQueryBuilder
	 */
	public function &join($table2, $joinType = 'INNER', $on = ''){
		//echo $joinType;
		$this->_joinType[$table2->getUniqueId()] = $joinType;
		if (strlen($on)){
			$this->_joinOn[$table2->getUniqueId()] = $on;
		}
		$this->_joinedTables[$table2->getUniqueId()] = $table2;
		return $this;
	}
	/**
	 * @return modelQueryBuilder
	 */
	public function &autoJoin($table2){
		$joinType = isset($this->_joinType[$table2->getUniqueId()])?$this->_joinType[$table2->getUniqueId()]:'INNER';
		//if (!isset($this->_joinType[$table2->getUniqueId()]))
		//$this->_joinType[$table2->getUniqueId()] = $joinType;
		$this->_joinedTables[$table2->getUniqueId()] = $table2;
		return $this;
	}
	/**
	 * @return modelQueryBuilder
	 */
	public function &leftJoin($table2, $on = ''){
		return $this->join($table2, 'LEFT', $on);
	}
	/**
	 * @return modelQueryBuilder
	 */
	public function &innerJoin($table2, $on = ''){
		return $this->join($table2, 'INNER', $on);
	}
	/**
	 * @return modelQueryBuilder
	 */
	public function &limit(){
		$args = func_get_args();
		switch (count($args)){
			case 1:
				$this->_limit = $args[0];
				$this->_limitFrom = 0;
				break;
			case 2:
				$this->_limit = $args[1];
				$this->_limitFrom = $args[0];
				break;
			default:
				$this->_limit = null;
				$this->_limitFrom = 0;
		}
		return $this;
	}
	/**
	 *
	 * @param modelField $field
	 */
	protected function _fixJoinField(&$field){
		$collection = modelCollection::getInstanceById($field->getCollectionId());
		$foreignKeys = $collection->getForeignKeys();
		$fieldName = $field->getName();
		if (isset($foreignKeys[$fieldName])){
			foreach ($foreignKeys[$fieldName] as $foreignModel => $foreignKey){
				foreach ($this->_joinedTables as $table){
					/** modelCollection $table */
					if ($foreignModel == $table->getModelClass()){
						$field = $table->{$foreignKey};
						return;
					}
				}
			}
		}
	}
	protected function _joinCondition(&$condition){
		if ($condition instanceof modelExpression){
			$left = $condition->getLeft();
			if ($left instanceof modelField){
				$this->_fixJoinField($left);
				$condition->setLeft($left);
				$collection = modelCollection::getInstanceById($left->getCollectionId());
				$this->autoJoin($collection);
			}
			$right = $condition->getRight();
			if ($right instanceof modelField){
				$this->_fixJoinField($right);
				$condition->setRight($right);
				$collection = modelCollection::getInstanceById($right->getCollectionId());
				$this->autoJoin($collection);
			}
		}
	}
	/**
	 * @return modelQueryBuilder
	 */
	public function &where(){
		$conditions = func_num_args()?func_get_args():array();
		foreach ($conditions as $condition){
			$this->_joinCondition($condition);
			$this->_where[] = $condition;
		}
		return $this;
	}
	/**
	 * @return modelQueryBuilder
	 */
	public function &having($condition){
		$this->_joinCondition($condition);
		$this->_having[] = $condition;
		return $this;
	}
	/**
	 * @return modelQueryBuilder
	 */
	public function &asc($field){
		$this->_order[] = $field.' ASC';
		if ($field instanceof modelField){
			$this->autoJoin($field->getCollection());
		}
		return $this;
	}
	/**
	 * @return modelQueryBuilder
	 */
	public function &desc($field){
		$this->_order[] = $field.' DESC';
		if ($field instanceof modelField){
			$collection = modelCollection::getInstanceById($field->getCollectionId());
			$this->autoJoin($collection);
		}
		return $this;
	}
	/**
	 * @return modelQueryBuilder
	 */
	public function &orderBy($orderString){
		$this->_order[] = $orderString;
		return $this;
	}
	/**
	 * @return modelQueryBuilder
	 */
	public function &groupBy($groupString){
		$this->_group[] = $groupString;
		return $this;
	}
	protected function getWhatSql(){
		$wa = array();
		foreach ($this->_selected as $sa){
			if (is_array($sa)){
				list($table, $fields) = $sa;
				if ($table instanceof modelCollection){
					foreach ($fields as $fid => $field){
						$wa[] = $field." as ".$field->getUniqueId();
					}
				}else{
					list($k,$v) = each($sa);
					$wa[] = "$v as $k";
				}
			}else{
				list($k,$v) = each($sa);
				$wa[] = "$v as $k";
			}
		}
		return implode(", ", $wa);
	}
	protected function getJoinSql(){
		$this->_constructJoins();
		return implode("", $this->_join);
	}
	protected function getFromSql(){
		reset($this->_selected);
		$sa = current($this->_selected);
		list($table, $fields) = $sa;
		return " FROM ".$table->getTableName()." as ".$table;
	}
	protected function getOrderSql(){
		if (count($this->_order)){
			return " ORDER BY ".implode(", ", $this->_order);
		}
		return '';
	}
	protected function getWhereSql(){
		if (count($this->_where)){
			$wa = array();
			foreach ($this->_where as $k => $condition){
				if (strval($condition) != ''){
					$wa[] = $condition;
				}
			}
			return " WHERE ".implode(" AND ", $wa);
		}
		return '';
	}
	protected function getHavingSql(){
		if (count($this->_having)){
			return " HAVING ".implode(" AND ", $this->_having);
		}
		return '';
	}
	protected function getGroupBySql(){
		if (count($this->_group)){
			return " GROUP BY ".implode(", ", $this->_group);
		}
		return '';
	}
	protected function getLimitSql(){
		if ($this->_limitFrom){
			if ($this->_limit){
				return " LIMIT $this->_limitFrom, $this->_limit";
			}else{
				return "";//,18446744073709551615;
			}
		}else{
			if ($this->_limit){
				return " LIMIT $this->_limit";
			}else{
				return "";//,18446744073709551615;
			}
		}
	}
	protected function applyFilters(){
		foreach ($this->_joinedTables as $tableUid => $table){
			$filters = $table->getFilters();
			if (count($filters)){
				if ($this->_storageSource->getUniqueId() == $tableUid){
					foreach ($filters as $filter){
						$this->where($filter);
					}
				}else{
					foreach ($filters as $filter){
						if ($filter instanceof modelExpression){
							$left = $filter->getLeft();
							$right = $filter->getRight();
							if ($left instanceof modelField){
								$this->_joinWhere[$left->getCollectionId()][] = $filter;
							}elseif($right instanceof modelField){
								$this->_joinWhere[$right->getCollectionId()][] = $filter;
							}else{
								$this->where($filter);
							}
						}
					}
				}
			}
		}
	}
	public function getSqlHtml(){
		$sql = htmlspecialchars($this->getSql());
		$m = array("FROM","INNER JOIN","WHERE","AND","OR","GROUP BY");
		$pattern = '#( '.implode(" | ", $m).' )#imsu';
		//echo $pattern;
		$sql = preg_replace($pattern, '<br /><b style="color: red;">\1</b> ', $sql);
		return '<div style="padding: 3px;" onClick="$(this).children(\'div\').show();"><b style="color: #24659B">SQL</b><div style="display: none; background: #FFE5BF; padding: 7px;">'.($sql).'</div></div>';
	}
	public function &getSql(){
		$this->getStorage()->registerForeignKeys();
		$this->applyFilters();
		$sql = "SELECT ".$this->getWhatSql()
		.$this->getFromSql()
		// join
		.$this->getJoinSql()
		.$this->getWhereSql()
		.$this->getGroupBySql()
		.$this->getHavingSql()
		.$this->getOrderSql()
		.$this->getLimitSql();
		//echo '<b>'.$sql.'</b><br />';
		return $sql;
	}
	public function &getCountSql(){
		$this->getStorage()->registerForeignKeys();
		$this->applyFilters();
		$sql = "SELECT COUNT(*)"
		.$this->getFromSql()
		// join
		.$this->getJoinSql()
		.$this->getWhereSql()
		.$this->getGroupBySql()
		.$this->getHavingSql()
		.$this->getOrderSql()
		.$this->getLimitSql();
		return $sql;
	}
	public function __toString(){
		return $this->getSql();
	}

}