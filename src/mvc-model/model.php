<?php
require_once dirname(__FILE__).'/properties/stringProperty.php';
require_once dirname(__FILE__).'/properties/integerProperty.php';
require_once dirname(__FILE__).'/properties/textProperty.php';
require_once dirname(__FILE__).'/properties/timestampProperty.php';
require_once dirname(__FILE__).'/properties/creationTimestampProperty.php';
require_once dirname(__FILE__).'/properties/modificationTimestampProperty.php';
require_once dirname(__FILE__).'/modelIterator.php';
class model implements ArrayAccess, IteratorAggregate{
	protected $_properties = array(); // propertyName => modelProperty
	protected $_classes = array(); // propertyName => className
	protected $_fields = array(); // propertyName => fieldName
	protected $_primaryKey = array(); // propertyNames
	protected $_autoIncrement = null; // propertyName
	protected $_foreignKeys = array(); // property => array(foreignClass, foreignProperty)
	protected $_options = array(); // propertyName => options
	//protected $_storage = null;
	//protected $_storageClass = 'modelStorage';
	public function __construct(){
		foreach ($this->_classes as $propertyName => $class){
			$this->_getProperty($propertyName);
		}
	}
	public function __clone(){
		//echo 'Clone ';
		foreach ($this->_classes as $propertyName => $class){
			$property = $this->_getProperty($propertyName);
			/** @var modelProperty $property */
			$property->setValue($property->getInitialValue());
			$property->setInitialValue(null);
		}
		foreach ($this->_primaryKey as $propertyName){
			//echo $pk.' ';
			$key = $this->_getProperty($propertyName);
			/** @var modelProperty */
			$key->setValue(null);
			$key->setInitialValue(null);
		}
	}
	public function destroy(){
		foreach ($this->_properties as $property){
			$property->destroy();
		}
		unset($this->_properties);
		unset($this->_options);
		unset($this);
	}
	public function isValid(){
		foreach ($this as $property){
			if (!$property->isValid()) return false;
		}
		return true;
	}
	public function isEmpty(){
		foreach ($this as $property){
			if (!$property->isEmpty()) return false;
		}
		return true;
	}
	/**
	 * @return model
	 */
	
