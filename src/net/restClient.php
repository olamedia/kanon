<?php
// prototype: http://code.google.com/p/php-rest-api/
class restClient{
	protected $_format = '';
	public function put($uri, $headers = array(), $get = array(), $post = array()){
		$this->_request('PUT', $uri, $headers, $get, $post);
	}
	public function delete($uri, $headers = array(), $get = array(), $post = array()){
		$this->_request('DELETE', $uri, $headers, $get, $post);
	}
	public function post($uri, $headers = array(), $get = array(), $post = array()){
		$this->_request('POST', $uri, $headers, $get, $post);
	}
	public function get($uri, $headers = array(), $get = array(), $post = array()){
		$this->_request('GET', $uri, $headers, $get, $post);
	}
	protected function _request($method, $uri, $headers = array(), $get = array(), $post = array()){
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
			$postFields = array();
			foreach ($post as $k => $v){
				$postFields[] = $k.'='.$v;
			}
			curl_setopt($ch, CURLOPT_POSTFIELDS, implode('&',$postFields));
		}
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 4);
		curl_setopt($ch, CURLOPT_TIMEOUT, 8);
		$response = curl_exec($ch);
		$info = curl_getinfo($ch);
		curl_close($ch);
	}
	public function setFormat($format){
		$this->_format = $format;
	}
	public function getObject($info, $response){
		if ($response === false) return false;
		if (!preg_match('/^2[0-9]{2}$/', $info['http_code'])) return false;
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