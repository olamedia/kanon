<?php

class registry{
	/**
	 * The variables array
	 * @access private
	 */
	private $_vars = array();
	/**
	 * Set variable
	 * @param string $index
	 * @param mixed $value
	 * @return void
	 */
	public function __set($key, $value){
		$this->_vars[$key] = $value;
	}
	/**
	 * Get variable
	 * @param mixed $index
	 * @return mixed
	 */
	public function __get($key){
		if (isset($this->_vars[$key])) return $this->_vars[$key];
		return null;
	}
}

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


class controllerPrototype{
	protected $_me = null; // ReflectionClass
	protected $_parent = null;
	protected $_baseUri = null;
	protected $_relativeUri = null;
	protected $_childUri = '';
	protected $_action = '';
	protected $_options = array();
	public function __construct(){
		$this->_baseUri = uri::fromString('/');
		$this->_relativeUri = uri::fromRequestUri();
		$this->_me = new ReflectionClass(get_class($this));
	}
	/**
	 * Executing before run()
	 */
	public function onConstruct(){

	}
	/**
	 * Set parent controller
	 * @param controllerPrototype $parentController
	 */
	public function setParent($parentController){
		$this->_parent = $parentController;
	}
	/**
	 * Get parent controller
	 */
	public function getParent(){
		return $this->_parent;
	}
	/**
	 * Get current domain name without www
	 */
	public function getDomainName(){
		return uri::getDomainName();
	}
	/**
	 * Source for <link rel="canonical" href="" />
	 */
	public function getCanonicalUrl(){
		return 'http://'.$this->getDomainName().''.$this->rel("$this->_relativeUri"); // quotes required to not overwrite _relativeUri
	}
	/**
	 * Get current url excluding query
	 */
	public function getCurrentUrl(){
		return 'http://'.$_SERVER['SERVER_NAME'].''.reset(explode("?",$_SERVER['REQUEST_URI']));
	}
	public function setOptions($options = array()){
		$this->_options = $options;
	}
	/**
	 * Get $_SERVER['REQUEST_METHOD']
	 */
	public function getHttpMethod(){
		return $_SERVER['REQUEST_METHOD'];
	}
	public function setBaseUri($uriString, $autoRel = true){
		$this->_baseUri = uri::fromString($uriString);
		if ($autoRel){
			$this->_relativeUri = uri::fromRequestUri();
			$this->_relativeUri->subtractBase($this->_baseUri);
		}
	}

