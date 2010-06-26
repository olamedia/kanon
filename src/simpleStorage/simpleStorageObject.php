<?php
class simpleStorageObject{
	/**
	 * 
	 * @var simpleStorageBucket
	 */
	protected $_bucket = null;
	protected $_uri = null;
	public function __construct($bucket, $uri){
		$this->_bucket = $bucket;
		$this->_uri = $uri;
	}
	public function getUrl(){
		return $this->_bucket->getStorage()->getDriver()->getUri($this->_bucket, $this->_uri);
	}
	public function getAuthenticatedUrl($lifetime = 3600){
		
	}
	public function __toString(){
		return $this->getUrl();
	}
}