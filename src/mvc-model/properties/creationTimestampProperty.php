<?php
require_once dirname(__FILE__).'/timestampProperty.php';
class creationTimestampProperty extends timestampProperty{
	public function preInsert(){
		$this->setValue(time());
	}
}