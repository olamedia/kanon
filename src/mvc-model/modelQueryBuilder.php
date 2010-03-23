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
	protected $_join = array();
	protected $_where = array();
	protected $_having = array();
	protected $_order = array();
	protected $_group = array();
	protected $_filters = array();
	public function addFilter($filter){
		$this->_filters[] = $filter;
		return $this;
	}
	public function getStorage(){
		
	}
	public function e($unescapedString){
		return $this->getStorage()->quote($unescapedString);
	}
	/**
	 * @return modelQueryBuilder
	 */
	public function select(){
		$args = func_get_args();
		if (!count($args)) return $this;
		foreach ($args as $arg){
			$table = null;
			$field = null;
			if ($arg instanceof modelCollection){
				$table = $arg;
			}
			if ($arg instanceof zenMysqlField){
				$table = $arg->getTable();
				$field = $arg;
			}
			if ($this->_storageSource === null) $this->_storageSource = $table;
			$a = array();
			if ($arg instanceof modelCollection){
				foreach ($table->getFieldNames() as $fieldName){
					$field = $table[$fieldName];
					$fid = $field->__toString();
					$a[$fid] = $field;
				}
			}
			if ($arg instanceof zenMysqlField){
				$fid = $field->__toString();
				$a[$fid] = $field;
			}
			$this->_selected[] = array($table, $a);
			$this->_selectedTables[$table->getUniqueId()] = $table;
			$this->_joinedTables[$table->getUniqueId()] = $table;
		}
		//var_dump($this->_selected);
		return $this;
	}
	protected function _constructJoins(){
		$this->_join = array(); // reset joins
		$sourceTable = $this->_storageSource;
		$sourceTableUid = $sourceTable->getUniqueId();
		$joined = array();
		$joined[$sourceTable->getUniqueId()] = true;
		foreach ($this->_joinedTables as $tableUid => $table2){
			if ($sourceTableUid !== $tableUid){ //
				// Trying to join table
				$on = '';
				$joinType = 'INNER';
				if (isset($this->_joinOptions[$table2->getUniqueId()])){
					$options = $this->_joinOptions[$table2->getUniqueId()];
					$on = $options['on'];
					$joinType = $options['type'];
				}
				$join = zenMysql::getIndirectTablesJoins($sourceTable, $table2, $this->_joinOptions);
				if ($join !== false){
					list($joinTables, $joinString) = $join;
					$notJoined = false;
					foreach ($joinTables as $tableUid => $b){
						if (!isset($joined[$tableUid])){
							$notJoined = true;
							$joined[$tableUid] = true;
						}
					}
					if ($notJoined){
						$this->_join[] = $joinString;
					}
				}
			}
		}
		//var_dump($this->_join);
	}
	/**
	 * @return modelQueryBuilder
	 */
	public function &join($table2, $on = '', $joinType = 'INNER'){
		/*foreach ($this->_joinedTables as $table1){
			$join = zenMysql::getTablesJoin($table1, $table2, $joinType, $on);
			if ($join !== false){
				$this->_join[] = $join;
				break;
			}
		}*/
		/*if (!is_object($table2)){
			var_dump($table2);
		}*/
		$this->_joinOptions[$table2->getUniqueId()] = array(
			'on' => $on,
			'type' => $joinType
		);
		$this->_joinedTables[$table2->getUniqueId()] = $table2;
		return $this;
	}
	/**
	 * @return modelQueryBuilder
	 */
	public function &leftJoin($table2, $on = ''){
		return $this->join($table2, $on, 'LEFT');
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
	protected function _joinCondition($condition){
		if ($condition instanceof zenExpression){
			$left = $condition->getLeft();
			if ($left instanceof zenMysqlField){
				$this->join($left->getTable());
			}
			$right = $condition->getLeft();
			if ($right instanceof zenMysqlField){
				$this->join($right->getTable());
			}
		}
	}
	/**
	 * @return modelQueryBuilder
	 */
	public function &where(){
		$conditions = func_num_args()?func_get_args():array();
		foreach ($conditions as $condition){
			$this->_where[] = $condition;
			$this->_joinCondition($condition);
		}
		return $this;
	}
	/**
	 * @return modelQueryBuilder
	 */
	public function &having($condition){
		$this->_having[] = $condition;
		$this->_joinCondition($condition);
		return $this;
	}
	/**
	 * @return modelQueryBuilder
	 */
	public function &asc($field){
		$this->_order[] = $field.' ASC';
		if ($field instanceof zenMysqlField){
			$this->join($field->getTable());
		}
		return $this;
	}
	/**
	 * @return modelQueryBuilder
	 */
	public function &desc($field){
		$this->_order[] = $field.' DESC';
		if ($field instanceof zenMysqlField){
			$this->join($field->getTable());
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
			list($table, $fields) = $sa;
			foreach ($fields as $fid => $field){
				$wa[] = $field." as ".$field->getUniqueId();
			}
		}
		return implode(", ", $wa);
	}
	protected function getJoinSql(){
		$this->_constructJoins();
		return implode(" ", $this->_join);
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
			return " WHERE ".implode(" AND ", $this->_where);
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
	public function getSql(){
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
	public function getCountSql(){
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
	
}