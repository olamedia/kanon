<?php

require_once dirname(__FILE__).'/properties/stringProperty.php';
require_once dirname(__FILE__).'/properties/integerProperty.php';
require_once dirname(__FILE__).'/properties/textProperty.php';
require_once dirname(__FILE__).'/properties/timestampProperty.php';
require_once dirname(__FILE__).'/properties/creationTimestampProperty.php';
require_once dirname(__FILE__).'/properties/modificationTimestampProperty.php';
require_once dirname(__FILE__).'/modelIterator.php';

class model extends extendable implements ArrayAccess, IteratorAggregate, Serializable{
	protected $_properties = array();
	// propertyName => modelProperty
	protected $_propertiesInfo = null;
	//
	protected $_classes = array();
	// propertyName => className
	protected $_fields = array();
	// propertyName => fieldName
	protected $_primaryKey = array();
	// propertyNames
	protected $_autoIncrement = null;
	// propertyName
	protected $_foreignKeys = array();
	// property => array(foreignClass, foreignProperty)
	protected $_options = array();
	// propertyName => options
	protected $_templateMode = false;
	protected $_parentKey = null;
	// ->getParent();
	protected $_titleKey = null;
	// ->__toString();
	protected $_values = array();
	// temporary storage for initial values
	protected $_actAs = array();
	protected $_isSaved = false;
	public function serialize(){
		var_dump(get_object_vars($this));
		return serialize(get_object_vars($this));
	}
	public function unserialize($data){
		$data = unserialize($data);
		foreach ($data as $k => $v){
			$this->{$k} = $v;
		}
	}
	public function markSaved($isSaved = true){
		$this->_isSaved = $isSaved;
	}
	public function isSaved(){
		return $this->_isSaved;
	}
	/**
	 *
	 * @param string $behaviourClass
	 */
	public function actAs($behaviorClass, $options = array()){
		//echo ' act as '.$behaviorClass.' ';
		$behavior = new $behaviorClass($this, $options);
		$this->extend($behavior);
	}
	public function syncWith(model $model){
		foreach ($model->export as $k => $v){
			$this->{'_'.$k} = $v;
		}
	}
	public function export(){
		return array(
			'properties' => $this->_properties,
			'classes' => $this->_classes,
			'fields' => $this->_fields,
			'primaryKey' => $this->_primaryKey,
			'autoIncrement' => $this->_autoIncrement,
			'foreignKeys' => $this->_foreignKeys,
			'options' => $this->_options,
			'parentKey' => $this->_parentKey,
			'titleKey' => $this->_titleKey,
			'values' => $this->_values,
		);
	}
	public function select(){
		$args = func_get_args();
		$collection = modelCollection::getInstance(get_class($this));
		return call_user_func_array(array($collection, 'select'), $args);
	}
	public function __toString(){
		if ($this->_titleKey!==null){
			return (string) $this->{$this->_titleKey};
		}
		return '';
	}
	public function __construct(){
		// Compatibility with zenMysql2 ORM
		if (isset($this->_classesMap)&&count($this->_classesMap)){
			$this->_classes = &$this->_classesMap;
		}
		if (isset($this->_fieldsMap)&&count($this->_fieldsMap)){
			$this->_fields = &$this->_fieldsMap;
		}
		foreach ($this->_actAs as $behaviourName => $options){
			if (is_array($options)){
				$this->actAs($behaviourName, $options);
			}else{
				$behaviourName = $options;
				$this->actAs($behaviourName);
			}
		}
		if (count($this->_properties)){
			/* $this->_properties = &$this->_properties;
			  $this->_propertiesInfo = &$this->_properties;
			  $this->_properties = array(); */
			//unset($this->_properties);
			foreach ($this->_properties as $propertyName => $propertyInfo){
				if (is_array($propertyInfo)){
					if (isset($propertyInfo['class']))
							$this->_classes[$propertyName] = $propertyInfo['class'];
					if (isset($propertyInfo['field']))
							$this->_fields[$propertyName] = $propertyInfo['field'];
					if (isset($propertyInfo['foreignKey']))
							$this->_foreignKeys[$propertyName] = $propertyInfo['foreignKey'];
					if (isset($propertyInfo['primaryKey'])&&$propertyInfo['primaryKey'])
							$this->_primaryKey[] = $propertyName;
					if (isset($propertyInfo['autoIncrement']))
							$this->_autoIncrement = $propertyName;
					if (isset($propertyInfo['parentKey'])) $this->_parentKey = $propertyName;
					if (isset($propertyInfo['titleKey'])) $this->_titleKey = $propertyName;
				}
			}
			//$this->_properties = array();
		}
		foreach ($this->_fields as $propertyName => $fieldName){
			$default = modelCollection::getDefaultValue(get_class($this), $propertyName, null);
			if ($default!==null){
				$this->{$propertyName} = $default;
			}
		}
		/* foreach ($this->_classes as $propertyName => $class){
		  $this->_getProperty($propertyName);
		  } */
		$this->onConstruct();
	}
	public function onConstruct(){

	}
	public function setInitialFieldValue($fieldName, $value){
		if (($propertyName = array_search($fieldName, $this->_fields))!==false){
			if (!isset($this->_properties[$propertyName])){
				$this->_values[$propertyName] = $value;
			}else{
				$this->_getProperty($propertyName)->setInitialValue($value);
			}
		}
	}
	/**
	 *
	 * @param string $name
	 * @return modelProperty
	 */
	protected function &_getProperty($name){
		if (!isset($this->_properties[$name])||!is_object($this->_properties[$name])){
			$class = 'stringProperty';
			if (isset($this->_classes[$name])&&class_exists($this->_classes[$name])){
				$class = $this->_classes[$name];
			}
			$property = new $class($name);
			$collection = modelCollection::getInstance($class);
			// $property->fieldConstruct($collection, $this->_fields[$name]);
			if (isset($this->_fields[$name])){
				$property->setFieldName($this->_fields[$name]);
			}
			//$this->_properties[$name]->setModel($this);
			if (isset($this->_options[$name])&&is_array($this->_options[$name])){
				$property->setOptions($this->_options[$name]);
			}
			if (isset($this->_values[$name])){
				$property->setInitialValue($this->_values[$name]);
			}
			$this->_properties[$name] = $property;
		}
		return $this->_properties[$name];
	}
	public function getRelative($relativeModelClass){
		//get_class
		$relativeModels = modelCollection::getInstance(($relativeModelClass));
		$models = modelCollection::getInstance(get_class($this));
		$list = $relativeModels->select();
		foreach ($this->_primaryKey as $propertyName){
			$list->where($models->{$propertyName}->is($this->{$propertyName}));
		}
		return $list;
	}
	public function getOneRelative($relativeModelClass){
		return $this->getRelative($relativeModelClass)->fetch();
	}
	public function getParent(){
		if ($this->_parentKey===null) return false;
		$models = modelCollection::getInstance(get_class($this));
		return $models->select()->where($models->{$this->_primaryKey[0]}->is($this->{$this->_parentKey}))->fetch();
	}
	public function getChildren($modelClass = null){
		if ($modelClass==get_class($this)){
			if ($this->_parentKey!==null){
				$models = modelCollection::getInstance(get_class($this));
				return $models->select()->where($models->{$this->_parentKey}->is($this->{$this->_primaryKey[0]}));
			}
		}
		$models = modelCollection::getInstance(get_class($this));
		if ($modelClass!==null){
			$subModels = modelCollection::getInstance($modelClass);
			$pk = $this->{$this->_primaryKey[0]};
			return $subModels->select()->where($models->{$this->_primaryKey[0]}->is($pk));
		}
		return $models->select()->where($models->{$this->_parentKey}->is($this->{$this->_primaryKey[0]}));
	}
	/* protected static function getId(){
	  static $id = 0;
	  $id++;
	  return $id;
	  }
	  public function getModelId(){
	  static $id = null;
	  if ($id === null) $id = self::getId();
	  return $id;
	  } */
	public static function findOne(){
		if (isset($this)){
			$class = get_class($this);
		}else{
			$args = func_get_args();
			if (!function_exists('get_called_class')){
				require_once dirname(__FILE__).'/../common/compat/get_called_class.php';
				// PHP 5 >= 5.2.4
			}
			$class = get_called_class();
		}
		$collection = modelCollection::getInstance($class);
		return call_user_func_array(array($collection, 'findOne'), $args);
	}
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
	public function __clone(){
		if (!$this->_templateMode){
			if (isset($_COOKIE['debug'])){
				echo ' clone, reset primary key ';
			}
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
	//protected $isDestroyed = false;
	//public function __destruct(){
		//static $isDestroyed = false;
		//if ($this->isDestroyed) return;
		//echo ' destruct ';
		//$this->isDestroyed = true;
		/*foreach ($this->_properties as $k => &$property){
			destroy($property); // destroy backlinks to model
			unset($this->_properties[$k]);
		}*/
		/*foreach ($this->_classes as $k => $v){
			unset($this->_classes[$k]);
		}
		foreach ($this->_fields as $k => $v){
			unset($this->_fields[$k]);
		}*/
	//}
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
		$sql = 'CREATE TABLE '.$driver->quoteField($t).' ('."\r\n";
		$set = array();
		foreach ($this->_fields as $propertyName => $fieldName){
			$property = $this->_getProperty($propertyName);
			$set[] = "\t".$property->getCreateSql($driver).
					($property->getName()==$this->_autoIncrement?' AUTO_INCREMENT':'');
		}
		if (count($this->_primaryKey)){
			$a = array();
			foreach ($this->_primaryKey as $c)
					$a[] = $driver->quoteField($this->_fields[$c]);
			$set[] = "\t".'PRIMARY KEY ('.implode(',', $a).')'."\r\n";
		}
		$sql .= implode(",\r\n", $set);
		$sql .= ")";
		return $sql;
	}
	/* public function __sleep(){
	  foreach ($this->_classes as $propertyName => $class){
	  $property = $this->_getProperty($propertyName);
	  }
	  return array('_properties', '_values', '_classes', '_fields', '_autoIncrement', '_primaryKey', '_parentKey', '_isSaved', '_options', '_foreignKeys', '_titleKey'); //'_classesMap', '_fieldsMap', '_primaryKey', '_autoIncrement',
	  }
	  public function __wakeup(){

	  } */
	/**
	 * @return modelCollection
	 */
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
		return new ArrayIterator($this->_properties); //, $this->_classes
	}
	public function getPrimaryKey(){
		return $this->_primaryKey;
	}
	public function getPrimaryKeyValue(){
		if (count($this->_primaryKey)==1){
			reset($this->_primaryKey);
			return $this->{current($this->_primaryKey)};
		}
		throw new Exception('Trying to get primary key value of multiple columns');
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
		if (($propertyName = array_search($offset, $this->_fields))!==false){
			return $this->_getProperty($propertyName);
		}
		return new nullObject;
	}
	public function offsetSet($offset, $value){
		if (($propertyName = array_search($offset, $this->_fields))!==false){
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
		if ($storage instanceof registry){
			throw new Exception('storage for '.get_class($this).' not registered');
		}
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
			if ($control!==null){
				$control->preSave();
			}
		}
		$result = $this->getStorage()->saveModel($this, $debug);

		$this->postSave();
		foreach ($this->_classes as $propertyName => $class){
			$property = $this->_getProperty($propertyName);
			$property->postSave();
			$control = $property->getControl();
			if ($control!==null){
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
	public function preSave(){

	}
	public function preInsert(){

	}
	public function preUpdate(){

	}
	public function preDelete(){

	}
	public function postSave(){

	}
	public function postInsert(){

	}
	public function postUpdate(){

	}
	public function postDelete(){

	}
}