<?php
require_once realpath(dirname(__FILE__).'/../').'/simpleStorageDriver.php';
// bucket.commondatastorage.googleapis.com/object
// commondatastorage.googleapis.com/bucket/object
class simpleStorageGoogleStorageDriver implements simpleStorageDriver{
	protected $_restClient = null;
	protected $_publicKey = '';
	protected $_privateKey = '';
	protected $_bucketName = '';
	protected $_uri = null;
	public function signRequest(){
		$method = $this->_restClient->getMethod();
		$bucketName = $this->_bucketName;
		$uri = $this->_uri;
		// MessageToBeSigned = UTF-8-Encoding-Of(CanonicalHeaders + CanonicalExtensionHeaders + CanonicalResource)
		// You construct the CanonicalHeaders portion of the message by concatenating several header values and adding a newline (U+000A) after each header value.
		$date = $this->_restClient->getHeaderValue('Date', '');
		$contentType = $this->_restClient->getHeaderValue('Content-Type', '');
		$contentMd5 = $this->_restClient->getHeaderValue('Content-MD5', '');
		$canonicalHeaders = $method."\n".
		$contentMd5."\n".
		$contentType."\n".
		$date."\n";
		$canonicalExtensionHeaders = ''; // FIXME
		$canonicalResource = '';
		if ($bucketName != '') $canonicalResource .= '/'.$bucketName;
		$canonicalResource .= '/'.$uri;
		$message = $canonicalHeaders.$canonicalExtensionHeaders.$canonicalResource;
		// Signature = Base64-Encoding-Of(HMAC-SHA1(UTF-8-Encoding-Of(YourGoogleStorageSecretKey, MessageToBeSigned)))
		$signature = base64_encode(hash_hmac('sha1', $message, $this->_privateKey, true));
		// Authorization: GOOG1 google_storage_access_key:signature
		$header = 'Authorization: GOOG1 '.$this->_publicKey.':'.$signature;
		// echo ' header='.$header;
		$this->_restClient->setHeader($header);
	}
	public function __construct($publicKey, $privateKey){
		$this->_publicKey = $publicKey;
		$this->_privateKey = $privateKey;
		$this->_restClient = new restClient();
		$this->_restClient->getEventDispatcher()->attach('rest:before', array($this,'signRequest'));
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
	public function getObject($bucketName, $uri){
		$this->_bucketName = $bucketName;
		$this->_uri = $uri;
		return $this->_restClient->get('http://'.$bucketName.'.commondatastorage.googleapis.com/'.$uri, array(
		'Content-Type: application/x-www-form-urlencoded'
		));
	}
	/**
	 * Deletes an object.
	 * @param string $bucketName
	 * @param string $uri
	 * @return boolean
	 */
	public function deleteObject($bucketName, $uri){
		$this->_bucketName = $bucketName;
		$this->_uri = $uri;
		$response = $this->_restClient->delete('http://'.$bucketName.'.commondatastorage.googleapis.com/'.$uri, array(
		'Content-Type: application/x-www-form-urlencoded'
		));
	}
}