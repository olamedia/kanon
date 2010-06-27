<?php
require_once realpath(dirname(__FILE__).'/../').'/simpleStorageDriver.php';
class simpleStorageLocalDriver implements simpleStorageDriver{
	protected $_path = null;
	protected $_uri = null;
	public function __construct($path, $uri){
		$this->_path = realpath($path).'/';
		$this->_uri = $uri;
	}
	public function getUri($bucket, $uri){
		return $this->_uri.($bucket==''?'':$bucket.'/').$uri;
	}
	/**
	 * Creates a bucket and applies ACLs to a bucket.
	 * @param string $bucketName
	 * @return boolean
	 */
	public function putBucket($bucketName){
		if (!is_dir($this->_path.$bucketName)){
			return mkdir($this->_path.$bucketName);
		}
		return true;
	}
	/**
	 * Get list of bucket names
	 * @return array
	 */
	public function listBuckets(){
		$buckets = array();
		foreach (glob($this->_path.'*',GLOB_ONLYDIR) as $name){
			$buckets[] = basename($name);
		}
		return $buckets;
	}
	public function listObjects($bucketName){
		
	}
	/**
	 * Deletes an empty bucket.
	 * @param string $bucketName
	 * @return boolean
	 */
	public function deleteBucket($bucketName){
		if (is_dir($this->_path.$bucketName)){
			return rmdir($this->_path.$bucketName);
		}
		return true;
	}
	/**
	 * Uploads an object or applies object ACLs.
	 * @param string $bucketName
	 * @param inputFile $input
	 * @param string $uri
	 * @return boolean
	 */
	public function putObject($bucketName, $input, $uri){
		$filename = $this->_path.$bucketName.'/'.$uri;
		if ($input instanceof simpleStorageInput){
			return file_put_contents($filename, $input->getContents());
		}
		return false;
	}
	public function getObject($bucketName, $uri){
		
	}
	/**
	 * Deletes an object.
	 * @param string $bucketName
	 * @param string $uri
	 * @return boolean
	 */
	public function deleteObject($bucketName, $uri){
		$filename = $this->_path.$bucketName.'/'.$uri;
		if (is_file($filename)){
			return unlink($filename);
		}
		return true;
	}
}