	public function setRelativeUriFromBase($uriString){
		$baseUri = uri::fromString($uriString);
		$this->_relativeUri = uri::fromRequestUri();
		$this->_relativeUri->subtractBase($baseUri);
	}
	/**
	 * Last method to run if another methods not found
	 * @param string $action
	 */
	protected function _action($action){
		return $this->notFound('Directory "'.$action.'" not found in '.get_class($this).'');
	}
	/**
	 * Get url relative to this controller (combine with controller's base uri)
	 * @param string|uri $relativeUri
	 * @param boolean $includeAction
	 * @return uri
	 */
	public function rel($relativeUri = '', $includeAction = false){
		if (is_string($relativeUri)) $relativeUri = uri::fromString($relativeUri);
		$a = array();
		if ($includeAction) $a[] = $this->_action;
		$relativeUri->setPath(array_merge($this->_baseUri->getPath(),$a,$relativeUri->getPath()));
		return $relativeUri;
	}
	/**
	 * Redirect with custom HTTP code
	 * @param string $message
	 */
	protected function _redirect($url = null, $httpCode = 303){
		//echo '<a href="'.$url.'">'.$url.'</a>';
		//exit;
		$title = 'Переадресация';
		if (!preg_match("#^[a-z]+:#ims",$url)){
			if (!preg_match("#^/#ims",$url)){
				$url = $this->rel($url, true);
			}
			$url = 'http://'.$this->getDomainName().$url;
		}
		$wait = 0;
		header("Location: ".$url, true, $httpCode);
		//header($_SERVER['SERVER_PROTOCOL']." 303 See Other");
		header("Content-type: text/html; charset=UTF-8");
		echo '<html><head>';
		echo '<title>'.$title.'</title>';
		echo '</head><body onload="doRedirect()" bgcolor="#ffffff" text="#000000" link="#0000cc" vlink="#551a8b" alink="#ff0000">';
		echo '<noscript>';
		echo '<meta http-equiv="refresh" content="'.$wait.'; url=&#39;'.htmlspecialchars($url).'&#39;">';
		echo '</noscript>';
		echo '<p><font face="Arial, sans-serif">Подождите...</font></p>';
		echo '<p><font face="Arial, sans-serif">Если переадресация не сработала, перейдите по <a href="'.$url.'">ссылке</a> вручную.</font></p>';
		echo '<script type="text/javascript" language="javascript">';
		echo 'function doRedirect() {';
		if (!$wait)	echo 'location.replace("'.$url.'");';
		echo '}';
		echo '</script>';
		echo '</body></html>';
		exit;
	}
	/**
	 * Exit with HTTP 403 error code
	 * @param string $message
	 */
	public function forbidden($message = ''){
		header($_SERVER['SERVER_PROTOCOL']." 403 Forbidden");
		echo $message;
		exit;
	}
	/**
	 * Exit with HTTP 404 error code
	 * @param string $message
	 */
	public function notFound($message = ''){
		header($_SERVER['SERVER_PROTOCOL']." 404 Not found");
		echo $message;
		// Google helper:
		/*echo '<script type="text/javascript">
		 var GOOG_FIXURL_LANG = "ru";
		 var GOOG_FIXURL_SITE = "http://'.$this->getDomainName().'";
		 </script>
		 <script type="text/javascript" src="http://linkhelp.clients.google.com/tbproxy/lh/wm/fixurl.js">
		 </script>';*/
		echo str_repeat('&nbsp; ', 100); // required to display custom error message (IE, Chrome)
		exit;
	}
	/**
	 * Redirect with HTTP 301 "Moved Permanently" code
	 * @param string $message
	 */
	public function movedPermanently($url){
		$this->_redirect($url, 301);
	}
	/**
	 * Redirect with HTTP 303 "See Other" code
	 * @param string $message
	 */
	public function seeOther($url){
		$this->_redirect($url, 303);
	}
	/**
	 * Redirect with HTTP 303 "See Other" code
	 * This is a recommended method to redirect after POST
	 * @param string $message
	 */
	public function redirect($url){
		$this->_redirect($url, 303);
	}
	/**
	 * Redirect to previous page
	 */
	function back(){
		$this->redirect($_SERVER['HTTP_REFERER']);
	}
	/**
	 * Deprecated
	 * @param string $message
	 */
	protected function _show403($message = ''){
		$this->forbidden($message);
	}
	/**
	 * Deprecated
	 * @param string $message
	 */
	protected function _show404($message = ''){
		$this->notFound($message);
	}
	protected function _header(){
		if ($c = $this->getParent()){
			if ($c->getParent()) echo "\r\n".'<div class="'.get_class($c).'_wrapper">';
			$c->_header();
		}
		$this->header();
		echo "\r\n".'<div class="'.get_class($this).'_content">';
	}
	protected function _footer(){
		echo "\r\n".'</div>';
		$this->footer();
		if ($c = $this->getParent()){
			$c->_footer();
			if ($c->getParent()) echo "\r\n".'</div>';
		}
	}
	public function header(){
	}
	public function _initIndex(){
	}
	public function index(){
	}
	public function footer(){
	}
	/**
	 * Get arguments for method $methodName from $predefinedArgs, $_GET, $_POST arrays, default value or setting them null.
	 * @param string $methodName
	 * @param array $predefinedArgs
	 * @return array
	 */
	protected function _getArgs($methodName, $predefinedArgs = array()){
		$method = $this->_me->getMethod($methodName);
		$parameters = $method->getParameters();
		$args = array();
		foreach ($parameters as $p){
			$name = $p->getName();
			if (isset($predefinedArgs[$name])){
				$value = $predefinedArgs[$name];
			}elseif (isset($_GET[$name])){
				$value = $_GET[$name];
			}elseif (isset($_POST[$name])){
				$value = $_POST[$name];
			}else{
				$value = $p->isDefaultValueAvailable()?$p->getDefaultValue():null;
			}
			$args[] = $value;//$name
		}
		return $args;
	}
	/**
	 * Make $this->_childUri from $this->_baseUri + $actions
	 * @param array $actions
	 * @return controllerPrototype
	 */
	protected function _makeChildUri($actions){
		$childUri = clone $this->_baseUri;
		$path = $childUri->getPath();
		foreach ($actions as $action){
			$path[] = $action;
		}
		$childUri->setPath($path);
		$this->_childUri = strval($childUri);
		return $this;
	}
	/**
	 * Forward action to another controller
	 * @param string $controllerClass
	 * @param string $relativePath
	 * @param array $options
	 */
	public function forwardTo($controllerClass, $relativePath = '', $options = array()){
		$controller = new $controllerClass();
		$controller->setParent($this);
		$controller->setBaseUri($this->rel($relativePath), false);
		$controller->setRelativeUriFromBase($this->_baseUri);
		$controller->setOptions($options);
		if (method_exists($controller, 'customRun')){
			$controller->customRun();
		}else{
			$controller->run();
		}
		exit;
	}
	/**
	 * Run another controller
	 * @param string $controllerClass
	 * @param array $options
	 */
	public function runController($controllerClass, $options = array()){
		$controller = new $controllerClass();
		$controller->setParent($this);
		$controller->setBaseUri($this->_childUri);
		$controller->setOptions($options);
		if (method_exists($controller, 'customRun')){
			$controller->customRun();
		}else{
			$controller->run();
		}
		exit;
	}
	/**
	 * Get route from docComments if possible
	 * @param $uri
	 * @param string $prefix
	 * @return array|false
	 */
	protected function _getRouteMethod($uri, $prefix = '!Route'){
		$this->_me = new ReflectionClass(get_class($this));
		$methods = $this->_me->getMethods();
		$maxIdentWeight = 0;
		$maxLength = 0;
		$result = false;
		//var_dump($methods);
		foreach ($methods as $method){
			//var_dump($method);
			//$method = $this->_me->getMethod($methodName);
			if ($doc = $method->getDocComment()){
				// 1. expand comment
				$doc = trim(preg_replace("#/\*\*(.*)\*/#ims", "\\1", $doc));
				// 2. search for !Route
				$la = explode("\n", $doc);
				$routes = array();
				foreach ($la as $line){
					if (($pos = strpos($line, $prefix)) !== false){
						$routes[] = substr($line, $pos + strlen($prefix) + 1);
					}
				}
				foreach ($routes as $route){
					$httpMethod = reset(explode(" ", $route));
					$route = trim(substr($route, strlen($httpMethod)));
					if ($httpMethod == $this->getHttpMethod() || strtoupper($httpMethod) == 'ANY'){
						//var_dump($route);
						$routePath = explode("/", $route);
						$path = $uri->getPath();
						$identical = false;
						$actions = array();
						$args = array();
						$identWeight = 0;
						$length = count($routePath);
						if (count($path) >= count($routePath)){
							$identical = true;
							foreach ($routePath as $rdir){
								$dir = array_shift($path);
								$rdir = array_shift($routePath);
								$actions[] = $dir;
								if (substr($rdir,0,1) != '$'){
									if ($dir != $rdir){
										$identical = false;
									}else{
										$identWeight++;
									}
								}else{
									$argName = substr($rdir,1);
									$args[$argName] = $dir;
								}
							}
						}
						$use = false;
						if ($identWeight > $maxIdentWeight){ // more identical directories
							$use = true;
						}else{
							if ($length > $maxLength){ // more variables
								$use = true;
							}
						}
						if ($identical && $use){
							//
							$maxIdentWeight = max($identWeight,$maxIdentWeight);
							$maxLength = max($length,$maxLength);
							$result = array($actions, $method->getName(), $args);
						}
						//var_dump($identical);
					}
				}
			}
		}
		return $result;
	}
	/**
	 * Run controller - select methods and run them
	 */
	public function run(){
		$methodFound = false;
		$class = get_class($this);
		if (strlen($this->_relativeUri) > 1){ // longer than "/"
			if ($this->getCurrentUrl() != $this->getCanonicalUrl()){
				$this->movedPermanently($this->getCanonicalUrl());
			}
		}
		if ($action = $this->_relativeUri->getBasePath()){
			$this->_action = $action;
		}
		$this->onConstruct();
		if (list($actions, $methodName, $pathArgs) = $this->_getRouteMethod($this->_relativeUri, '!RouteInit')){
			$this->_makeChildUri($actions);
			if (method_exists($this, $methodName)){
				call_user_func_array(array($this, $methodName), $this->_getArgs($methodName, $pathArgs));
			}
		}
		if (list($actions, $methodName, $pathArgs) = $this->_getRouteMethod($this->_relativeUri, '!Route')){
			$this->_makeChildUri($actions);
			if (method_exists($this, $methodName)){
				$methodFound = true;
				$args = $this->_getArgs($methodName, $pathArgs);
				if ($this->getHttpMethod() == 'GET') $this->_header();
				call_user_func_array(array($this, $methodName), $args);
				if ($this->getHttpMethod() == 'GET') $this->_footer();
				return;
			}
		}

		if ($this->_action){
			$uc = ucfirst($this->_action);
			$this->_makeChildUri(array($action));
			$initFunction = 'init'.$uc;
			if (method_exists($this, $initFunction)){
				$methodFound = true;
				call_user_func_array(array($this, $initFunction), $this->_getArgs($initFunction));
			}
			$actionFunction = 'action'.$uc;
			if (method_exists($this, $actionFunction)){
				$methodFound = true;
				call_user_func_array(array($this, $actionFunction), $this->_getArgs($actionFunction));
			}
			$showFunction = 'show'.$uc;
			if (method_exists($this, $showFunction)){
				$methodFound = true;
				$this->_header();
				call_user_func_array(array($this, $showFunction), $this->_getArgs($showFunction));
				$this->_footer();
			}
			if (!$methodFound){
				return $this->_action($action);
			}
		}else{
			if (method_exists($this, 'customIndex')){
				$this->customIndex();
			}else{
				$this->_initIndex();
				$this->_header();
				$this->index();
				$this->_footer();
			}
		}
	}
}



