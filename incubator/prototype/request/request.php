<?php
// TODO
class request{
	protected $_method = null;
	protected $_instance = null;
	public static function getInstance(){
		if (self::$_instance === null){
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	public function isXmlHttpRequest(){
		return 'XMLHttpRequest' == $this->getHttpHeader('X-Requested-With');
	}
	public function getHttpHeader($name, $default = null){
		return $this->getServerParameter('HTTP_'.strtoupper(strtr($name, '-', '_')), $default);
	}
	public function getServerParameter($name, $default = null){
		return isset($_SERVER[$name])?$_SERVER[$name]:$default;
	}
	public function getScheme(){
		return ($this->getServerParameter('HTTPS') == 'on') ? 'https' : 'http';
	}
	public function getMethod(){
		if ($this->_method === null){
			if (in_array($_SERVER['REQUEST_METHOD'],array('GET','POST','PUT','DELETE','HEAD'))){
				$this->_method = $_SERVER['REQUEST_METHOD'];
			}
		}
		return $this->_method;
	}
	public function getPreferredLanguage(array $cultures = null){

	}
}