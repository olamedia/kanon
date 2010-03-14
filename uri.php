<?php
/**
 * Class representation of relative uri string
 * @author olamedia 
 */
class uri{
	/**
	 * Path section of URI
	 * @var array
	 */
	protected $_path = array();
	/**
	 * Query section of URI (after ?)
	 * @var array
	 */
	protected $_args = array();
	/**
	 * Get current domain name, excluding www. prefix
	 * @return string Domain name
	 */
	public static function getDomainName(){
		$da = explode(".", $_SERVER['SERVER_NAME']);
		reset($da);
		if ($da[0] == 'www'){
			array_shift($da);
		}
		return implode(".", $da);
	}
	/**
	 * Set path section of URI
	 * @param array $path
	 * @return uri Self
	 */
	public function setPath($path){
		$this->_path = $path;
		// realpath() for uri (stripping ".."):
		$k1 = false;
		$before = array();
		foreach ($this->_path as $k2 => $dir){
			if ($k1 !== false && ($this->_path[$k1] !== '..')){
				if ($dir == '..'){
					unset($this->_path[$k1]);
					unset($this->_path[$k2]);
					$k1 = $before[$k1];
					$k2 = false;
					unset($before[$k1]);
				}
			}
			if ($k2 !== false){
				$before[$k2] = $k1;
				$k1 = $k2;
			}
		}
		return $this;
	}
	/**
	 * Get path section of URI
	 * @return array
	 */
	public function getPath(){
		return $this->_path;
	}
	/**
	 * Get directory name from beginning of URI path
	 * @param integer $shift Position from beginning to get
	 * @return string
	 */
	public function getBasePath($shift = 0){
		reset($this->_path);
		for($i=0;$i<$shift;$i++) next($this->_path);
		return current($this->_path);
	}
	/**
	 * Set query section of URI
	 * @param array $args
	 * @return uri
	 */
	public function setArgs($args){
		$this->_args = $args;
		return $this;
	}
	/**
	 * Get query section of URI
	 * @return array
	 */
	public function getArgs(){
		return $this->_args;
	}
	/**
	 * Make uri object from relative path string
	 * @param string $uriString Relative url
	 * @return uri
	 */
	public static function fromString($uriString){
		$uri = new uri();
		$qpos = strpos($uriString, '?');
		$get = '';
		if ($qpos !== false){
			$get = substr($uriString, $qpos+1);
			$uriString = substr($uriString, 0, $qpos);
		}
		$geta = explode("&", $get);
		$args = array();
		foreach ($geta as $v){
			list($k, $v) = explode("=", $v);
			$args[$k] = $v;
		}
		// cut index.php
		$path = explode("/", $uriString);
		foreach ($path as $k => $v){
			if ($v == '') unset($path[$k]); else{
				$path[$k] = urldecode($v);
			}
		}
		foreach ($args as $k => $v){
			if ($v == '') unset($args[$k]);
		}
		$uri->setPath($path);
		$uri->setArgs($args);
		return $uri;
	}
	/**
	 * Make uri object from $_SERVER['REQUEST_URI']
	 * @return uri
	 */
	public static function fromRequestUri(){
		return uri::fromString($_SERVER['REQUEST_URI']);
	}
	/**
	 * Subtract $baseUri from left part of URI
	 * @param string|uri $baseUri
	 * @return uri
	 */
	public function subtractBase($baseUri){
		if (is_string($baseUri)) $baseUri = uri::fromString($baseUri);
		$basepath = $baseUri->getPath();
		$path = $this->_path;
		foreach ($basepath as $basedir){
			$dir = array_shift($path);
			if ($dir !== $basedir){
				throw new Exception('base dir not found');
			}
		}
		$this->_path = $path;
		return $this;
	}
	/**
	 * Return string representation of URI
	 * @return string
	 */
	public function __toString(){
		return '/'.implode('/',$this->_path);
	}
}