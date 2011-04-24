<?php
/*
 * TODO:
 * fromArray()
 * loadRelated() / submodels
 * Trees
 * select($table1, $table2, $field1, $field2, $string)
 * state(DRAFT/TDRAFT/TCLEAN/CLEAN)
 * $user->updated_at = new Doctrine_Expression('NOW()');
 * $user->refresh($refreshRelated = true);
 * $user->refreshRelated();
 * replace()
 * not()
 * PDO
 * find(pk)
 *
 *
 *
 *
 *
 *
 *
 *
 *
 */
class zenExpression{
	protected $_left = null;
	protected $_operator = '=';
	protected $_right = null;
	public function __construct($left, $operator, $right){
		$this->_left = $left;
		$this->_operator = $operator;
		$this->_right = $right;
		if ($this->_right instanceof kanonProperty){
			$this->_right = $this->_right->getValue();
		}
	}
	public function getLeft(){
		return $this->_left;
	}
	public function getRight(){
		return $this->_right;
	}
	public function __toString(){
		$right = $this->getRight();
		if (strtoupper($this->_operator) == 'IN'){
			if (is_array($right)){
				$right = implode(",", $right);
			}
			$right = '('.$right.')';
		}
		return $this->getLeft().' '.$this->_operator.' '.$right;
	}
}
class kanonObject implements ArrayAccess, Countable, IteratorAggregate{
	protected $_properties;
	protected function _propertyExists($name){
		return isset($this->_properties[$name]);
	}
	protected function _propertyUnset($name){
		unset($this->_properties[$name]);
	}
	protected function _propertyGet($name){
		return $this->_properties[$name];
	}
	protected function _propertySet($name, $value){
		$this->_properties[$name] = $value;
	}
	public function offsetExists($offset){
		return $this->_propertyExists($offset);
	}
	public function offsetUnset($offset){
		$this->_propertyUnset($offset);
	}
	public function offsetGet($offset){
		return $this->_propertyGet($offset);
	}
	public function offsetSet($offset, $value){
		$this->_propertySet($offset, $value);
	}
	public function __isset($name){
		return $this->_propertyExists($name);
	}
	public function __unset($name){
		$this->_propertyUnset($name);
	}
	public function __get($name){
		return $this->_propertyGet($name);
	}
	public function __set($name, $value){
		$this->_propertySet($name, $value);
	}
	public function count(){
		return count($this->_properties);
	}
	public function getIterator(){
		return new ArrayIterator($this->_properties);
	}
}
interface IControllableProperty{// extends IProperty
	public function setControl($controlClassName);
	/**
	 * @return IControl
	 */
	public function getControl();
}
interface IPropertyControl{// extends IControl
	public function setProperty($property);
	/**
	 * @return IControllableProperty
	 */
	public function getProperty();
}
class kanonProperty implements IControllableProperty{
	protected $_name = null;
	protected $_defaultValue = null;
	protected $_initialValue = null;
	protected $_value = null;
	protected $_options = array();
	protected $_item = null;
	/**
	 * @var IPropertyControl
	 */
	protected $_control = null;
	public function setOptions($options = array()){
		foreach ($options as $k => $v) $this->_options[$k] = $v;
	}
	public function __construct($propertyName){
		$this->_name = $propertyName;
		$this->onConstruct();
	}
	public function is($value){
		return new zenExpression($this, '=', $value);
	}
	public function lt($value){
		return new zenExpression($this, '<', $value);
	}
	public function gt($value){
		return new zenExpression($this, '>', $value);
	}
	public function in($value){
		return new zenExpression($this, 'IN', $value);
	}
	public function like($value){
		return new zenExpression($this, 'LIKE', $value);
	}
	public function onConstruct(){
		
	}
	public function setControl($controlClassName){
		$this->_control = new $controlClassName($this->_name);
		if ($this->_control->getProperty() === null){
			$this->_control->setProperty($this);
		}
	}
	public function setItem($item){
		$this->_item = $item;
	}
	public function getItem(){
		return $this->_item;
	}
	public function getControl(){
		return $this->_control;
	}
	public function getDefaultValue(){
		return $this->_defaultValue;
	}
	public function getInitialValue(){
		return $this->_initialValue;
	}
	public function getDatabaseValue($allowDefault = true){ // for sql SET
		return $this->_getValue($allowDefault);
	}
	public function getValue($allowDefault = true){
		return $this->_getValue($allowDefault);
	}
	protected function _getValue($allowDefault = true){ // Template for both public and database variants
		if ($this->_value === null){
			if ($this->_initialValue === null){
				if ($allowDefault){
					return $this->_defaultValue;
				}
			}
			return $this->_initialValue;
		}
		return $this->_value;
	}
	public function setValue($value){
		$this->_value = $value;
	}
	public function setInitialValue($value){
		$this->_initialValue = $value;
	}
	public function isChangedValue(){
		return (($this->_value !== null) && ($this->_value != $this->_initialValue));
	}
	public function __toString(){
		return strval($this->getValue());
	}
	public function html(){
		return htmlspecialchars($this->getValue());
	}
}
class kanonItem extends kanonObject{
	protected $_classesMap = array(); // propertyName => className
	protected $_options = array();
	public function setOptions($options = array()){
		foreach ($options as $k => $v) $this->_options[$k] = $v;
	}
	public function __construct(){
		$this->_makeAllProperties();
		$this->onConstruct();
	}
	public function onConstruct(){
		
	}
	protected function _makeAllProperties(){
		foreach ($this->_classesMap as $name => $class){
			if (!isset($this->_properties[$name])){
				$this->_properties[$name] = new $class($name);
				if (isset($this->_options[$name])){
					$this->_properties[$name]->setOptions($this->_options[$name]);
					$this->_properties[$name]->setItem($this);
					
				}
			}
		}
	}
	public function makeValuesInitial(){
		foreach ($this->_properties as $property){
			if ($property->isChangedValue()){
				$property->setInitialValue($property->getValue());
				$property->setValue(null);
			}
		}
	}
	protected function _propertyExists($name){
		return isset($this->_classesMap[$name]);
	}
	protected function _propertyUnset($name){
		unset($this->_properties[$name]);
	}
	protected function _propertyGet($name){
		if (!isset($this->_properties[$name])){
			$this->_makeAllProperties();
		}
		return $this->_properties[$name];
	}
	protected function _propertySet($name, $value){
		if (!is_object($this->_propertyGet($name))){
			throw new Exception("$name unknown in ".get_class($this));
		}
		$this->_propertyGet($name)->setValue($value);
	}
	public function toArray(){
		$a = array();
		foreach ($this as $name => $property){
			$a[$name] = $property->getValue();
		}
		return $a;
	}
	public function fromArray($array){
		foreach ($array as $name => $value){
			$this->_propertyGet($name)->setInitialValue($value);
		}
	}
	public function count(){
		$this->_makeAllProperties();
		return count($this->_properties);
	}
	public function getIterator(){
		$this->_makeAllProperties();
		return new ArrayIterator($this->_properties);
	}
}
class kanonCollection{
}
class storableProperty extends kanonProperty{
	public function isValidValue($toSave = false){
		return true;
	}
	public function preSave(){
		if (!$this->isValidValue()){
			$this->setValue(null);
		}
	}
	public function preInsert(){}
	public function preUpdate(){}
	public function preDelete(){}
	public function postSave(){}
	public function postInsert(){}
	public function postUpdate(){}
	public function postDelete(){}
}
class storableItem extends kanonItem{
	protected $_storage = null;
	protected $_storageClass = 'itemStorage';
	public function storageSet($storage){
		$this->_storage = $storage;
	}
	public function storageGet(){
		if ($this->_storage === null){
			$storageClass = $this->_storageClass;
			$this->_storage = new $storageClass();
		}
		//$this->_storage->setAdaptee($this);
		return $this->_storage;
	}
	public function save(){
		//echo 'save';
		$this->preSave();
		foreach ($this->_classesMap as $propertyName => $class){
			$property = $this->_propertyGet($propertyName);
			$property->preSave();
			//echo ' '.get_class($property);
			$control = $property->getControl();
			//var_dump($control);
			if ($control !== null){
				$control->preSave();
			}
		}
		$result = $this->storageGet()->saveItem($this);
		$this->postSave();
		foreach ($this->_classesMap as $propertyName => $class){
		$property = $this->_propertyGet($propertyName);
			$property->postSave();
			$control = $property->getControl();
			if ($control !== null){
				$control->postSave();
			}
		}
		return $result;
	}
	public function insert(){
		$this->preInsert();
		foreach ($this->_classesMap as $propertyName => $class){
			$this->_propertyGet($propertyName)->preInsert();
		}
		$result = $this->storageGet()->insertItem($this);
		$this->postInsert();
		foreach ($this->_classesMap as $propertyName => $class){
			$this->_propertyGet($propertyName)->postInsert();
		}
		return $result;
	}
	public function update(){
		$this->preUpdate();
		foreach ($this->_classesMap as $propertyName => $class){
			$this->_propertyGet($propertyName)->preUpdate();
		}
		$result = $this->storageGet()->updateItem($this);
		$this->postUpdate();
		foreach ($this->_classesMap as $propertyName => $class){
			$this->_propertyGet($propertyName)->postUpdate();
		}
		return $result;
	}
	public function delete(){
		$this->preDelete();
		foreach ($this->_classesMap as $propertyName => $class){
			$this->_propertyGet($propertyName)->preDelete();
		}
		$result = $this->storageGet()->deleteItem($this);
		$this->postDelete();
		foreach ($this->_classesMap as $propertyName => $class){
			$this->_propertyGet($propertyName)->postDelete();
		}
		return $result;
	}
	public function preSave(){}
	public function preInsert(){}
	public function preUpdate(){}
	public function preDelete(){}
	public function postSave(){}
	public function postInsert(){}
	public function postUpdate(){}
	public function postDelete(){}
}
class storableCollection extends kanonCollection{
}
class itemStorage{
	public function saveItem($item){}
	public function insertItem($item){}
	public function updateItem($item){}
	public function deleteItem($item){}
}
abstract class databaseCell extends storableProperty{

}
abstract class databaseRow extends storableItem implements ArrayAccess{
	protected $_fieldsMap = array(); // propertyName => fieldName
	protected $_primaryKey = array(); // fieldNames
	protected $_autoIncrement = null; // fieldName
	protected $_foreignKeys = array(); // property => array(foreignClass, foreignProperty)
	public function fieldsGet(){
		$fields = array();
		foreach ($this->_classesMap as $propertyName => $className){
			$fields[] = $this->propertyFieldNameGet($propertyName);
		}
		return $fields;
	}
	public function primaryKeyGet(){
		return $this->_primaryKey;
	}
	public function foreignKeysGet(){
		return $this->_foreignKeys;
	}
	public function primaryKey(){
		if (count($this->_primaryKey) == 1){
			return $this->{$this->_primaryKey[0]}->getValue();
		}
		return false;
	}
	public function autoIncrementGet(){
		return $this->_autoIncrement;
	}
	public function propertyFieldNameGet($propertyName){
		if (isset($this->_fieldsMap[$propertyName])){
			return $this->_fieldsMap[$propertyName];
		}
		return $propertyName;
	}
	public function fieldPropertyGet($fieldName){
		if (($propertyName = array_search($fieldName, $this->_fieldsMap)) !== FALSE){
			return $this->_propertyGet($propertyName);
		}
		return null;
	}
	/* ArrayAccess interface */
	public function offsetSet($fieldName, $value) {
	}
	public function offsetExists($fieldName) {
	}
	public function offsetUnset($fieldName) {
	}
	public function offsetGet($fieldName) {
		return $this->fieldPropertyGet($fieldName);
	}
}
abstract class databaseTable extends storableCollection{
}
class mysqlCell extends databaseCell{
}
class mysqlRow extends databaseRow{
	
}
class mysqlTable extends databaseTable{
}

