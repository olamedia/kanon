<?php

/**
 * Description of request
 *
 * @author olamedia
 */
class request{
	public static function isCli(){
		return (PHP_SAPI=='cli');
	}
	public static function isAjax(){
		return 'XMLHttpRequest'==self::getHttpHeader('X-Requested-With');
	}
	public static function getMethod(){
		return $_SERVER['REQUEST_METHOD'];
	}
	public static function getHttpHeader($name, $default = null){
		return self::getServerParameter('HTTP_'.strtoupper(strtr($name, '-', '_')), $default);
	}
	public static function getServerParameter($name, $default = null){
		return isset($_SERVER[$name])?$_SERVER[$name]:$default;
	}
	public static function getUri($default = ''){
		// DOCUMENT_URI - nginx SSI include fix (REQUEST_URI = /)
		return self::getServerParameter('DOCUMENT_URI', self::getServerParameter('REQUEST_URI', $default));
	}
	public static function getServerName($default = ''){
		return self::getServerParameter('HTTP_HOST', self::getServerParameter('SERVER_NAME', $default));
	}
	public static function getReferer($default = false){
		return self::getServerParameter('HTTP_REFERER', $default);
	}
	/**
	 * Get current domain name, !excluding www. prefix
	 * @return string Domain name
	 */
	public static function getDomainName(){
		$da = explode(".", self::getServerName());
		reset($da);
		if ($da[0]=='www'){
			array_shift($da);
		}
		return implode(".", $da);
	}
}

