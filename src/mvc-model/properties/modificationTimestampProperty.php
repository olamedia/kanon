<?php
require_once dirname(__FILE__).'/timestampProperty.php';
class modificationTimestampProperty extends timestampProperty{
	public function preSave(){
		$this->setValue(time());
	}
}