	public function getCreateSql(){
		$t = $this->getTableName();
		$sql = "CREATE TABLE IF NOT EXISTS `$t` (\r\n";
		$set = array();
		foreach ($this->_fields as $propertyName => $fieldName){
			$property = $this->_getProperty($propertyName);
			$set[] = "\t".$property->getCreateSql().
			($property->getName() == $this->_autoIncrement?' AUTO_INCREMENT':'');
		}
		if (count($this->_primaryKey)){
			$a = array();
			foreach ($this->_primaryKey as $c) $a[] = "`".$c."`";
			$set[] = "\tPRIMARY KEY (".implode(",", $a).")\r\n";
		}
		$sql .= implode(",\r\n", $set);
		$sql .= ")";
		return $sql;
	}
	public function __sleep(){
		return array('_properties');//'_classesMap', '_fieldsMap', '_primaryKey', '_autoIncrement',
	}
	public function __wakeup(){}
	public static function &getCollection(){
		if (!function_exists('get_called_class')){
			require_once dirname(__FILE__).'/../common/compat/get_called_class.php';
			// PHP 5 >= 5.2.4
		}
		return modelCollection::getInstance(get_called_class()); // PHP 5 >= 5.3.0
	}
	public function getIterator(){
		foreach ($this->_classes as $propertyName => $class){
			$this->_getProperty($propertyName);
		}
		return new ArrayIterator($this->_properties);//, $this->_classes
	}
	public function getPrimaryKey(){
		return $this->_primaryKey;
	}
	public function getAutoIncrement(){
		return $this->_autoIncrement;
	}
	public function getFieldNames(){
		return $this->_fields;
	}
	public function getPropertyNames(){
		return array_keys($this->_classes);
	}
	public function getForeignKeys(){
		return $this->_foreignKeys;
	}
	public function toArray($showInternal = false){
		$a = array();
		foreach ($this->_classes as $propertyName => $class){
			$property = $this->_getProperty($propertyName);
			if ($showInternal){
				$a[$propertyName] = $property->getInternalValue();
			}else{
				$a[$propertyName] = $property->getValue();
			}
		}
		return $a;
	}
	public function setOptions($options = array()){
		foreach ($options as $k => $v) $this->_options[$k] = $v;
		return $this;
	}
	protected function &_getProperty($name){
		if (!isset($this->_properties[$name])){
			$class = 'stringProperty';
			if (isset($this->_classes[$name])){
				if (class_exists($this->_classes[$name])){
					$class = $this->_classes[$name];
				}
			}
			$this->_properties[$name] = new $class($name);
			if (isset($this->_fields[$name])){
				$this->_properties[$name]->setFieldName($this->_fields[$name]);
			}
			$this->_properties[$name]->setModel($this);
			if (isset($this->_options[$name]) && is_array($this->_options[$name])){
				$this->_properties[$name]->setOptions($this->_options[$name]);
			}
		}
		return $this->_properties[$name];
	}
	public function makeValuesInitial(){
		foreach ($this->_classes as $propertyName => $class){
			$property = $this->_getProperty($propertyName);
			if ($property->isChangedValue()){
				$property->setInitialValue($property->getValue());
				$property->setValue(null);
			}
		}
	}
	public function __get($name){
		return $this->_getProperty($name);
	}
	public function __set($name, $value){
		$this->_getProperty($name)->setValue($value);
	}
	// ArrayAccess
	public function offsetExists($offset){
		return in_array($this->_fields($offset));
	}
	public function offsetUnset($offset){
		// can't be unset
	}
	public function offsetGet($offset){
		if (($propertyName = array_search($offset, $this->_fields)) !== false){
			return $this->_getProperty($propertyName);
		}
		return new nullObject;
	}
	public function offsetSet($offset, $value){
		if (($propertyName = array_search($offset, $this->_fields)) !== false){
			$this->_getProperty($propertyName)->setValue($value);
		}
		return $this;
	}
	/**
	 * @return modelStorage
	 */
	public function &getStorage(){
		$storageId = storageRegistry::getInstance()->modelSettings[get_class($this)]['storage'];
		$storage = storageRegistry::getInstance()->storages[$storageId];
		return $storage;
	}
	/**
	 * @return string
	 */
	public function getTableName(){
		return storageRegistry::getInstance()->modelSettings[get_class($this)]['table'];
	}
	public function save($debug = false){
		$this->preSave();
		foreach ($this->_classes as $propertyName => $class){
			$property = $this->_getProperty($propertyName);
			$property->preSave();
			$control = $property->getControl();
			if ($control !== null){
				$control->preSave();
			}
		}
		$result = $this->getStorage()->saveModel($this, $debug);
		$this->postSave();
		foreach ($this->_classes as $propertyName => $class){
			$property = $this->_getProperty($propertyName);
			$property->postSave();
			$control = $property->getControl();
			if ($control !== null){
				$control->postSave();
			}
		}
		return $result;
	}
	public function insert($debug = false){
		$this->preInsert();
		foreach ($this->_classes as $propertyName => $class){
			$property = $this->_getProperty($propertyName);
			$property->preInsert();
		}
		$result = $this->getStorage()->insertModel($this, $debug);
		$this->postInsert();
		foreach ($this as $property){
			$property->postInsert();
		}
		return $result;
	}
	public function update($debug = false){
		$this->preUpdate();
		foreach ($this->_classes as $propertyName => $class){
			$property = $this->_getProperty($propertyName);
			$property->preUpdate();
		}
		$result = $this->getStorage()->updateModel($this, $debug);
		$this->postUpdate();
		foreach ($this->_classes as $propertyName => $class){
			$property = $this->_getProperty($propertyName);
			$property->postUpdate();
		}
		return $result;
	}
	public function delete(){
		$this->preDelete();
		foreach ($this->_classes as $propertyName => $class){
			$property = $this->_getProperty($propertyName);
			$property->preDelete();
		}
		$result = $this->getStorage()->deleteModel($this);
		$this->postDelete();
		foreach ($this->_classes as $propertyName => $class){
			$property = $this->_getProperty($propertyName);
			$property->postDelete();
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