<?php
class simpleStorage{
	private $_name = null;
	private $_driver = null;
	private static $_instances = array();
	private $_buckets = array();
	public function __construct($driver){
		$this->_driver = $driver;
	}
	//	public static function getInstance($name){
	//		if (!isset(self::$_instances[$name])){
	//			self::$_instances[$name] = new self($name);
	//		}
	//		return self::$_instances[$name];
	//	}
	public function getDriver(){
		return $this->_driver;
	}
	public function getBuckets(){
	}
	public function getBucket($name = ''){
		if (!isset($this->_buckets[$name])){
			$this->_buckets[$name] = new simpleStorageBucket($this,$name);
		}
		return $this->_buckets[$name];
	}
	public function putBucket($name){

	}
	public function deleteBucket($name){

	}
}