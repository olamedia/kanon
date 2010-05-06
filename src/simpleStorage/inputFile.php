<?php
class inputFile{
	protected $_filename = null;
	public function __construct($filename){
		$this->_filename = $filename;
	}
	public function getFilename(){
		return $this->_filename;
	}
}