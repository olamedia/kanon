<?php
// bucket.commondatastorage.googleapis.com/object
// commondatastorage.googleapis.com/bucket/object
class simpleStorageGoogleStorageDriver{
	protected $_path = null;
	protected $_uri = null;
	public function __construct($path, $uri){
		
	}
	public function getUri($bucket, $uri){
		return 'http://'.$bucket.'.commondatastorage.googleapis.com/'.$uri;
		return 'http://commondatastorage.googleapis.com/'.$bucket.'/'.$uri;
	}
	/**
	 * Creates a bucket and applies ACLs to a bucket.
	 * @param string $bucketName
	 * @return boolean
	 */
	public function putBucket($bucketName){
		
	}
	/**
	 * Get list of bucket names
	 * @return array
	 */
	public function getBuckets(){
		
	}
	/**
	 * Deletes an empty bucket.
	 * @param string $bucketName
	 * @return boolean
	 */
	public function deleteBucket($bucketName){
		
	}
	/**
	 * Uploads an object or applies object ACLs.
	 * @param string $bucketName
	 * @param inputFile $input
	 * @param string $uri
	 * @return boolean
	 */
	public function putObject($bucketName, $input, $uri){
		
	}
	/**
	 * Deletes an object.
	 * @param string $bucketName
	 * @param string $uri
	 * @return boolean
	 */
	public function deleteObject($bucketName, $uri){
		
	}
}