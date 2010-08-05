<?php
#require_once dirname(__FILE__).'/integerProperty.php';
class versionProperty extends integerProperty{
	public function preInsert(){
		$this->setValue(1);
	}
	public function preUpdate(){
		$this->setValue($this->getValue()+1);
	}
}