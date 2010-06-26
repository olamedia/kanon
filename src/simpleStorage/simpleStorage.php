<?php
class simpleStorage{
	private $_name = null;
	/**
	 * 
	 * @var simpleStorageLocalDriver
	 */
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
	/**
	 * Get list of bucket names
	 * @return array
	 */
	public function getBuckets(){
		return $this->_driver->getBuckets();
	}
	/**
	 * Get a bucket
	 * @param string $bucketName
	 * @return simpleStorageBucket
	 */
	public function getBucket($bucketName = ''){
		if (!isset($this->_buckets[$bucketName])){
			$this->_buckets[$bucketName] = new simpleStorageBucket($this,$bucketName);
		}
		return $this->_buckets[$bucketName];
	}
	/**
	 * Creates a bucket and applies ACLs to a bucket.
	 * @param string $bucketName
	 * @return boolean
	 */
	public function putBucket($bucketName){
		return $this->_driver->putBucket($bucketName);
	}
	/**
	 * Deletes an empty bucket.
	 * @param string $bucketName
	 * @return boolean
	 */
	public function deleteBucket($bucketName){
		return $this->_driver->deleteBucket($bucketName);
	}
}