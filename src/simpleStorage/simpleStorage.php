<?php
class simpleStorage{
	private $_name = null;
	private $_driver = null;
	private static $_instances = array();
	private $_buckets = array();
	protected function __construct($name){
		$this->_name = $name;
	}
	public static function getInstance($name){
		if (!isset(self::$_instances[$name])){
			self::$_instances[$name] = new self($name);
		}
		return self::$_instances[$name];
	}
	public function connect(){
		
	}
	public function getBuckets(){
	}
	public function getBucket($name){
		
	}
	public function putBucket($name){

	}
	public function deleteBucket($name){

	}
}