class controller extends controllerPrototype{
	protected $_startTime = null;
	public function __construct(){
		$this->_startTime = microtime(true);
		parent::__construct();
	}
	public function getRegistry(){
		return applicationRegistry::getInstance();
	}
	public function getApplication(){
		application::getInstance();
	}
	public function app(){
		return $this->getApplication();
	}
	/**
	 * Set base path for /images/, /css/ etc
	 * @param string $path
	 */
	public function setBasePath($path){
		$this->getRegistry()->basePath = $path;
		return $this;
	}
	public function getBasePath($path = null){
		if ($path !== null){
			return realpath($this->getBasePath().$path).'/';
		}
		if ($this->getRegistry()->basePath === null){
			return realpath(dirname(__FILE__).$this->_relativeBasePath).'/';
		}else{
			return realpath($this->getRegistry()->basePath).'/';
		}
	}
	/**
	 * Set html page <title>
	 * @param string $title
	 * @return controller
	 */
	public function setTitle($title){
		$this->getRegistry()->title = $title;
		return $this;
	}
	public function getTitle(){
		return $this->getRegistry()->title;
	}
	public function appendToBreadcrumb($links = array()){
		if (count($links)){
			if (!is_array($this->getRegistry()->breadcrumb)){
				$this->getRegistry()->breadcrumb = array();
			}
			foreach ($links as $link){
				$this->getRegistry()->breadcrumb[] = $link;
			}
		}
		return $this;
	}
	public function getBreadcrumb(){
		if (!is_array($this->getRegistry()->breadcrumb)){
			$this->getRegistry()->breadcrumb = array();
		}
		return $this->getRegistry()->breadcrumb;
	}
	public function viewBreadcrumb(){
		echo implode(" → ", $this->getBreadcrumb());
	}
	public function getUser(){
		return isset($_SESSION['site_user'])?$_SESSION['site_user']:null;
	}
	public function getUserId(){
		return is_object($this->getUser())?$this->getUser()->id->getValue():0;
	}
	public function requireCss($uri){
		if (!is_array($this->getRegistry()->cssIncludes)){
			$this->getRegistry()->cssIncludes = array();
		}
		$this->getRegistry()->cssIncludes[] = $uri;
	}
	public function css($cssString){
		$this->getRegistry()->plainCss .= $cssString;
	}
	public function requireJs($uri){
		if (!is_array($this->getRegistry()->javascriptIncludes)){
			$this->getRegistry()->javascriptIncludes = array();
		}
		$this->getRegistry()->javascriptIncludes[] = $uri;
	}
	public function getHeadContents(){
		$h = '<!DOCTYPE html>'; // html5
		$h .= '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
		$h .= '<title>'.$this->getTitle().'</title>';
		if (is_array($this->getRegistry()->cssIncludes)){
			foreach ($this->getRegistry()->cssIncludes as $url){
				$h .= '<link rel="stylesheet" type="text/css" href="'.$url.'" />';
			}
		}
		if (isset($this->getRegistry()->plainCss)){
			$h .= '<style type="text/css">';
			$h .= $this->getRegistry()->plainCss;
			$h .= '</style>';
		}
		if (is_array($this->getRegistry()->javascriptIncludes)){
			foreach ($this->getRegistry()->javascriptIncludes as $url){
				$h .= '<script type="text/javascript" src="'.$url.'"></script>';
			}
		}
		$h .= '<link rel="shortcut icon" href="/favicon.ico" />';
		return $h;
	}
	protected function &getDatabase($name = null){
		if ($name === null){
			return $this->getRegistry()->defaultDatabase;
		}
		if (!is_array($this->getRegistry()->databases)){
			$this->getRegistry()->databases = array();
		}
		return isset($this->getRegistry()->databases[$name])?$this->getRegistry()->databases[$name]:null;
	}

}