interface IZenMysqlPrototype{
	public function q($sql);
	public function e($unescapedString);
}
class zenMysqlResult extends zenMysqlQueryBuilder implements IteratorAggregate, Countable{// implements IteratorAggregate, ArrayAccess, Countable
	protected $_mysqlResult = null;
	protected $_finished = false;
	protected $_list = array();
	public function count(){
		$q = $this->_q($this->getCountSql());
		if ($q === false){
			$app = application::getInstance();
			if ($app->getUserId() == 1){
				echo $this->getCountSql();
			}
			throw new Exception(mysql_error($this->getLink())."\n".$sql);
		}else{
			return mysql_result($q,0);
		}
	}
	public function delete(){ // Properly delete items
		foreach ($this as $item){
			$item->delete();
		}
	}
	protected function _makeList(){
		while ($model = $this->fetch()){
			$this->_list[] = $model;
		}
	}
	public function getIterator(){
		if (!$this->_finished){
			$this->_makeList();
		}
		return new ArrayIterator($this->_list);
	}
	public function execute(){
		if ($this->_mysqlResult === null){
			if ($this->_mysqlResult = $this->_q($this->getSql())){
				return true;
			}
			return false;
		}
		return true;
	}
	/**
	 * Fetch a result row as a zenMysqlItem
	 * @return zenMysqlItem
	 */
	public function fetch(){
		if ($this->_finished) return false;
		$this->execute();
		if ($this->_mysqlResult){
			if ($a = mysql_fetch_assoc($this->_mysqlResult)){
				//var_dump($a);
				$models = $this->_makeModels($a);
				return $models;
			}
		}
		$this->_finished = true;
		return false;
	}
	protected function _makeModels($a){
		$models = array();
		foreach ($this->_selected as $sa){
			//reset($this->_selected);
			//$sa = current($this->_selected);
			list($table, $fields) = $sa;
			if (!($modelClass = zenMysql::getTableModel($table))){
				$modelClass = 'zenMysqlRow';
			}
			$model = new $modelClass();
			/** @var zenMysqlRow $model */
			//var_dump($model);
			$tid = $table->getUid();
			foreach ($a as $k => $v){
				if (($p = strpos($k, "__")) !== FALSE){
					$k_tid = substr($k, 0, $p);
					$k_fn = substr($k,$p+2);
					//if ($k_tid == )
					//echo $k;
					if ($tid == $k_tid){
						if (!is_object($model[$k_fn]) && is_object($model)){
							// throw new Exception("Property \"".htmlspecialchars($k_fn)."\" not exists in class \"".get_class($model).'"');
						}else{
							$model[$k_fn]->setInitialValue($v);
						}
					}
				}
			}
			$models[] = $model;
		}
		if (count($models) == 1){
			return $model;
		}
		return $models;
	}
}
class zenMysqlQueryBuilder{
	protected $_linkSource = null;
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
	protected function _q($sql){
		return mysql_query($sql, $this->getLink());
	}
	public function q($sql){
		return mysql_query($sql, $this->getLink());
	}
	public function e($unescapedString){
		return mysql_real_escape_string($unescapedString, $this->getLink());
	}
	public function &getLink(){
		return zenMysql::getDatabaseLink($this->_linkSource->getServerId(), $this->_linkSource->getDatabaseName())->getLink();
	}
	public function &select(){
		$args = func_get_args();
		if (!count($args)) return $this;
		foreach ($args as $arg){
			$table = null;
			$field = null;
			if ($arg instanceof zenMysqlTable){
				$table = $arg;
			}
			if ($arg instanceof zenMysqlField){
				$table = $arg->getTable();
				$field = $arg;
			}
			if ($this->_linkSource === null) $this->_linkSource = $table;
			$a = array();
			if ($arg instanceof zenMysqlTable){
				foreach ($table as $field){
					$fid = $field->__toString();
					$a[$fid] = $field;
				}
			}
			if ($arg instanceof zenMysqlField){
				$fid = $field->__toString();
				$a[$fid] = $field;
			}
			if (is_object($table)){
				$this->_selected[] = array($table, $a);
				$this->_selectedTables[$table->getUid()] = $table;
				$this->_joinedTables[$table->getUid()] = $table;
			}
		}
		//var_dump($this->_selected);
		return $this;
	}
	protected function _constructJoins(){
		$this->_join = array(); // reset joins
		$sourceTable = $this->_linkSource;
		$sourceTableUid = $sourceTable->getUid();
		$joined = array();
		$joined[$sourceTable->getUid()] = true;
		foreach ($this->_joinedTables as $tableUid => $table2){
			if ($sourceTableUid !== $tableUid){ //
				// Trying to join table
				$on = '';
				$joinType = 'INNER';
				if (isset($this->_joinOptions[$table2->getUid()])){
					$options = $this->_joinOptions[$table2->getUid()];
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
		$this->_joinOptions[$table2->getUid()] = array(
			'on' => $on,
			'type' => $joinType
		);
		$this->_joinedTables[$table2->getUid()] = $table2;
		return $this;
	}
	public function &leftJoin($table2, $on = ''){
		return $this->join($table2, $on, 'LEFT');
	}
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
	public function &where(){
		$conditions = func_num_args()?func_get_args():array();
		foreach ($conditions as $condition){
			$this->_where[] = $condition;
			$this->_joinCondition($condition);
		}
		return $this;
	}
	public function &having($condition){
		$this->_having[] = $condition;
		$this->_joinCondition($condition);
		return $this;
	}
	public function &asc($field){
		$this->_order[] = $field.' ASC';
		if ($field instanceof zenMysqlField){
			$this->join($field->getTable());
		}
		return $this;
	}
	public function &desc($field){
		$this->_order[] = $field.' DESC';
		if ($field instanceof zenMysqlField){
			$this->join($field->getTable());
		}
		return $this;
	}
	public function &orderBy($orderString){
		$this->_order[] = $orderString;
		return $this;
	}
	public function &groupBy($groupString){
		$this->_group[] = $groupString;
		return $this;
	}
	protected function getWhatSql(){
		$wa = array();
		foreach ($this->_selected as $sa){
			list($table, $fields) = $sa;
			foreach ($fields as $fid => $field){
				$wa[] = $field." as ".$field->getUid();
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
class zenMysqlCell extends mysqlCell{
}
class zenMysqlRow extends mysqlRow{
	protected $_storageClass = 'zenMysqlItemStorage';
	public function __construct(){
		$this->_makeAllProperties();
		$this->_updateDefaults();
		$this->onConstruct();
	}
	public function onConstruct(){
		
	}
	protected function _updateDefaults(){
		$class = get_class($this);
		$table = zenMysql::getModelTable($class);
		if (!is_object($table)){
			throw new Exception('No table found for class '.$class);
		}
		foreach ($this->_classesMap as $propertyName => $className){
			if (isset($this->_fieldsMap[$propertyName])){
				$fieldName = $this->_fieldsMap[$propertyName];
				$default = $table->getDefaultFieldValue($fieldName);
				if ($default !== false){
					$this->{$propertyName}->setValue($default);
				}
			}
		}
	}
	public function __sleep(){
        return array('_properties');//'_classesMap', '_fieldsMap', '_primaryKey', '_autoIncrement', 
	}
	public function __wakeup(){}
}
class zenMysqlField{
	private $_serverId = null;
	private $_databaseName = null;
	private $_table = null;
	private $_tableName = null;
	private $_tableUid = null;
	private $_fieldName = null;
	public function __construct($table, $fieldName){
		$this->_serverId = $table->getServerId();
		$this->_databaseName = $table->getDatabaseName();
		$this->_table = $table;
		$this->_tableName = $table->getTableName();
		$this->_tableUid = $table->getUid();
		$this->_fieldName = $fieldName;
	}
	public function __toString(){
		return $this->_tableUid.'.`'.$this->_fieldName.'`';
	}
	public function getUid(){
		return $this->_tableUid.'__'.$this->_fieldName;
	}
	public function getServerId(){
		return $this->_serverId;
	}
	public function getDatabaseName(){
		return $this->_databaseName;
	}
	public function getTable(){
		return $this->_table;
	}
	public function getTableName(){
		return $this->_tableName;
	}
	public function getTableUid(){
		return $this->_tableUid;
	}
	public function getFieldName(){
		return $this->_fieldName;
	}
	public function is($value){
		return new zenExpression($this, '=', $value);
	}
	public function lt($value){
		return new zenExpression($this, '<', $value);
	}
	public function gt($value){
		return new zenExpression($this, '>', $value);
	}
	public function in($value){
		return new zenExpression($this, 'IN', $value);
	}
	public function like($value){
		return new zenExpression($this, 'LIKE', $value);
	}
}
class zenMysqlTable extends mysqlTable implements ArrayAccess, Countable, IteratorAggregate{
	private $_serverId = null;
	private $_databaseName = null;
	private $_tableName = null;
	private $_fCache = array();
	private $_fList = array();
	private $_isFullList = false;
	private $_uid = null;
	private $_filters = array();
	private $_defaults = array();
	public function &addFilter($filter){
		$this->_filters[] = $filter;
		return $this;
	}
	public function &setDefaultFieldValue($field, $value){
		$this->_defaults[$field] = $value;
		return $this;
	}
	public function &getDefaultFieldValue($field){
		if (isset($this->_defaults[$field])){
			return $this->_defaults[$field];
		}		
		return false;
	}
	public function &resetFilters(){
		$this->_filters = array();
		return $this;
	}
	protected function _q($sql){
		return mysql_query($sql, $this->getLink());
	}
	public function q($sql){
		return mysql_query($sql, $this->getLink());
	}
	public function e($unescapedString){
		return mysql_real_escape_string($unescapedString, $this->getLink());
	}
	public function getUid(){
		if ($this->_uid === null){
			$this->_uid = zenMysql::getTableUid();
		}
		return $this->_uid;
	}
	protected function _fetchFieldsList(){
		$sql = "SHOW COLUMNS FROM `".$this->e($this->_tableName)."`";
		if ($q = $this->_q($sql)){
			while ($a = mysql_fetch_row($q)){
				$fieldName = $a[0];
				$this->_fList[$fieldName] = $this->getFieldByName($fieldName);
			}
			$this->_isFullList = true;
		}
	}
	public function __construct($database, $tableName){
		$this->_serverId = $database->getServerId();
		$this->_databaseName = $database->getDatabaseName();
		$this->_tableName = $tableName;
	}
	public function getFieldByName($fieldName){
		if (!isset($this->_fCache[$fieldName])){
			$this->_fCache[$fieldName] = new zenMysqlField($this, $fieldName);
		}
		return $this->_fCache[$fieldName];
	}
	public function __toString(){
		return $this->getUid();
		return $this->_tableName;
	}
	public function setModel($modelClass){
		zenMysql::setTableModel($this, $modelClass);
		//zenMysql::configureModel($modelClass)->setTable($this);
	}
	public function getServerId(){
		return $this->_serverId;
	}
	public function getDatabaseName(){
		return $this->_databaseName;
	}
	public function getTableName(){
		return $this->_tableName;
	}
	public function &getLink(){
		return zenMysql::getDatabaseLink($this->_serverId, $this->_databaseName)->getLink();
	}
	/* ArrayAccess interface */
	public function offsetSet($fieldName, $value) {
		$this->_fCache[$fieldName] = $value;
	}
	public function offsetExists($fieldName) {
		if (!isset($this->_fCache[$fieldName]) && !$this->_isFullList){
			$this->_fetchFieldsList();
		}
		return isset($this->_fCache[$fieldName]);
	}
	public function offsetUnset($fieldName) {
		unset($this->_fCache[$fieldName]);
	}
	public function offsetGet($fieldName) {
		return $this->getFieldByName($fieldName);
	}
	/**
	 * Countable interface
	 * @return integer
	 */
	public function count() {
		if (!$this->_isFullList){
			$this->_fetchFieldsList();
		}
		return count($this->_fList);
	}
	public function getIterator(){
		if (!$this->_isFullList){
			$this->_fetchFieldsList();
		}
		return new ArrayIterator($this->_fList);
	}
	// __get && __set && __isset && __unset
	public function __get($fieldName){
		return $this->getFieldByName($fieldName);
	}
	/* SQL */
	public function select(){ 
		$args = func_num_args()?func_get_args():array();
		array_unshift($args, $this);
		$qb = new zenMysqlResult();
		call_user_func_array(array($qb, 'select'), $args);
		foreach ($this->_filters as $filter){
			$qb->where($filter);
		}
		return $qb;
	}
}
class zenMysqlDatabase implements IZenMysqlPrototype, ArrayAccess, Countable, IteratorAggregate{
	private $_serverId = null;
	private $_databaseName = null;
	private $_tCache = array();
	private $_tList = array();
	private $_isFullList = false;
	public function __construct($server, $databaseName){
		$this->_serverId = $server->getServerId();
		$this->_databaseName = $databaseName;
	}
	public function __toString(){
		return $this->_databaseName;
	}
	protected function _q($sql){
		return mysql_query($sql, $this->getLink());
	}
	public function q($sql){
		return mysql_query($sql, $this->getLink());
	}
	public function e($unescapedString){
		return mysql_real_escape_string($unescapedString, $this->getLink());
	}
	public function getServerId(){
		return $this->_serverId;
	}
	public function getDatabaseName(){
		return $this->_databaseName;
	}
	public function &setAlias($alias){
		zenMysql::setDatabaseAlias($this, $alias);
		return $this;
	}
	public function &setDefault(){
		return $this->setAlias('__default');
	}
	public function getTableByName($tableName){
		if (!isset($this->_tCache[$tableName])){
			$this->_tCache[$tableName] = new zenMysqlTable($this, $tableName);
		}
		return $this->_tCache[$tableName];
	}
	public function &getLink(){
		return zenMysql::getDatabaseLink($this->_serverId, $this->_databaseName)->getLink();
	}
	protected function _fetchTablesList(){
		$sql = "SHOW TABLES";
		if ($q = $this->_q($sql)){
			while ($a = mysql_fetch_row($q)){
				$tableName = $a[0];
				$this->_tList[$tableName] = $this->getTableByName($tableName);
			}
			$this->_isFullList = true;
		}
	}
	/* ArrayAccess interface */
	public function offsetSet($tableName, $value) {
		$this->_tCache[$tableName] = $value;
	}
	public function offsetExists($tableName) {
		if (!isset($this->_tCache[$tableName]) && !$this->_isFullList){
			$this->_fetchTablesList();
		}
		return isset($this->_tCache[$tableName]);
	}
	public function offsetUnset($tableName) {
		unset($this->_tCache[$tableName]);
	}
	public function offsetGet($tableName) {
		return $this->getTableByName($tableName);
	}
	/**
	 * Countable interface
	 * @return integer
	 */
	public function count() {
		if (!$this->_isFullList){
			$this->_fetchTablesList();
		}
		return count($this->_tList);
	}
	public function getIterator(){
		if (!$this->_isFullList){
			$this->_fetchTablesList();
		}
		return new ArrayIterator($this->_tList);
	}
}
class zenMysqlServer implements IZenMysqlPrototype, ArrayAccess, Countable, IteratorAggregate{
	private $_serverId = null;
	private $_zenMysqlLink = null;
	private $_dCache = array();
	private $_dList = array();
	private $_isFullList = false;
	public function __construct($serverId){
		$this->_serverId = $serverId;
	}
	protected function _fetchDatabasesList(){
		$sql = "SHOW DATABASES";
		if ($q = $this->_q($sql)){
			while ($a = mysql_fetch_assoc($q)){
				$databaseName = $a['Database'];
				$this->_dList[$databaseName] = $this->getDatabaseByName($databaseName);
			}
			$this->_isFullList = true;
		}
	}
	protected function _q($sql){
		return mysql_query($sql, $this->getLink());
	}
	public function q($sql){
		return mysql_query($sql, $this->getLink());
	}
	public function e($unescapedString){
		return mysql_real_escape_string($unescapedString, $this->getLink());
	}
	public function getServerId(){
		return $this->_serverId;
	}
	public function getDatabaseByName($databaseName){
		if (!isset($this->_dCache[$databaseName])){
			$this->_dCache[$databaseName] = new zenMysqlDatabase($this, $databaseName);
		}
		return $this->_dCache[$databaseName];
	}
	public function getDatabaseByAlias($alias){
		return zenMysql::getDatabaseByAlias($alias);
	}
	public function &getLink(){
		return zenMysql::getServerLink($this->_serverId)->getLink();
	}
	/* ArrayAccess interface */
	public function offsetSet($databaseName, $value) {
		$this->_dCache[$databaseName] = $value;
	}
	public function offsetExists($databaseName) {
		if (!isset($this->_dCache[$databaseName]) && !$this->_isFullList){
			$this->_fetchDatabasesList();
		}
		return isset($this->_dCache[$databaseName]);
	}
	public function offsetUnset($databaseName) {
		unset($this->_dCache[$databaseName]);
	}
	/**
	 * @param string $databaseName
	 * @return zenMysqlDatabase
	 */
	public function offsetGet($databaseName) {
		return $this->getDatabaseByName($databaseName);
	}
	/**
	 * Countable interface
	 * @return integer
	 */
	public function count() {
		if (!$this->_isFullList){
			$this->_fetchDatabasesList();
		}
		return count($this->_dList);
	}
	public function getIterator(){
		if (!$this->_isFullList){
			$this->_fetchDatabasesList();
		}
		return new ArrayIterator($this->_dList);
	}
}
class zenMysql{
	private static $_models = array();
	private static $_tables = array();
	private static $_databases = array();
	private static $_servers = array();
	private static $_serverLinks = array();
	private static $_databaseLinks = array();
	private static $_tableUidAi = 0;
	private static $_foreignKeys = array(); // table => array(key, foreignTable, foreignKey);
	private static $_foreignConnections = array(); // complete list of direct & indirect connections
	public static function getTableUid(){
		$uid = self::$_tableUidAi;
		$uid = strval(base_convert($uid, 10, 26));
		$shift = ord("a") - ord("0");
		for ($i = 0; $i < strlen($uid); $i++){
			$c = $uid{$i};
			if (ord($c) < ord("a")){
				$uid{$i} = chr(ord($c)+$shift);
			}else{
				$uid{$i} = chr(ord($c)+10);
			}
		}
		self::$_tableUidAi++;
		return $uid;
	}
	public static function registerForeignKeys($model){
		$class = get_class($model);
		//echo '<b style="font-weight: bold;">Register '.$class.'</b><br />';
		
		$fks = $model->foreignKeysGet(); // array( property => array(foreignClass, foreignProperty),..)
		foreach ($fks as $propertyName => $a){
			list($foreignClass, $foreignPropertyName) = $a;
			//echo '+ foreignClass '.$foreignClass.':<br />';
			// add direct connections
			if (!isset(self::$_foreignConnections[$foreignClass])){
				self::$_foreignConnections[$foreignClass] = array();
			}
			self::$_foreignConnections[$foreignClass][$class] = array($foreignPropertyName, $propertyName);
			// reverse connection
			if (!isset(self::$_foreignConnections[$foreignClass])){
				self::$_foreignConnections[$foreignClass] = array();
			}
			self::$_foreignConnections[$class][$foreignClass] = array($propertyName, $foreignPropertyName);
			
			// add indirect connections
			/*foreach (self::$_foreignConnections as $indirectClass => $connections){
				//echo 'indirectClass '.$indirectClass.'<br />';
				if ($indirectClass == $foreignClass){
					foreach ($connections as $indirectForeignClass => $indirectOptions){
						if ($indirectForeignClass !== $class && $indirectForeignClass !== $foreignClass){
							// add connection from indirectForeignClass to foreignClass
							self::$_foreignConnections[$class][$indirectForeignClass] = $foreignClass; // connection available via $class
							self::$_foreignConnections[$indirectForeignClass][$class] = $foreignClass; // connection available via $class
						}
					}
				}
			}*/
			//echo '<pre>';
			//var_dump(self::$_foreignConnections);
			//echo '</pre><hr />';
		}
		foreach (self::$_foreignConnections as $class => $connections){
			foreach ($connections as $foreignClass => $options){
				if (!isset(self::$_foreignConnections[$foreignClass])) continue;
				foreach (self::$_foreignConnections[$foreignClass] as $foreignClass2 => $options2){
					if (!isset(self::$_foreignConnections[$class][$foreignClass2])){
						//echo $class.'=>'.$foreignClass2.' via '.$foreignClass.'.<br />';
						//echo $indirectForeignClass2.'<br />';
						self::$_foreignConnections[$class][$foreignClass2] = $foreignClass;
					}
				}
			}
		}
		
	}
	public static function setDatabaseAlias($zenMysqlDatabase, $databaseAlias){
		self::$_databases[$databaseAlias] = $zenMysqlDatabase;
	}
	public static function getDatabaseByAlias($databaseAlias){
		return self::$_databases[$databaseAlias];
	}
	public static function getModelForeignKeys($modelClass){
		$model = new $modelClass();
		return $model->foreignKeysGet();
		//list($foreignModelClass, $foreignPropertyName) = $a;
	}
	public static function getIndirectTablesJoins($sourceTable, $table2, $joinOptions){
		//echo '<pre>';
		//var_dump(self::$_foreignConnections);
		//echo '</pre>';
		$sourceModelClass = self::getTableModel($sourceTable);
		$model2Class = self::getTableModel($table2);
		$joins = array();
		$joinedTables = array();
		foreach (self::$_foreignConnections[$sourceModelClass] as $connectedClass => $options){
			if ($connectedClass == $model2Class){
				if (!is_array($options)){
					$viaClass = $options;
					//echo 'Connectiong via '.$viaClass.'<br />';
					$viaTable = self::getModelTable($viaClass);
					$join1 = self::getIndirectTablesJoins($sourceTable, $viaTable, $joinOptions);
					$join2 = self::getIndirectTablesJoins($viaTable, $table2, $joinOptions);
					if ($join1 !== false){
						list($extraJoinedTables, $joinString) = $join1;
						$joins[] = $joinString;
						foreach ($extraJoinedTables as $uid => $b){
							$joinedTables[$uid] = true;
						}
					}
					if ($join2 !== false){
						list($extraJoinedTables, $joinString) = $join2;
						$joins[] = $joinString;
						foreach ($extraJoinedTables as $uid => $b){
							$joinedTables[$uid] = true;
						}
					}
					return array($joinedTables, implode("", $joins));
				}else{
					$joinString = self::getTablesJoin($sourceTable, $table2, $joinOptions);
					$joinedTables[$sourceTable->getUid()] = true;
					$joinedTables[$table2->getUid()] = true;
					//echo 'Connectiong via DIRECT<br />';
					if ($joinString !== false) return array($joinedTables, $joinString);
				}
			}
		}
		//echo 'Connectiong via FALSE<br />';
		return false;
	}
	public static function getTablesJoin($table1, $table2, $options){
		$joinType = $options[$table2->getUid()]['type'];
		$model1Class = self::getTableModel($table1);
		$model2Class = self::getTableModel($table2);
		$model1 = new $model1Class();
		$model2 = new $model2Class();
		$fks = $model1->foreignKeysGet();
		foreach ($fks as $p => $a){
			list($fm, $fp) = $a;
			if ($fm == $model2Class){
				$f1 = $model1->propertyFieldNameGet($p);
				$f2 = $model2->propertyFieldNameGet($fp);
				return " ".$joinType." JOIN {$table2->getTableName()} AS $table2 ON ({$table1->$f1} = {$table2->$f2}".($on==''?'':' AND '.$on).")";
			}
		}
		$fks = $model2->foreignKeysGet();
		foreach ($fks as $p => $a){
			list($fm, $fp) = $a;
			if ($fm == $model1Class){
				$f1 = $model1->propertyFieldNameGet($fp);
				$f2 = $model2->propertyFieldNameGet($p);
				return " ".$joinType." JOIN {$table2->getTableName()} AS $table2 ON ({$table1->$f1} = {$table2->$f2}".($on==''?'':' AND '.$on).")";
			}
		}
		return false;
	}
	public static function setTableModel($zenMysqlTable, $modelClass){
		self::$_models[$zenMysqlTable->getDatabaseName()][$zenMysqlTable->getTableName()] = $modelClass;
		self::$_tables[$modelClass] = $zenMysqlTable;
		$model = new $modelClass();
		self::registerForeignKeys($model);
	}
	public static function getModelTable($class){
		//var_dump(self::$_tables);
		return self::$_tables[$class];
	}
	public static function getModels(){
		return self::$_tables;
	}
	public static function getTable($class){
		return self::getModelTable($class);
	}
	public static function getTableModel($table){
		if (!isset(self::$_models[$table->getDatabaseName()]) || !isset(self::$_models[$table->getDatabaseName()][$table->getTableName()])){
			return false;
		}
		return self::$_models[$table->getDatabaseName()][$table->getTableName()];
	}
	public static function configureModel($modelClass){
		return new zenMysqlModelConfiguration($modelClass);
	}
	public static function registerServer($hostname = 'localhost', $username = 'root', $password = '', $persistent = false){
		$serverId = $username.'@'.$hostname;
		$server = new zenMysqlServer($serverId);
		self::$_serverLinks[$serverId] = new zenMysqlLink($hostname, $username, $password, $persistent);
		return $server;
	}
	public static function &getServerLink($serverId){
		return self::$_serverLinks[$serverId];
	}
	public static function &getDatabaseLink($serverId, $databaseName){
		if (!isset(self::$_databaseLinks[$serverId]) || !isset(self::$_databaseLinks[$serverId][$databaseName])){
			$link = clone self::getServerLink($serverId);
			$link->setDatabase($databaseName);
			self::$_databaseLinks[$serverId][$databaseName] = $link;
		}
		return self::$_databaseLinks[$serverId][$databaseName];
	}
}
class zenMysqlLink{
	protected $_server = null;
	protected $_username = null;
	protected $_password = null;
	protected $_persistent = null;
	protected $_databaseName = null;
	protected $_characterSet = 'UTF8';
	
	protected $_lastAffectedRows = 0;
	protected $_currentDatabaseName = null;
	protected $_link = null; // mysql link resource
	public function __construct($server, $username, $password, $persistent = false){
		$this->_server = $server;
		$this->_username = $username;
		$this->_password = $password;
		$this->_persistent = $persistent;
	}
	protected function _switchDatabase($databaseName){
		if ($this->_currentDatabaseName !== $this->_databaseName){
			if (is_resource($this->_link)){
				if (mysql_select_db($this->_databaseName, $this->_link)){
					$this->_currentDatabaseName = $this->_databaseName;
					return true;
				}
			}
			return false;
		}
		return true;
	}
	public function setDatabase($databaseName){
		$this->_databaseName = $databaseName;
	}
	public function isConnected(){
		if ($this->_link === null) return false;
		if (!is_resource($this->_link)) return false;
		if (mysql_ping ($this->_link)) {
			return true;
		}
		return false;
	}
	public function open(){
		if ($this->_link !== null) return;
		if ($this->_persistent){
			$this->_link = @mysql_pconnect($this->_server, $this->_username, $this->_password, true);
		}else{
			$this->_link = @mysql_connect($this->_server, $this->_username, $this->_password, true);
		}
		$this->query("SET NAMES ".$this->_characterSet);
		$this->_switchDatabase($this->_databaseName);
	}
	public function query($sql){
		$q = mysql_query($sql, $this->getLink());
		$this->_lastAffectedRows = mysql_affected_rows($this->getLink());
		return $q;
	}
	public function insertId(){
		return mysql_insert_id($this->getLink());
	}
	public function affectedRows(){
		return $this->_lastAffectedRows;//mysql_affected_rows($this->getLink());
	}
	public function lastErrorNumber(){
		return mysql_errno($this->getLink());
	}
	public function lastError(){
		return mysql_error($this->getLink());
	}
	public function escape($unescaped_string){
		return mysql_real_escape_string($unescaped_string, $this->getLink());
	}

	public function &getLink(){
		if ($this->_link === null) $this->open();
		return $this->_link;
	}
	public function __clone() {
		$this->_link = null;
	}
}
class zenMysqlModelConfiguration{
	private $_modelClass;
	public function __construct($modelClass){
		$this->_modelClass = $modelClass;
	}
	public function setTable($zenMysqlTable){
		zenMysql::setTableModel($zenMysqlTable, $this->_modelClass);
	}
}
/*class directoryLink extends zenMysqlRow{
 protected $_fieldsMap = array(
 'id' => 'id',
 'categoryId' => 'category_id',
 'url' => 'url',
 'title' => 'title',
 'description' => 'description',
 'bannerUrl' => 'banner_url',
 'createdAt' => 'created_at',
 'modifiedAt' => 'modified_at',
 );
 protected $_classesMap = array(
 'id' => 'kanonProperty',
 'categoryId' => 'kanonProperty',
 'url' => 'kanonProperty',
 'title' => 'kanonProperty',
 'description' => 'kanonProperty',
 'bannerUrl' => 'kanonProperty',
 'createdAt' => 'kanonProperty',
 'modifiedAt' => 'kanonProperty',
 );
 protected $_primaryKey = array(
 'id',
 );
 protected $_autoIncrement = array(
 'id',
 );
 public static function _getTable(){
 return zenMysql::getModelTable('directoryLink');
 }
 }
 /*
 $server = zenMysql::registerServer('localhost', 'root', 'ghbrjkbcnfvytnflvbyfvlf');
 $db = $server['af_web_directory']->setDefault();
 $table = $db['directory_links'];
 $table->setModel('directoryLink');
 $q = $table->select()
 ->where("$table->id < 100")
 ->orderBy("$table->created_at DESC")
 ->having("$table->id > 3")
 ->groupBy("$table->id")
 ->limit(20);
 echo $q->getSql();
 $q = $table->select();
 echo $q->getSql();
 while ($link = $q->fetch()){
 var_dump($link->toArray());
 }
 var_dump($q);*/
//echo count($server);
/*foreach ($server as $db){
 echo $db.'<br />';
 foreach ($db as $table){
 echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$table.'<br />';
 foreach ($table as $field){
 echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$field.'<br />';
 }
 }
 }*/
/*$i = 0;
 while ($i < 50){
 echo zenMysql::getTableUid().' ';
 $i++;
 }*/
/*
 var_dump($server);
 var_dump($server->getLink());
 var_dump($db);
 */
// zenMysql::configureModel('directoryLink')->setTable($table);



class zenMysqlItemStorage extends itemStorage{
	protected $_table = null;
	public function query($sql){
		if (!strlen($sql)) return false;
		//echo $sql;
		//die();
		$q = mysql_query($sql, $this->_table->getLink());
		if (!$q){
			$app = application::getInstance();
			if ($app->getUserId() == 1){
				throw new Exception(mysql_error($this->_table->getLink())."\n".$sql);
			}
		}
		return $q;
		return true;
	}
	protected function _getSetSql($item){
		$seta = array();
		$fields = $item->fieldsGet();
		foreach ($fields as $fieldName){
			$property = $item[$fieldName];
			if ($property){
				if ($property->isChangedValue()){
					$seta[] = "`$fieldName` = '".$this->_table->e($property->getDatabaseValue())."'";
				}
			}
		}
		if (count($seta)){
			return " SET ".implode(",", $seta);
		}
		return false;
	}
	protected function _getWhatSql($item){
		return '*';
	}
	protected function _getWherePrimaryKeySql($item, $useAssignedValues = false){
		$wherea = array();
		$pk = $item->primaryKeyGet();
		if (count($pk)){
			foreach ($pk as $fieldName){
				$property = $item[$fieldName];
				if ($property){
					$initialValue = $property->getInitialValue();
					if ($initialValue !== null){
						$wherea[] = "`$fieldName` = '".$this->_table->e($initialValue)."'";
					}else{
						if ($useAssignedValues){
							$value = $property->getValue();
							if ($value !== null){
								$wherea[] = "`$fieldName` = '".$this->_table->e($value)."'";
							}
						}
					}
				}
			}
			if (count($pk) == count($wherea)){
				return " WHERE ".implode(" AND ", $wherea);
			}
		}
		return false;
	}
	protected function _getWhereSql($item, $useAssignedValues = false){
		if (($whereSql = $this->_getWherePrimaryKeySql($item, $useAssignedValues)) !== false){
			return $whereSql;
		}
		// can't use PK
		$wherea = array();
		$fields = $item->fieldsGet();
		foreach ($fields as $fieldName){
			$property = $item[$fieldName];
			if ($property){
				$initialValue = $property->getInitialValue();
				if ($initialValue !== null){
					$wherea[] = "`$fieldName` = '".$this->_table->e($initialValue)."'";
				}
			}
		}
		if (count($wherea)){
			return " WHERE ".implode(" AND ", $wherea);
		}
		return false;
	}
	protected function _getInsertSql($item){
		if (!is_object($this->_table)){
			$this->_table = zenMysql::getModelTable(get_class($item));
			if (!is_object($this->_table)){
				var_dump($item);
				var_dump($this->_table);
				var_dump(zenMysql::getModels());
				throw new Exception("can't save item: table undefined");
			}
		}
		$tableName = $this->_table->getTableName();
		$setSql = $this->_getSetSql($item);
		if ($setSql){
			return "INSERT INTO `$tableName`".$setSql;
		}
		return false;
	}
	protected function _getUpdateSql($item){
		$tableName = $this->_table->getTableName();
		$setSql = $this->_getSetSql($item);
		if ($setSql){
			return "UPDATE `$tableName`".$setSql.$this->_getWhereSql($item)." LIMIT 1";
		}
		return false;
	}
	protected function _getDeleteSql($item){
		$tableName = $this->_table->getTableName();
		if ($where = $this->_getWhereSql($item, true)){
			return "DELETE FROM `$tableName`".$where." LIMIT 1";
		}else{
			return false;
		}
	}
	public function saveItem($item){
		$this->_table = zenMysql::getModelTable(get_class($item));
		if ($this->_getWhereSql($item)){
			$result = $item->update();
		}else{
			$result = $item->insert();
		}
		return $result;
	}
	public function insertItem($item){
		//var_dump(__METHOD__);
		$this->_table = zenMysql::getModelTable(get_class($item));
		$sql = $this->_getInsertSql($item);
		if ($this->query($sql)){
			$item->makeValuesInitial();
			$aiFieldName = $item->autoIncrementGet();
			//echo $aiFieldName;
			if ($aiFieldName !== null){
				$aiProperty = $item[$aiFieldName];
				if ($aiValue = mysql_insert_id($this->_table->getLink())){
					if (!is_object($aiProperty)){
						throw new Exception('field "'.print_r($aiFieldName, true).'" not defined in class "'.get_class($item).'"');
					}
					$aiProperty->setInitialValue($aiValue);
				}
			}
			return true;
		}
		return false;
	}
	public function updateItem($item){
		//var_dump(__METHOD__);
		$this->_table = zenMysql::getModelTable(get_class($item));
		$sql = $this->_getUpdateSql($item);
		if ($this->query($sql)){
			$item->makeValuesInitial();
			return true;
		}
		return false;
	}
	public function deleteItem($item){
		//var_dump(__METHOD__);
		$this->_table = zenMysql::getModelTable(get_class($item));
		if ($sql = $this->_getDeleteSql($item)){
		//echo $sql;
		$this->query($sql);
		}
	}
}

class integerProperty extends zenMysqlCell{
	public function getCreateTablePropertySql(){
		return "`".$this->_fieldName."` bigint(20) unsigned NOT NULL";
	}
}
class stringProperty extends zenMysqlCell{
}
class httpProperty extends zenMysqlCell{

}
class timestampProperty extends integerProperty{
	/**
	 * @return string Human presentation
	 */
	public function format($format = "d.m.Y H:i:s"){
		return date($format, $this->value());
	}
	public function chanFormat(){//Вск 06 Дек 2009
		$ts = $this->getValue();
		$wa = array('Вск','Пнд','Втр','Срд','Чтв','Птн','Сбт');
		$ma = array(null,'Янв','Фев','Мар','Апр','Май','Июн','Июл','Авг','Сен','Окт','Ноя','Дек');
		return $wa[date("w", $ts)].' '.date("d", $ts).' '.$ma[date("m", $ts)].' '.date("Y H:i:s", $ts);
	}
}
class creationTimestampProperty extends timestampProperty{
	public function preInsert(){
		$this->setValue(time());
	}
}
class modificationTimestampProperty extends timestampProperty{
	public function preSave(){
		$this->setValue(time());
	}
}
class filenameProperty extends stringProperty{
	protected $_path = 'undefined';
	protected $_uri = '';
	protected $_tmWidth = 0;
	protected $_tmHeight = 0;
	public function getPath(){
		$app = application::getInstance();
		return $app->getBasePath($this->_options['path']);
	}
	public function getUri(){
		return $this->_options['url'];
	}
	public function sourcePath(){
		return $this->getPath().$this->getValue();
	}
	public function sourceUrl(){
		return $this->getUri().$this->getValue();
	}
	public function source(){
		return $this->sourceUrl();
	}
	public function unlink(){
		unlink($this->sourcePath());
	}
	public function preDelete(){
		$this->unlink();
	}
}
class imageFilenameProperty extends filenameProperty{
	public function tm($size, $method = 'fit'){
		if (!is_file($this->getPath().$this->getValue())){
			return false;
		}
		$img = new tImage();
		$img->path = $this->getPath();
		$img->thumbnailsFolder = '.thumb';
		$tm = $img->tm($this->getValue(), $size, $method);
		if (is_file($img->path.$img->thumbnailsFolder.'/'.$tm)){
			$info = getimagesize($img->path.$img->thumbnailsFolder.'/'.$tm);
			$this->_tmWidth = $info[0];
			$this->_tmHeight = $info[1];
		}else{
			return false;
		}
		return $this->getUri().$img->thumbnailsFolder.'/'.$tm;
	}
	public function html($size = 100, $method="fit"){
		return '<img src="'.$this->tm($size, $method).'" height="'.$this->_tmHeight.'" width="'.$this->_tmWidth.'" />';
	}
	public function unlink(){
		$img = new tImage();
		$img->path = $this->getPath();
		$img->thumbnailsFolder = '.thumb';
		$img->unlink($this->getValue());
	}
	public function unlinkThumbs(){
		$img = new tImage();
		$img->path = $this->getPath();
		$img->thumbnailsFolder = '.thumb';
		$img->unlink($this->getValue(), true);
	}
}
class flashFilenameProperty extends filenameProperty{
	public function html($width = 'auto', $height = 'auto'){
		if ($this->getValue() == '') return '';
		list($w,$h) = getimagesize($this->sourcePath());
		if ($width == 'auto') $width = $w;
		if ($height == 'auto') $height = $h;
		return '<object width="'.$width.'" height="'.$height.'">'.
		'<param name="movie" value="'.$this->source().'"></param>'.
		'<param name="allowFullScreen" value="true"></param>'.
		'<param name="allowscriptaccess" value="always"></param>'.
		'<embed src="'.$this->source().'" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="'.$width.'" height="'.$height.'"></embed>'.
		'</object>';
	}
}
class mediaFilenameProperty extends imageFilenameProperty{
	protected function _getSize(){
		if ($this->getValue() == '') return false;
		list($fw,$fh) = getimagesize($this->sourcePath());
		$w = $fw; $h = $fh;
		$item = $this->getItem();
		//echo get_class($item);
		if (isset($this->_options['widthKey'])){
			$wk = $this->_options['widthKey'];
			$w = $item->$wk->getValue();
		}
		$w = $w?$w:$fw;
		if (isset($this->_options['heightKey'])){
			$hk = $this->_options['heightKey'];
			$h = $item->$hk->getValue();
		}
		$h = $h?$h:$fh;
			//echo ' w:'.$w;
			//echo ' h:'.$h;
		return array($w,$h);
	}
	protected function _flashHtml($width = 'auto', $height = 'auto'){
		if ($this->getValue() == '') return '';
		list($w,$h) = $this->_getSize();//getimagesize($this->sourcePath());
		if ($width == 'auto') $width = $w;
		if ($height == 'auto') $height = $h;
		return '<object width="'.$width.'" height="'.$height.'">'.
		'<param name="movie" value="'.$this->source().'"></param>'.
		'<param name="allowFullScreen" value="true"></param>'.
		'<param name="allowscriptaccess" value="always"></param>'.
		'<embed src="'.$this->source().'" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="'.$width.'" height="'.$height.'"></embed>'.
		'</object>';
	}
	public function _imageHtml($size = 100, $method="fit"){
		if ($size == 'auto'){
			$tm = $this->source();
			list($w,$h) = getimagesize($this->sourcePath());
			$in = ' height="'.$h.'" width="'.$w.'"';
		}else{
			$tm = $this->tm($size, $method);
			$in = ' height="'.$this->_tmHeight.'" width="'.$this->_tmWidth.'"';
		}
		return '<img src="'.$tm.'"'.$in.' />';
	}
	public function _imageSourceHtml($size = 100, $method="fit"){
		$tm = $this->source();
		list($w,$h) = getimagesize($this->sourcePath());
		$in = ' height="'.$h.'" width="'.$w.'"';
		return '<img src="'.$tm.'"'.$in.' />';
	}
	public function html($width = 'auto', $height = 'auto'){
		$ext = end(explode(".", $this->getValue()));
		if ($ext == 'swf'){
			return $this->_flashhtml();//$width, $height
		}else{
			$size = 'auto';
			if ($width != 'auto' && $height != 'auto'){
				$size = max($width, $height);
			}else{
				if ($width != 'auto') $size = $width;
				if ($height != 'auto') $size = $height;
			}
			return $this->_imageSourceHtml($size);
		}
	}
}