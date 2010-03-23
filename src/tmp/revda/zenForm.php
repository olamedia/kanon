<?php
require_once(realpath(dirname(__FILE__)).'/zenFormFiles.php');
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
	public function setValue($value){
		$this->_value = $value;
		$this->onChange();
	}
	public function onChange(){
	}
	public function setDefaultValue($value){
		$this->_defaultValue = $value;
	}
	public function getValue(){
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
	public function getRowHtml(){
		return '<tr><td valign="top" class="label">'.($this->getId()?'<label class="'.$this->getLabelCssClass().'" for="'.$this->getId().'">':'').
		((strlen($this->getTitle()) || $this->isRequired())?
		'<span'.($this->_required?' title="Обязательно к заполнению"':'').'>'.$this->getTitle().($this->_required?' <b style="color: #f00;">*</b>':'').$this->_afterTitle.'</span>':'')
		.'</td><td>'.$this->getHtml().$this->getNote().''.($this->getId()?'</label>':'').'</td></tr>';
	}
	public function getHtml(){
		return '<input type="text"'.$this->getIdHtml().' class="'.$this->getInputCssClass().'" name="'.$this->getName().'" value="'.$this->getValue().'" />';
	}
}

/******************************************************************************************
 *
 * controlSet
 *
 ******************************************************************************************/
abstract class controlSet{
	protected $_controls;
	protected $_classesMap = array(); // controlName => class
	protected $_titles = array(); // controlName => title
	protected $_required = array(); // controlName => required
	protected $_propertiesMap = array(); // controlName => propertyName
	protected $_options = array(); // control options
	protected $_errors;
	protected $_prefix = null;
	protected $_item = null;
	protected $_itemTemplate = null;
	protected $_hiddenControls = array();
	protected $_repeat = false;
	protected $_isUpdated = false;
	//===================================================================== getters && setters / options
	public function setOptions($options = array()){
		foreach ($options as $k => $v) $this->_options[$k] = $v;
	}
	public function hideControl($controlName){
		$this->_hiddenControls[$controlName] = true;
	}
	public function showControl($controlName){
		unset($this->_hiddenControls[$controlName]);
	}
	public function setRepeat($repeat = true){
		$this->_repeat = $repeat;
	}
	public function getRepeat(){
		return $this->_repeat;
	}
	public function isUpdated(){
		return $this->_isUpdated;
	}	
	public function setItemUpdated($updated = true){
		$this->_isUpdated = true;
	}	
	public function setItem($item){
		$this->_item = $item;
	}
	public function getItem(){
		return $this->_item;
	}
	public function setClasses($classes){
		$this->_classesMap = $classes;
	}
	public function setProperties($properties){
		$this->_propertiesMap = $properties;
	}
	public function setTitles($titles){
		foreach ($titles as $controlName => $title){
			$this->getControl($controlName)->setTitle($title);
		}
	}
	/**
	 * @return AControl
	 */
	public function getControl($controlName){
		if (!isset($this->_controls[$controlName])){
			if (!isset($this->_classesMap[$controlName])){
				return null;
			}
			$class = $this->_classesMap[$controlName];
			/** @var AControl */
			$control = new $class($controlName, true);
			$control->setControlSet($this);
			$control->setPrefix($this->_prefix);
			if (isset($this->_options[$controlName])) $control->setOptions($this->_options[$controlName]);
			$this->_controls[$controlName] = $control;
			if (isset($this->_propertiesMap[$controlName])){
				$propertyName = $this->_propertiesMap[$controlName];
				if ($this->_item !== null){
					$control->setProperty($this->_item->{$propertyName});
				}
			}
			if (isset($this->_titles[$controlName])){
				$title = $this->_titles[$controlName];
				$control->setTitle($title);
			}
			if (isset($this->_notes[$controlName])){
				$note = $this->_notes[$controlName];
				$control->setNote($note);
			}
			if (isset($this->_required[$controlName])){
				$required = $this->_required[$controlName];
				$control->setRequired($required);
			}
			$control->setRepeatable($this->getRepeat()?true:false);
			$control->onConstruct();
		}
		return $this->_controls[$controlName];
	}
	public function resetControls(){
		$this->_controls = array();
	}
	public function save(){
		if ($this->getItem() !== null){
			$result = $this->getItem()->save();
			//var_dump($result);
			return $result;
		}
	}
	public function error($errorString){
		$this->_errors[] = $errorString;
	}
	public function getErrors(){
		return $this->_errors;
	}
	public function setKey($key){
		foreach ($this->_classesMap as $controlName => $class){
			$control = $this->getControl($controlName);
			$control->setKey($key);
		}
	}
	//===================================================================== processing POST
	/**
	 * Get keys array for POST and FILES
	 */
	public function getPostKeys(){
		$keys = array();
		foreach ($this->_classesMap as $controlName => $class){
			if (!isset($this->_hiddenControls[$controlName])){
				$controlKeys = $this->getControl($controlName)->getPostKeys();
				if (count($controlKeys)){
					$keys = array_unique(array_merge($keys, $controlKeys));
				}
			}
		}
		//echo 'Keys:<br />';
		//var_dump($keys);
		if (!count($keys)) return false;
		
		return $keys;
	}
	public function inPost($key = null){
		foreach ($this->_classesMap as $controlName => $class){
			if (!isset($this->_hiddenControls[$controlName])){
				if (($foundKey = $this->getControl($controlName)->inPost($key)) !== false){
					return $foundKey;
				}
			}
		}
		return false;
	}
	public function fillFromPost($key = null){
		foreach ($this->_classesMap as $controlName => $class){
			if (!isset($this->_hiddenControls[$controlName])){
				$control = $this->getControl($controlName);
				$control->setKey($key);
				$control->fillFromPost();
			}
		}
	}
	public function isValidValues(){
		foreach ($this->_classesMap as $controlName => $class){
			if (!isset($this->_hiddenControls[$controlName])){
				if (!$this->getControl($controlName)->isValidValue()) {
					return false;
				}
			}
		}
		return true;
	}
	public function beforeSave(){
		foreach ($this->_classesMap as $controlName => $class){
			if (!isset($this->_hiddenControls[$controlName])){
				$control = $this->getControl($controlName);
				$control->beforeSave();
			}
		}
	}
	public function afterSave(){
		foreach ($this->_classesMap as $controlName => $class){
			if (!isset($this->_hiddenControls[$controlName])){
				$control = $this->getControl($controlName);
				$control->afterSave();
			}
		}
	}
	public function checkPost($key = null){
		$this->fillFromPost($key);
		if ($this->isValidValues()){
			$this->beforeSave();
			if ($this->save()){
				$this->afterSave();
				$this->setItemUpdated(true);
			}
		}
	}
	public function setItemTemplate($itemTemplate){
		$this->_itemTemplate = $itemTemplate;
	}
	public function getItemTemplate(){
		return clone $this->_itemTemplate;
	}
	public function process(){
		//echo 'Process<br />';
		$this->processPost();
	}
	public function processPost(){
		if ($keys = $this->getPostKeys()){
			if (is_array($keys) && count($keys)){
				foreach ($keys as $key){
					if (is_object($this->_itemTemplate)){
						$this->resetControls();
						$this->setItem($this->getItemTemplate());
					}
					$this->checkPost($key);
				}
			}
		}
	}

