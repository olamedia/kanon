<?php
class simpleStorageLocalDriver{
	protected $_path = null;
	protected $_uri = null;
	public function __construct($path, $uri){
		$this->_path = $path;
		$this->_uri = $uri;
	}
	public function getUri($bucket, $uri){
		return $this->_uri.($bucket==''?'':$bucket.'/').$uri;
	}
}