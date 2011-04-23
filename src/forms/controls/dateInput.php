<?php
#require_once dirname(__FILE__).'/textInput.php';
class dateInput extends textInput{
	public function setValue($value){
		if (preg_match("#^([0-9]{1,2})\.([0-9]{1,2})\.([0-9]{4})$#ims", $value, $subs)){
			$m = ltrim($subs[2], '0');
			$d = ltrim($subs[1], '0');
			$y = $subs[3];
			$timestamp = mktime(0,0,0,$m,$d,$y);
			parent::setValue($timestamp);
		}
	}
	public function getValue(){
		$value = parent::getValue();
		if (!$value) return '';
		return date("d.m.Y", $value); 
	}
}