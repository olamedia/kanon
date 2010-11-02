<?php
#require_once dirname(__FILE__).'/../control.php';
class selectControl extends control{
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
	public function isValid(){
		return $this->isValidValue();
	}
	public function isValidValue(){
		if (!$this->_required) return true;
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
					if (is_array($optionTitle)){
						$optgroup = '<optgroup label='.htmlspecialchars($optionId).'>';
						foreach ($optionTitle as $optId => $optTitle){
							$optgroup .= '<option value="'.$optId.'"'.($selectedId==$optId?' selected="selected"':'').'>'.htmlspecialchars($optTitle).'</option>';
						}
						$optgroup .= '</optgroup>';
						$options[] = $optgroup;
					}else{
						$options[] = '<option value="'.$optionId.'"'.($selectedId==$optionId?' selected="selected"':'').'>'.htmlspecialchars($optionTitle).'</option>';
					}
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