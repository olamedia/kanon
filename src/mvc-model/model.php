<?php
require_once dirname(__FILE__).'/properties/stringProperty.php';
require_once dirname(__FILE__).'/modelIterator.php';
class model implements ArrayAccess, IteratorAggregate{
	protected $_properties = array(); // array of modelProperty
	protected $_classes = array(); // propertyName => className
	protected $_fields = array(); // propertyName => fieldName
	protected $_primaryKey = array(); // propertyNames
	protected $_autoIncrement = null; // propertyName
	protected $_foreignKeys = array(); // property => array(foreignClass, foreignProperty)
	protected $_options = array(); // propertyName => options
	protected $_storage = null;
	protected $_storageClass = 'mysqlStorage';
	public function getIterator(){
		return new modelIterator($this, $this->_classes);
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
	public function getForeignKeys(){
		return $this->_foreignKeys;
	}
	public function toArray(){
		$a = array();
		foreach ($this as $name => $property){
			$a[$name] = $property->getValue();
		}
		return $a;
	}
	public function setOptions($options = array()){
		foreach ($options as $k => $v) $this->_options[$k] = $v;
		return $this;
	}
	protected function _getProperty($name){
		if (!isset($this->_properties[$name])){
			$class = 'stringProperty';
			if (isset($this->_classes[$name])){
				if (class_exists($this->_classes[$name])){
					$class = $this->_classes[$name];
				}
			}
			$this->_properties[$name] = new $class();
			$this->_properties[$name]->setModel($this);
			$this->_properties[$name]->setOptions($this->_options[$name]);
		}
		return $this->_properties[$name];
	}
	protected function _makeValuesInitial(){
		foreach ($this->_properties as $property){
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
	public function setStorage($storage){
		$this->_storage = $storage;
	}
	public function getStorage(){
		if ($this->_storage === null){
			$storageClass = $this->_storageClass;
			$this->_storage = new $storageClass();
		}
		return $this->_storage;
	}
	public function save(){
		$this->preSave();
		foreach ($this->_classes as $propertyName => $class){
			$property = $this->_getProperty($propertyName);
			$property->preSave();
			$control = $property->getControl();
			if ($control !== null){
				$control->preSave();
			}
		}
		$result = $this->getStorage()->saveModel($this);
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
	public function insert(){
		$this->preInsert();
		foreach ($this->_classes as $propertyName => $class){
			$this->_getProperty($propertyName)->preInsert();
		}
		$result = $this->getStorage()->insertModel($this);
		$this->postInsert();
		foreach ($this->_classes as $propertyName => $class){
			$this->_getProperty($propertyName)->postInsert();
		}
		return $result;
	}
	public function update(){
		$this->preUpdate();
		foreach ($this->_classes as $propertyName => $class){
			$this->_getProperty($propertyName)->preUpdate();
		}
		$result = $this->getStorage()->updateModel($this);
		$this->postUpdate();
		foreach ($this->_classes as $propertyName => $class){
			$this->_getProperty($propertyName)->postUpdate();
		}
		return $result;
	}
	public function delete(){
		$this->preDelete();
		foreach ($this->_classes as $propertyName => $class){
			$this->_getProperty($propertyName)->preDelete();
		}
		$result = $this->getStorage()->deleteModel($this);
		$this->postDelete();
		foreach ($this->_classes as $propertyName => $class){
			$this->_getProperty($propertyName)->postDelete();
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