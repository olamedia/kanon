<?php
/**
 * filename represents a file name 
 * providing some useful functions to interact with file
 * 
 * @author olamedia
 */
class filename implements dataSource{
	protected $_filename = null;
	public function __construct($filename){
		$this->_filename = $filename;
	}
	public function getData(){
		return file_get_contents($this->_filename);
	}
	public function __toString(){
		return (string) $this->_filename;
	}
}