	//===================================================================== output HTML
	public function getTableRowsHtml($key = null){
		$h = '';
		$this->setKey($key);
		foreach ($this->_classesMap as $controlName => $class){
			if (!isset($this->_hiddenControls[$controlName])){
			$control = $this->getControl($controlName);
			$h .= $control->getRowHtml();
			}
		}
		return $h;
	}
	public function getTableHtml($key = null){
		$h = '';
		$h .= '<table>';
		$rh = $this->getTableRowsHtml($key);
		$repeat = 1;
		if ($this->getRepeat()) $repeat = $this->getRepeat();
		$h .= str_repeat($rh, $repeat);
		$h .= '</table>';
		return $h;
	}
	public function getHtml($key = null){
		return $this->getTableHtml($key);
	}
	public function getFormHtml($key = null){
		return 
		(count($this->getErrors())?'<div class="errors"><ul><li>'.implode("</li><li>", $this->getErrors()).'</li></ul></div>':'').
		'<form method="post" enctype="multipart/form-data" action="">'.$this->getHtml($key).'<input type="submit" value="Сохранить" /></form>';
	}
}

class propertyControl extends control{
	/**
	 * @var controllableProperty
	 */
	protected $_property = null;
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
			parent::setValue($value);
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
		return parent::getValue();
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
class textInput extends propertyControl{
}
class passwordInput extends textInput{
	public function getHtml(){
		return '<input type="password" autocomplete="off"'.$this->getIdHtml().' class="'.$this->getInputCssClass().'" name="'.$this->getName().'" value="'.$this->getValue().'" />';
	}
}
class checkboxInput extends propertyControl{ // 0/1 values
	protected $_inputCssClass = 'checkbox';
	public function getHtml(){
		return '<div style="margin: 5px 0;"><input type="hidden" name="'.$this->getName().'" value="0" />'.
		'<input type="checkbox"'.$this->getIdHtml().' class="'.$this->getInputCssClass().'" name="'.$this->getName().'" value="1"'.($this->getValue()?' checked="checked"':'').' /></div>';
	}
}
class textarea extends textInput{
	public function getHtml(){
		$style = array();
		if (isset($this->_options['width'])){
			$style[] = 'width: '.$this->_options['width'];
		}
		if (isset($this->_options['height'])){
			$style[] = 'height: '.$this->_options['height'];
		}
		$ss = count($style)?' style="'.implode(";", $style).'"':'';
		return '<textarea id="'.$this->getId().'" class="'.$this->getInputCssClass().'" name="'.$this->getName().'"'.$ss.'>'.$this->getValue().'</textarea>';
	}
}
class htmlTextarea extends textarea{
	protected $_inputCssClass = 'htmlarea';
	public function getHtml(){
		$html = parent::getHtml();
		$config = '';
		if (isset($this->_options['filemanager_url'])){
			$url = $this->_options['filemanager_url'];
			$config  = '<script type="text/javascript">var '.$this->getId().'_filemanager = "'.$url.'";</script>';
		}
		return $html.$config;
	}
	
}
class selectControl extends propertyControl{
	protected $_controlType = 'select'; // select, checkbox, radio
	protected $_multiple = false;
	protected $_options = array();
	protected $_invalidOptions = array();
	public function setControlType($controlType){
		$this->_controlType = $controlType;
	}
	public function setMultiple($multiple){
		$this->_multiple = $multiple;
	}
	public function setOptions($options){
		$this->_options = $options;
	}
	public function importPropertyValue($value){
		switch ($this->_controlType){
			case 'checkbox':
				if (strlen($value)){
					$values = explode('//', substr($value, 1, strlen($value)-2));
				}else{
					$values = array();
				}
				$this->setValue($values);
				break;
			case 'radio':
			case 'select':
			default:
				$this->setValue($value);
				break;
		}
	}
	public function exportValueToProperty(){
		switch ($this->_controlType){
			case 'checkbox':
				$values = $this->getValue();
				return '/'.implode('//', $values).'/';
				break;
			case 'radio':
			case 'select':
			default:
				return $this->getValue();
				break;
		}
	}
	public function isValidValue(){
		$values = array();
		if ($this->_controlType == 'checkbox'){
			$values = $this->getValue();
		}else{
			$values[] = $this->getValue();
		}
		//var_dump($values);
		foreach ($values as $value){
			if (in_array($value, $this->_invalidOptions)){
				$this->error('Неверно заполнено поле "'.$this->getTitle().'"');
				return false;
			}
		}
		foreach ($values as $value){
			if (!isset($this->_options[$value])){
				$this->error('Неверно заполнено поле "'.$this->getTitle().'"');
				return false;
			}
		}
		return true;
	}
	public function getHtml(){
		//'.$this->getValue().'
		$selectedId = $this->getValue();
		$options = array();
		$onchangehtml = '';
		if ($this->_jsOnChangeCallback !== ''){
			$onchangehtml .= ' onChange="'.$this->_jsOnChangeCallback.'"';
		}
		foreach ($this->_options as $optionId => $optionTitle){
			switch ($this->_controlType){
				case 'checkbox':
					if (!is_array($selectedId)) $selectedId = array();
					$options[] = '<input class="checkbox" name="'.$this->getName().'[]" type="checkbox" id="'.$this->getId().'_'.$optionId.'" value="'.$optionId.'"'.(in_array($optionId,$selectedId)?' checked="checked"':'').'><label for="'.$this->getId().'_'.$optionId.'">'.htmlspecialchars($optionTitle).'</label>';
					break;
				case 'radio':
					$options[] = '<input name="'.$this->getName().'" type="radio" id="'.$this->getId().'_'.$optionId.'" value="'.$optionId.'"'.($selectedId==$optionId?' checked="checked"':'').'><label for="'.$this->getId().'_'.$optionId.'">'.htmlspecialchars($optionTitle).'</label>';
					break;
				case 'select':
				default:
					$options[] = '<option value="'.$optionId.'"'.($selectedId==$optionId?' selected="selected"':'').'>'.htmlspecialchars($optionTitle).'</option>';
					break;
			}
		}
		switch ($this->_controlType){
			case 'checkbox':
			case 'radio':
				return '<div id="'.$this->getId().'_wrap"><div>'.implode('</div><div>',$options).'</div></div>';
				break;
			case 'select':
			default:
				return '<div id="'.$this->getId().'_wrap" class="select"><select'.$onchangehtml.' id="'.$this->getId().'" name="'.$this->getName().'">'.implode('',$options).'</select></div>';
				break;
		}

		//'.($this->_onChangeCallback ==''?'':' onChange="'.$this->_onChangeCallback.'"').'
		//return '<select></select>';
	}
}

/*
class zenFormControlSetData{
	protected $_controlSet = null;
	public function setControlSet($controlSet){
		$this->_controlSet = $controlSet;
	}
}
class 
class zenForm{
	protected $_controlSets = array();
	public function __construct(){
	}
	public function isValidValues(){
		foreach ($this->_controlSets as $controlSetData){
			if (!$controlSetData->getControlSet()->isValidValues()){
				return false;
			}
		}
		return true;
	}
	public function initControls(){
		
	}
	public function addControlSet($controlSetId, $controlSet){
		$data = new zenFormControlSetOptions();
		$data->setControlSet($controlSet);
		$this->_controlSets[$controlSetId] = $data;
	}
	public function isUpdated(){
		
	}
	public function getHtml(){
		
	}
}
class sampleForm extends zenForm{
	public function initControls(){
		
	}
}*/