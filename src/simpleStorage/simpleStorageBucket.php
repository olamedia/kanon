<?php
class simpleStorageBucket{
	protected $_storage = null;
	protected $_name = null;
	public function __construct($storage, $name){
		$this->_storage = $storage;
		$this->_name = $name;
	}
	/**
	 * @return simpleStorage
	 */
	public function getStorage(){
		return $this->_storage;
	}
	public function getName(){
		return $this->_name;
	}
	public function getObject($uri){
		return new simpleStorageObject($this, $uri);
	}
	public function putObject($input, $uri){
		
	}
	public function deleteObject($uri){
		
	}
}