class application extends frontController{
	private static $_selfInstance = null;
	private static $_instance = null;
	/*public static function __construct(){
		parent::__construct();


	}*/
	public static function getInstance($controllerClassName = null){
		/*if (self::$_selfInstance === null){
			self::$_selfInstance = new self();
				
			}*/
		header($_SERVER['SERVER_PROTOCOL']." 200 OK");
		header("Content-Type: text/html; charset=utf-8");
		@set_magic_quotes_runtime(false);
		frontController::startSession('.'.uri::getDomainName());
		if (get_magic_quotes_gpc()){
			frontController::_stripSlashesDeep($_GET);
			frontController::_stripSlashesDeep($_POST);
		}
		if (self::$_instance === null && $controllerClassName !== null){
			self::$_instance = new $controllerClassName();
		}
		return self::$_instance;
	}

}
function app(){
	return application::getInstance();
}


class kanon{
	public static function getBaseUri(){
		$requestUri = $_SERVER['REQUEST_URI'];
		$scriptUri = $_SERVER['SCRIPT_NAME'];
		$max = min(strlen($requestUri), strlen($scriptUri));
		$cmp = 0;
		for ($l = 1; $l <= $max; $l++){
			if (substr_compare($requestUri, $scriptUri, 0, $l, true) === 0){
				$cmp = $l;
			}
		}
		return substr($requestUri, 0, $cmp);
	}
	public static function run($applicationClass){//, $baseUrl = '/', $basePath = null
		$app = application::getInstance($applicationClass);
		//if ($basePath === null){
			$trace = debug_backtrace();
			$file = $trace[0]['file'];
			$basePath = dirname($file);
			$app->setBasePath($basePath);
		//}
		// ["REQUEST_URI"]=> string(40) "/kanon-framework/examples/hello_world/ok"
		// ["SCRIPT_NAME"]=> string(51) "/kanon-framework/examples/hello_world/bootstrap.php" 
		$baseUrl = kanon::getBaseUri();
		//echo 'Base PATH: '.$basePath."<br />";
		//echo 'Base URL: '.$baseUrl."<br />";
		$app->setBaseUri($baseUrl);
		//var_dump($app);
		$app->run($baseUrl);//$baseUrl
	}
}


class serviceController extends controller{
	/**
	 * Service-specific (REST for example) responce for unknown request
	 * 
	 */
	public function notFound($message =''){
		header($_SERVER['SERVER_PROTOCOL']." 500 Internal Server Error");
		header("Content-type: text/plain; charset=UTF-8");
		echo "Unknown request";
		exit;
	}
}


class applicationRegistry extends registry{
	private static $_instance = null;
	private function __construct(){
		
	}
	public static function getInstance(){
		if (self::$_instance === null){
			self::$_instance = new self();
		}
		return self::$_instance;
	}
}


class frontController extends controller{
	public static function startSession($domain, $expire = 360000) {
		session_set_cookie_params($expire, '/', $domain);
		@session_start();
		// Reset the expiration time upon page load
		if (isset($_COOKIE[session_name()])){
			setcookie(session_name(), $_COOKIE[session_name()], time() + $expire, "/", $domain);
		}
	}
	public static function _stripSlashesDeep(&$value){
		$value = is_array($value) ?
		array_map(array(self,'_stripSlashesDeep'), $value) :
		stripslashes($value);
		return $value;
	}

}
/*
 * $app = application::getInstance('/');
 * $app->run('/');
 */
