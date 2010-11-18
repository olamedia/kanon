<?php
#require_once dirname(__FILE__).'/../modelProperty.php';

class integerProperty extends modelProperty{
	protected $_dataType = modelProperty::TYPE_INTEGER;
	protected $_dataSize = 20;
	protected $_dataUnsigned = false;
	public function setValue($value){
		if (is_string($value)){
			$value = preg_replace('#([^0-9\.\,]+)#ims', '', $value);
		}
		$this->_value = $value;
	}
	public function getValue(){
		$value = parent::getValue();
		return intval($value);
	}
}