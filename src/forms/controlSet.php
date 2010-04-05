<?php
require_once dirname(__FILE__).'/control.php';
abstract class controlSet{
	protected $_controls;
	protected $_classesMap = array(); // controlName => class
	protected $_titles = array(); // controlName => title
	protected $_required = array(); // controlName => required
	protected $_propertiesMap = array(); // controlName => propertyName
	protected $_options = array(); // control options
	protected $_errors;
	protected $_prefix = null;
	protected $_key = null;
	protected $_legend = '';
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
	public function getKey(){
		return $this->_key;
	}
	public function setLegend($legend){
		$this->_legend = $legend;
	}
	public function getLegend(){
		return $this->_legend;
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
		$this->setItemTemplate($item);
		$this->_item = $item;
	}
	public function getItem(){
		return $this->_item;
	}
	public function setClasses($classes){
		$this->_classesMap = $classes;
	}
	public function setClass($controlName, $className){
		$this->_classesMap[$controlName] = $className;
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
	public function &getControl($controlName){
		if (!isset($this->_controls[$controlName])){
			if (!isset($this->_classesMap[$controlName])){
				return null;
			}
			$class = $this->_classesMap[$controlName];
			if (is_subclass_of($class, 'controlSet')){

				$controlSet = new $class($controlName, true);
				//$controlSet->setControlSet($this);
				$this->_controls[$controlName] = $controlSet;
			}else{
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
		}
		return $this->_controls[$controlName];
	}
	public function resetControls(){
		$items = array();
		foreach ($this->_classesMap as $controlName => $class){
			$items[$controlName] = $this->getControl($controlName)->getItem();
		}
		$this->_controls = array();
		foreach ($this->_classesMap as $controlName => $class){
			$this->getControl($controlName)->setItem($items[$controlName]); // controlSet->getControl()->setItem
		}
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
		$this->_key = $key;
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
			if (is_subclass_of($class, 'control')){
				if (!isset($this->_hiddenControls[$controlName])){
					$controlKeys = $this->getControl($controlName)->getPostKeys();
					if (count($controlKeys)){
						$keys = array_unique(array_merge($keys, $controlKeys));
					}
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
			if (is_subclass_of($class, 'controlSet')){
				// skip
			}else{
				if (!isset($this->_hiddenControls[$controlName])){
					$control = $this->getControl($controlName);
					$control->setKey($key);
					$control->fillFromPost();
				}
			}
		}
	}
	public function isValidValues(){
		return $this->isValid();
	}
	public function isValid(){
		foreach ($this->_classesMap as $controlName => $class){
			if (is_subclass_of($class, 'controlSet')){
				// skip
			}else{
				if (!isset($this->_hiddenControls[$controlName])){
					if (!$this->getControl($controlName)->isValid()) {
						return false;
					}
				}
			}
		}
		return true;
	}
	public function beforeSave(){
		foreach ($this->_classesMap as $controlName => $class){
			if (is_subclass_of($class, 'controlSet')){
				// skip
			}else{
				if (!isset($this->_hiddenControls[$controlName])){
					$control = $this->getControl($controlName);
					$control->beforeSave();
				}
			}
		}
	}
	public function afterSave(){ // onSuccess
		foreach ($this->_classesMap as $controlName => $class){
			if (is_subclass_of($class, 'controlSet')){
				// skip
			}else{
				if (!isset($this->_hiddenControls[$controlName])){
					$control = $this->getControl($controlName);
					$control->afterSave();
				}
			}
		}
		foreach ($this->_classesMap as $controlName => $class){
			if (is_subclass_of($class, 'controlSet')){
				$controlSet = $this->getControl($controlName);
				if (isset($_COOKIE['debug'])){
					echo ' process '.$controlName.' ';
				}
				$controlSet->process();
				if ($controlSet->isUpdated()){
					//?
				}
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
		$this->_item = clone $this->_itemTemplate;
		return $this->_item;
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
	public function getTableRowsHtml($key = null, $level = 0){
		$h = '';
		$this->setKey($key);
		//var_dump($this->_classesMap);
		//var_dump(get_class($this));
		foreach ($this->_classesMap as $controlName => $class){
			if (!isset($this->_hiddenControls[$controlName])){
				$control = $this->getControl($controlName);
				if (is_subclass_of($control, 'controlSet')){
					$h .= '<tr><td style="padding-left: '.($level*50).'px"><h3>';
					$h .= ''.$control->getLegend().'';
					$h .= '</h3></td><td></tr>';//<table width="100%">';
					$h .= $control->getTableRowsHtml($key, $level + 1);
					//$h .= '</table></td></tr>';
				}else{
					$h .= $control->getRowHtml($level);
				}
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