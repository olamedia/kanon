<?php
// prototype: http://code.google.com/p/php-rest-api/
class restClient{
	protected $_responseCode = 0;
	protected $_format = '';
	protected $_method = '';
	protected $_temporaryHeaders = array();
	protected $_headers = array();
	protected $_date = null;
	protected $_eventDispatcher = null;
	/**
	 * @return eventDispatcher
	 */
	public function getEventDispatcher(){
		return $this->_eventDispatcher;
	}
	public function __construct(){
		$this->_eventDispatcher = new eventDispatcher();
	}
	public function setHeader($headerString){
		$this->_headers[strtolower(array_shift(explode(':',$header)))] = $headerString;
	}
	public function setHeaders($headers){
		foreach ($headers as $headerString){
			$this->setHeader($headerString);
		}
	}
	public function getHeaders(){
		return $this->_headers;
	}
	public function getHeader($name){
		$name = strtolower($name);
		return isset($this->_headers[$name])?$this->_headers[$name]:false;
	}
	public function getDate(){
		if ($this->_date === null){
			$this->_date = time();
		}
		return $this->_date;
	}
	public function addTemporaryHeader($headerString){
		$this->_temporaryHeaders[strtolower(array_shift(explode(':',$headerString)))] = $headerString;
	}
	public function reset(){
		$this->_temporaryHeaders = array();
	}
	public function put($uri, $headers = array(), $get = array(), $post = array()){
		$this->reset();
		$this->_request('PUT', $uri, $headers, $get, $post);
	}
	public function delete($uri, $headers = array(), $get = array(), $post = array()){
		$this->reset();
		$this->_request('DELETE', $uri, $headers, $get, $post);
	}
	public function post($uri, $headers = array(), $get = array(), $post = array()){
		$this->reset();
		$this->_request('POST', $uri, $headers, $get, $post);
	}
	public function get($uri, $headers = array(), $get = array(), $post = array()){
		$this->reset();
		$this->_request('GET', $uri, $headers, $get, $post);
	}
	protected function _request($method, $uri, $headers = array(), $get = array(), $post = ''){
		$this->_method = $method;
		$this->setHeaders($headers);
		$this->_date = $this->getDate();
		$headers[] = 'Date: '.date('r', $this->_date);
		$ch = curl_init($url);
		$getFields = array();
		foreach ($get as $k => $v){
			$getFields[] = urlencode($k).'='.urlencode($v);
		}
		if (count($getFields)){
			$uri .= '?'.implode('&', $getFields);
		}
		if ($method === 'POST'){
			curl_setopt($ch, CURLOPT_POST, true);
		}
		if (is_array($post)){
			$postFields = array();
			foreach ($post as $k => $v){
				$postFields[] = $k.'='.$v;
			}
			$body = implode('&',$postFields);
		}else{
			$body = $post;
		}
		if ($method == 'POST' || $method != 'PUT'){
			// This is required for all PUT and POST requests.
			$headers[] = "Content-Length: ".strlen($postFields);
		}
		$this->_eventDispatcher->notify(new event($this,'rest:before',array()));
		curl_setopt($ch, CURLOPT_URL, $uri);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 4);
		curl_setopt($ch, CURLOPT_TIMEOUT, 8);
		if ($method != 'POST' && $method != 'GET'){
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
		}
		$response = curl_exec($ch);
		$info = curl_getinfo($ch);
		$this->_responseCode = $info['http_code'];
		curl_close($ch);
		return $this->getObject($response);
	}
	public function setFormat($format){
		$this->_format = $format;
	}
	public function getObject($response){
		if ($response === false) return false;
		if (!preg_match('/^2[0-9]{2}$/', $this->_responseCode)) return false;
		switch ($this->_format){
			case 'json':
			case 'js':
				return json_decode($response);
				break;
			case 'xml':
			case 'atom':
			case 'rss':
				return simplexml_load_string($response);
				break;
			case 'php':
			case 'php_serial':
				return unserialize($response);
			default:
				return $response;
		}
	}
}