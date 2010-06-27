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
	protected $_response = null;
	protected $_responseInfo = null;
	/**
	 * @return eventDispatcher
	 */
	public function getEventDispatcher(){
		return $this->_eventDispatcher;
	}
	public function __construct(){
		$this->_eventDispatcher = new eventDispatcher();
	}
	public function getMethod(){
		return $this->_method;
	}
	public function setHeader($headerString){
		$this->_headers[strtolower(array_shift(explode(':',$headerString)))] = $headerString;
	}
	public function setHeaders($headers){
		foreach ($headers as $headerString){
			$this->setHeader($headerString);
		}
	}
	public function getHeaders(){
		return $this->_headers;
	}
	public function getHeader($name, $default = false){
		$name = strtolower($name);
		return isset($this->_headers[$name])?$this->_headers[$name]:$default;
	}
	public function getHeaderValue($name, $default = false){
		$name = strtolower($name);
		if (isset($this->_headers[$name])){
			$a = explode(':',$this->_headers[$name]);
			array_shift($a);
			return trim(implode(':',$a));
		}
		return $default;
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
		$this->_headers = array();
	}
	public function put($uri, $headers = array(), $get = array(), $post = ''){
		$this->reset();
		return $this->_request('PUT', $uri, $headers, $get, $post);
	}
	public function delete($uri, $headers = array(), $get = array(), $post = ''){
		$this->reset();
		return $this->_request('DELETE', $uri, $headers, $get, $post);
	}
	public function post($uri, $headers = array(), $get = array(), $post = ''){
		$this->reset();
		return $this->_request('POST', $uri, $headers, $get, $post);
	}
	public function get($uri, $headers = array(), $get = array(), $post = ''){
		$this->reset();
		return $this->_request('GET', $uri, $headers, $get, $post);
	}
	protected function _request($method, $uri, $headers = array(), $get = array(), $post = ''){
		$this->_method = $method;
		$this->setHeaders($headers);
		$this->_date = $this->getDate();
		$this->setHeader('Date: '.date('r', $this->_date));
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
		if ($method == 'POST' || $method == 'PUT'){
			// This is required for all PUT and POST requests.
			$this->setHeader("Content-Length: ".strlen($postFields));
		}
		if ($method == 'PUT'){
			curl_setopt($ch, CURLOPT_PUT, true);
		}
		$this->_eventDispatcher->notify(new event($this,'rest:before',array()));
		curl_setopt($ch, CURLOPT_URL, $uri);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $this->_headers);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 4);
		curl_setopt($ch, CURLOPT_TIMEOUT, 8);
		if ($method != 'POST' && $method != 'GET' && $method != 'PUT'){
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
		}
		$this->_response = curl_exec($ch);
		$this->_responseInfo = curl_getinfo($ch);
		$this->_responseCode = $this->_responseInfo['http_code'];
		curl_close($ch);
		return $this->_response;//$this->getObject();
	}
	public function getResponseCode(){
		return $this->_responseCode;
	}
	public function setFormat($format){
		$this->_format = $format;
	}
	public function getObject(){
		if ($this->_response === false) return false;
		if (!preg_match('/^2[0-9]{2}$/', $this->_responseCode)) return false;
		switch ($this->_format){
			case 'json':
			case 'js':
				return json_decode($this->_response);
				break;
			case 'xml':
			case 'atom':
			case 'rss':
				return simplexml_load_string($this->_response);
				break;
			case 'php':
			case 'php_serial':
				return unserialize($this->_response);
			default:
				return $this->_response;
		}
	}
}