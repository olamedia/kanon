<?php
#require_once dirname(__FILE__).'/simpleStorageInput.php';
class inputResource implements simpleStorageInput{
	protected $_resource = null;
	protected $_bufferSize = 0;
	public function __construct(&$resource, $bufferSize){
		$this->_resource = &$resource;
		$this->_bufferSize = $bufferSize;
	}
	public function &getResource(){
		return $this->_resource;
	}
	public function getBufferSize(){
		return $this->_bufferSize;
	}
	public function getContents(){

	}
}