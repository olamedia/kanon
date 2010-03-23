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
 * $user->exists()
 *
 *
 *
 *
 *
 *
 *
 *
 */

class kanonItem extends kanonObject{
	
	public function fromArray($array){
		foreach ($array as $name => $value){
			$this->_propertyGet($name)->setInitialValue($value);
		}
	}
	public function count(){
		$this->_makeAllProperties();
		return count($this->_properties);
	}
}


class zenMysqlResult extends zenMysqlQueryBuilder implements IteratorAggregate, Countable{// implements IteratorAggregate, ArrayAccess, Countable
	protected $_mysqlResult = null;
	protected $_finished = false;
	protected $_list = array();
	public function count(){
		$q = $this->_q($this->getCountSql());
		if ($q === false){
			throw new Exception(mysql_error($this->getLink())."\n".$sql);
		}else{
			return mysql_result($q,0);
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
	
}
class zenMysqlTable extends mysqlTable implements ArrayAccess, Countable, IteratorAggregate{
	private $_serverId = null;
	private $_databaseName = null;
	private $_tableName = null;
	private $_fCache = array();
	private $_fList = array();
	private $_isFullList = false;
	private $_uid = null;
	private $_defaults = array();
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

	public static function setTableModel($zenMysqlTable, $modelClass){
		self::$_models[$zenMysqlTable->getDatabaseName()][$zenMysqlTable->getTableName()] = $modelClass;
		self::$_tables[$modelClass] = $zenMysqlTable;
		$model = new $modelClass();
		self::registerForeignKeys($model);
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
	protected function _getWherePrimaryKeySql($item){
		$wherea = array();
		$pk = $item->primaryKeyGet();
		if (count($pk)){
			foreach ($pk as $fieldName){
				$property = $item[$fieldName];
				if ($property){
					$initialValue = $property->getInitialValue();
					if ($initialValue !== null){
						$wherea[] = "`$fieldName` = '".$this->_table->e($initialValue)."'";
					}
				}
			}
			if (count($pk) == count($wherea)){
				return " WHERE ".implode(" AND ", $wherea);
			}
		}
		return false;
	}
	protected function _getWhereSql($item){
		if (($whereSql = $this->_getWherePrimaryKeySql($item)) !== false){
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
		return "DELETE FROM `$tableName`".$this->_getWhereSql($item)." LIMIT 1";
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
		$sql = $this->_getDeleteSql($item);
		//echo $sql;
		$this->query($sql);
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