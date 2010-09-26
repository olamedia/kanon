<?php

class response{
	const
	HTTP_100='Continue',
	HTTP_101='Switching Protocols',
	HTTP_200='OK',
	HTTP_201='Created',
	HTTP_202='Accepted',
	HTTP_203='Non-Authorative Information',
	HTTP_204='No Content',
	HTTP_205='Reset Content',
	HTTP_206='Partial Content',
	HTTP_300='Multiple Choices',
	HTTP_301='Moved Permanently',
	HTTP_302='Found',
	HTTP_303='See Other',
	HTTP_304='Not Modified',
	HTTP_305='Use Proxy',
	HTTP_306='Temporary Redirect',
	HTTP_400='Bad Request',
	HTTP_401='Unauthorized',
	HTTP_402='Payment Required',
	HTTP_403='Forbidden',
	HTTP_404='Not Found',
	HTTP_405='Method Not Allowed',
	HTTP_406='Not Acceptable',
	HTTP_407='Proxy Authentication Required',
	HTTP_408='Request Timeout',
	HTTP_409='Conflict',
	HTTP_410='Gone',
	HTTP_411='Length Required',
	HTTP_412='Precondition Failed',
	HTTP_413='Request Entity Too Large',
	HTTP_414='Request-URI Too Long',
	HTTP_415='Unsupported Media Type',
	HTTP_416='Requested Range Not Satisfiable',
	HTTP_417='Expectation Failed',
	HTTP_500='Internal Server Error',
	HTTP_501='Not Implemented',
	HTTP_502='Bad Gateway',
	HTTP_503='Service Unavailable',
	HTTP_504='Gateway Timeout',
	HTTP_505='HTTP Version Not Supported';
	public static function magic($magic, $context = null){
		static $map = array(
	'html/head' => 'head.php',
	'html/header' => 'header.php',
	'html/footer' => 'footer.php'
		);
		// setup default responses
		$args = func_get_args();
		array_shift($args);
		$path = dirname(__FILE__).'/html/';
		magic::set('title', magic::get('title', 'Untitled page')); // LOL
		if (is_int($magic)){
			$file = $magic.'.php';
			if ($magic>300&&$magic<400)
				$file = '30x.php';
			$file = $path.$file;
			if (!is_file($file))
				$file = $path.'500.php';
			magic::set($magic, $file);
			array_unshift($args, false);
			array_unshift($args, $magic);
			return call_user_func_array(array('magic', 'call'), $args);
		}
		if (isset($map[$magic])){
			magic::set($magic, $path.$map[$magic]);
			array_unshift($args, false);
			array_unshift($args, $magic);
			return call_user_func_array(array('magic', 'call'), $args);
		}
	}
	protected static $_mime = 'text/html';
	protected static $_charset = 'utf-8';
	public static function setMime($mime){
		self::$_mime = $mime;
		self::_sendContentType();
	}
	public static function setCharset($charset){
		self::$_charset = $charset;
		self::_sendContentType();
	}
	private static function _sendContentType(){
		if (!request::isCli()){
			header("Content-Type: ".self::$_mime."; charset=".self::$_charset);
		}
	}
	public static function setStatus($code){
		if (!request::isCli()){
			$message = constant('self::HTTP_'.$code);
			header($_SERVER['SERVER_PROTOCOL'].' '.$code.' '.$message);
			magic::set('http/code', $code);
			magic::set('http/message', $message);
		}
	}
	public static function http($code, $location = null){
		self::setStatus($code);
		if ($location!==null){
			header('Location: '.$location, true, $code);
		}
		if (!magic::get('css')){
			self::css('body{background:#fff;color:#000;font-family:sans-serif;font-size: 12px;}');
			self::css('a{color:#00c}');
			self::css('a:visited{color:#551a8b}');
			self::css('a:hover{color:#f00}');
		}
		magic::call($code);
		exit;
	}
	private static function _preventRedirectLoop($location){
		if (request::getMethod()=='GET'&&
				$location==$_SERVER['HTTP_REFERER']&&
				$location==$_SERVER['REQUEST_URI']){
			self::http(500); // dirty enough
		}
	}
	public static function redirect($location){
		request::getMethod()=='GET'?
						self::movedPermanently($location):
						self::seeOther($location);
	}
	public static function css($value){
		magic::append('css', $value);
	}
	public static function back(){
		self::redirect($_SERVER['HTTP_REFERER']);
	}
	public static function seeOther($location){
		self::_preventRedirectLoop($location);
		self::http(303, $location);
	}
	public static function movedPermanently($location){
		self::_preventRedirectLoop($location);
		self::http(301, $location);
	}
	public static function forbidden(){
		self::http(403);
	}
	public static function notFound(){
		self::http(404);
	}
	public static function unauthorized(){
		self::http(401);
	}
}