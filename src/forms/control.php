<?php
abstract class control{
	protected $_prefix = null; // <input name="prefix_control_name" />
	protected $_name = null; // <input name="control_name" />
	protected $_key = null; // <input name="control_name[key]" />
	protected $_value = null;
	protected $_defaultValue = null;
	protected $_required = false;
	// basic decorations
	protected $_title = '';
	// control set adapter
	protected $_controlSet = null;
	protected $_item = null;
	protected $_jsOnChangeCallback = '';
	protected $_options = array();
	protected $_repeatable = false; // name="name[]"
	protected $_inputCssClass = 'text';
	protected $_labelCssClass = 'text';
	protected $_afterTitle = '';
	
	protected $_property = null;
	//protected $_dataSources = array('GET', 'POST'); // GET/POST/FILES
	public function setOptions($options = array()){
		foreach ($options as $k => $v) $this->_options[$k] = $v;
	}
	public function setInputCssClass($cssClass){
		$this->_inputCssClass = $cssClass;
	}
	public function getInputCssClass(){
		if (isset($this->_options['inputCssClass'])){
			return $this->_options['inputCssClass'];
		}
		return $this->_inputCssClass;
	}
	public function setLabelCssClass($cssClass){
		$this->_labelCssClass = $cssClass;
	}
	public function getLabelCssClass(){
		if (isset($this->_options['labelCssClass'])){
			return $this->_options['labelCssClass'];
		}
		return $this->_labelCssClass;
	}
	public function __construct($controlName, $manualOnConstruct = false){
		$this->_name = $controlName;
		if (!$manualOnConstruct) $this->onConstruct();
	}
	public function onConstruct(){
	}
	public function error($errorString){
		if ($this->_controlSet !== null){
			$this->_controlSet->error($errorString);
		}
	}
	public function setControlSet($controlSet){
		$this->_controlSet = $controlSet;
		$this->setItem($this->_controlSet->getItem());
	}
	/**
	 * @return AControlSet
	 */
	public function getControlSet(){
		return $this->_controlSet;
	}
	public function setItem($item){
		$this->_item = $item;
	}
	public function setNote($note){
		$this->_note = $note;
	}
	public function getItem(){
		return is_object($this->_item)?$this->_item:false;
	}
	public function getItemPrimaryKey(){
		if ($item = $this->getItem()){
			return $item->primaryKey();
		}
		return false;
	}
	public function setRepeatable($repeatable = true){
		$this->_repeatable = $repeatable;
	}
	public function getRepeatable(){
		return $this->_repeatable;
	}
	public function setRequired($required){
		$this->_required = $required;
	}
	public function _setValue($value){
		$this->_value = $value;
		$this->onChange();
	}
	public function onChange(){
	}
	public function setDefaultValue($value){
		$this->_defaultValue = $value;
	}
	public function _getValue(){
		return ($this->_value === null?$this->_defaultValue:$this->_value);
	}
	public function setKey($key){
		$this->_key = $key;
	}
	public function getPostKey(){
		return $this->_key;
	}
	public function setPrefix($prefix){
		$this->_prefix = $prefix;
	}
	public function setName($name){
		$this->_name = $name;
	}
	public function getPostName(){ // for $_POST / $_FILES
		return ($this->_prefix===null?'':$this->_prefix.'_').$this->_name;
	}
	public function getName(){ // for Html
		return $this->getPostName().($this->getRepeatable()?'[]':($this->getPostKey()===null?'':'['.$this->getPostKey().']'));
	}
	public function setTitle($title){
		$this->_title = $title;
	}
	public function getTitle(){
		return $this->_title;
	}
	public function getNote(){
		if ($this->_note == '') return '';
		return ' '.$this->_note.'';
		return '';
	}
	public function getId(){
		return $this->getRepeatable()?false:($this->getPostName().($this->getPostKey()===null?'':'_'.$this->getPostKey().''));
	}
	// init
	public function isValidValue(){
		return $this->isValid();
	}
	public function isValid(){
		if (is_object($this->_property)){
			if (!$this->_property->isValid()){
				$this->error('Не заполнено поле "'.$this->getTitle().'"');
				return false;
			}
		}
		if ($this->_required && ($this->getValue() == '')){
			$this->error('Не заполнено поле "'.$this->getTitle().'"');
			return false;
		}
		return true; // always valid
	}
	public function isUpdated(){
		return $this->_isUpdated;
	}
	public function isRequired(){
		return $this->_required;
	}
	public function getPostKeys(){
		$name = $this->getPostName();
		if (isset($_POST[$name])){
			if (is_array($_POST[$name])){
				$keys = array_keys($_POST[$name]);
				return $keys;
			}else{
				return array(null);
			}
		}
		if (isset($_FILES[$name])){
			if (is_array($_FILES[$name]['tmp_name'])){
				$keys = array_keys($_FILES[$name]['tmp_name']);
				foreach ($keys as $k => $key){
					if ($_FILES[$name]['error'][$key] != UPLOAD_ERR_OK){
						unset($keys[$k]);
					}
				}
				return $keys;
			}else{
				if ($_FILES[$name]['error'] == UPLOAD_ERR_OK){
					return array(null);
				}
			}
		}
		return array();
	}
	public function inPost($key = null){
		return in_array($key, $this->getPostKeys());
	}
	public function fill($key){
		$this->_key = $key;
		$this->fillFromPost();
	}
	public function fillFromPost(){
		$name = ($this->_prefix===null?'':$this->_prefix.'_').$this->_name;
		if ($this->_key === null){
			if (isset($_POST[$name])){
				$this->setValue($_POST[$name]);
				return;
			}
		}else{
			if (isset($_POST[$name]) && isset($_POST[$name][$this->_key])){
				$this->setValue($_POST[$name][$this->_key]);
				return;
			}
		}
		//$this->setValue('');
	}
	public function beforeSave(){
	}
	public function afterSave(){
	}
	// html
	public function getIdHtml(){
		return $this->getId()?' id="'.$this->getId().'"':'';
	}
	public function getRowHtml($level){
		return '<tr><td valign="top" class="label" style="padding-left: '.($level*50).'px">'.($this->getId()?'<label class="'.$this->getLabelCssClass().'" for="'.$this->getId().'">':'').
		((strlen($this->getTitle()) || $this->isRequired())?
		'<span'.($this->_required?' title="Обязательно к заполнению"':'').'>'.$this->getTitle().($this->_required?' <b style="color: #f00;">*</b>':'').$this->_afterTitle.'</span>':'')
		.'</td><td>'.$this->getHtml().$this->getNote().''.($this->getId()?'</label>':'').'</td></tr>';
	}
	public function getHtml(){
		return '<input type="text"'.$this->getIdHtml().' class="'.$this->getInputCssClass().'" name="'.$this->getName().'" value="'.htmlspecialchars($this->getValue()).'" />';
	}
	
	
	
	
	

	public function setProperty($property){
		if (!is_object($property)){
			throw new Exception($property);
		}
		$this->_property = $property;
		if ($this->_value !== null){
			$this->_property->setValue($this->_value);
		}
		if ($property->getControl() === null){
			$property->setControl($this);
		}
	}
	public function getProperty(){
		return $this->_property;
	}
	public function setValue($value){
		$property = $this->getProperty();
		if ($property !== null && is_object($property)){
			$property->setValue($value);
		}else{
			$this->_setValue($value);
		}
		$this->onChange();
	}
	/**
	 * Prepare for DB
	 */
	public function importPropertyValue($propertyValue){
		$this->setValue($value);
	}
	public function exportValueToProperty(){
		return $this->getValue();
	}
	
	public function getValue(){
		$property = $this->getProperty();
		if ($property !== null && is_object($property)){
			$value = $property->getValue(false);
			if ($value !== null){
				return $value;
			}else{
				// default value of input is preffered
				if ($this->_defaultValue !== null){
					return $this->_defaultValue;
				}
				$propertyDefault = $property->getDefaultValue();
				if ($propertyDefault !== null){
					return $propertyDefault;
				}
				
			}
		}
		return $this->_getValue();
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