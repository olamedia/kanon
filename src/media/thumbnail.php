<?php
require_once dirname(__FILE__).'/image.php';
class thumbnail extends image{
	/**
	 * 
	 * @param string $filename
	 * @param image $sourceImage
	 * @param rectangle $rectangle
	 */
	public function __construct($filename, $sourceImage, $rectangle){
		parent::__construct($filename);
		$this->setType($sourceImage->getType());
		$this->fromArea($sourceImage, $rectangle);
		$this->save();
	}
}