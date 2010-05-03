<?php
require_once dirname(__FILE__).'/stringProperty.php';
class randomHashProperty extends stringProperty{
	public function generate(){
		srand((double) microtime() * 1000000);
		$this->setValue(md5(uniqid(rand())));
	}
}