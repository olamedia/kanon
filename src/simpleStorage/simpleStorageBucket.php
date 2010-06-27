<?php
class simpleStorageBucket{
	/**
	 *
	 * @var simpleStorage
	 */
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
	public function getDriver(){
		return $this->_storage->getDriver();
	}
	public function getName(){
		return $this->_name;
	}
	public function getObjects(){

	}
	public function getObject($uri){
		//return new simpleStorageObject($this, $uri);
		return $this->getDriver()->getObject($this->getName(), $uri);
	}
	/**
	 * Uploads an object or applies object ACLs.
	 * @param inputFile $input
	 * @param string $uri
	 * @return boolean
	 */
	public function putObject($input, $uri){
		return $this->getDriver()->putObject($this->getName(), $input, $uri);
	}
	/**
	 * Deletes an object.
	 * @param string $uri
	 * @return boolean
	 */
	public function deleteObject($uri){
		return $this->getDriver()->deleteObject($this->getName(), $uri);
	}
}