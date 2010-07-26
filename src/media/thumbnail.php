<?php
require_once dirname(__FILE__).'/image.php';
class thumbnail extends image{
	public function __construct($filename, $sourceImage, $rectangle){
		parent::__construct($filename);
		$this->fromArea($sourceImage, $rectangle);
		$this->save();
	}
}