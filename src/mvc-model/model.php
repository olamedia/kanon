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
	protected $_templateMode = false; 
	protected $_parentKey = null;
	public function getParent(){
		$models = modelCollection::getInstance(get_class($this));
		return $models->select()->where($models->{$this->_primaryKey[0]}->is($this->{$this->_parentKey}));
	}
	public function getChildren(){
		$models = modelCollection::getInstance(get_class($this));
		return $models->select()->where($models->{$this->_parentKey}->is($this->{$this->_primaryKey[0]}));
	}
	/*protected static function getId(){
		static $id = 0;
		$id++;
		return $id;
	}
	public function getModelId(){
		static $id = null;
		if ($id === null) $id = self::getId();
		return $id;
	}*/
	public static function find(){
		$args = func_get_args();
		if (!function_exists('get_called_class')){
			require_once dirname(__FILE__).'/../common/compat/get_called_class.php';
			// PHP 5 >= 5.2.4
		}
		$collection = modelCollection::getInstance(get_called_class());
		return call_user_func_array(array($collection, 'find'), $args);
	}
	public function keep(){ // protect from destroying after script ends (to allow saving in $_SESSION)
		$this->isDestroyed = true;
		foreach ($this->_properties as $property){
			keep($property); // destroy backlinks to model
		}
	}
	//protected $_storage = null;
	//protected $_storageClass = 'modelStorage';
	/**
	 * don't change properties on clone (for forms)
	 * @return model
	 */
	public function enableTemplateMode(){
		$this->_templateMode = true;
		return $this;
	}
	/**
	 * allow change properties on clone (for forms)
	 * @return model
	 */
	public function disableTemplateMode(){
		$this->_templateMode = false;
		return $this;
	}
	public function __construct(){
		// Compatibility with zenMysql2 ORM
		if (isset($this->_classesMap)){
			$this->_classes = $this->_classesMap;
		}
		if (isset($this->_fieldsMap)){
			$this->_fields = $this->_fieldsMap;
		}

		foreach ($this->_classes as $propertyName => $class){
			$this->_getProperty($propertyName);
		}
		$this->onConstruct();
	}
	public function onConstruct(){

	}
	public function __clone(){
		if (!$this->_templateMode){
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
	}
	protected $isDestroyed = false;
	public function __destruct(){
		//static $isDestroyed = false;
		if ($this->isDestroyed) return;
		//echo ' destruct ';
		$this->isDestroyed = true;
		foreach ($this->_properties as $k => &$property){
			destroy($property); // destroy backlinks to model
			unset($this->_properties[$k]);
		}
		foreach ($this->_classes as $k => $v){
			unset($this->_classes[$k]);
		}
		foreach ($this->_fields as $k => $v){
			unset($this->_fields[$k]);
		}
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
		$driver = $this->getStorage()->getDriver();
		// IF NOT EXISTS
		$sql = 'CREATE TABLE "'.$t.'" ('."\r\n";
		$set = array();
		foreach ($this->_fields as $propertyName => $fieldName){
			$property = $this->_getProperty($propertyName);
			$set[] = "\t".$property->getCreateSql($driver).
			($property->getName() == $this->_autoIncrement?' AUTO_INCREMENT':'');
		}
		if (count($this->_primaryKey)){
			$a = array();
			foreach ($this->_primaryKey as $c) $a[] = '"'.$this->_fields[$c].'"';
			$set[] = "\t".'PRIMARY KEY ('.implode(',', $a).')'."\r\n";
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
		//echo get_called_class();
		//var_dump(debug_backtrace());
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
	/**
	 *
	 * @param string $name
	 * @return modelProperty
	 */
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
			//$this->_properties[$name]->setModel($this);
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
				$property->forceSetValue(null);
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
		//$changed = false;
		foreach ($this->_classes as $propertyName => $class){
			$property = $this->_getProperty($propertyName);
			if ($property->isChangedValue()){
				//$changed = true;
				if (isset($_COOKIE['debug'])){
					echo ' changed '.$property->getName().' ';
				}
				return $this->save();
			}
		}
		//if ($changed){
		//	return $this->save();
		//}
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
		$this->makeValuesInitial();
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