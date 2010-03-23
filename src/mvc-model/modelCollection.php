<?php
require_once dirname(__FILE__).'/modelResultSet.php';
require_once dirname(__FILE__).'/modelField.php';
class modelCollection implements ArrayAccess{
	private static $_instances = array();
	protected $_modelName = null; // helper
	protected $_helper = null; // model instance
	protected $_uniqueId = null;
	public function offsetExists($offset){
		return in_array($offset, $this->getFieldNames());
	}
	public function __toString(){
		return $this->getUniqueId();
	}
	public function getTableName(){
		$tableName = storageRegistry::getInstance()->modelSettings[$this->_modelName]['table'];
		return is_string($tableName)?$tableName:false;
	}
	public function offsetGet($offset){
		return new modelField($this, $offset);
	}
	public function offsetSet($offset, $value){
		
	}
	public function offsetUnset($offset){
		
	}
	public function __get($name){
		$fields = $this->getHelper()->getFieldNames();
		if (isset($fields[$name])){
			return new modelField($this, $fields[$name]);
		}
		return null;
	}
	public function __set($name, $value){
		
	}
	public function select(){
		$args = func_get_args();
		array_unshift($args, $this);
		$result = new modelResultSet();
		call_user_func_array(array($result, 'select'), $args);
		return $result;
	}
	public function getFieldNames(){
		return $this->getHelper()->getFieldNames();
	}
	public function getConnection(){
		return $this->getHelper()->getStorage()->getConnection();
	}
	public function getUniqueId(){
		if ($this->_uniqueId === null){
			$this->_uniqueId = kanon::getUniqueId();
		}
		return $this->_uniqueId;
	}
	private function __construct($modelName){
		$this->_modelName = $modelName;
	}
	/**
	 * @return model
	 */
	public function getHelper(){
		if ($this->_helper === null){
			$this->_helper = new $this->_modelName;
		}
		return $this->_helper;
	}
	public static function getInstance($modelName){
		if (!isset(self::$_instances[$modelName])){
			self::$_instances[$modelName] = new self($modelName);
		}
		return self::$_instances[$modelName];
	}
}