<?php
#require_once dirname(__FILE__).'/simpleStorageInput.php';
class inputFile implements simpleStorageInput{
	protected $_filename = null;
	public function __construct($filename){
		$this->_filename = $filename;
	}
	public function getFilename(){
		return $this->_filename;
	}
	public function getContents(){
		return file_get_contents($this->getFilename());
	}
}