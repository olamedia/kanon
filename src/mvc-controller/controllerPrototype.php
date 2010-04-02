<?php
/**
 * $Id$
 */
require_once dirname(__FILE__).'/../common/uri.php';
class controllerPrototype{
	protected $_me = null; // ReflectionClass
	protected $_parent = null;
	protected $_baseUri = null;
	protected $_relativeUri = null;
	protected $_childUri = '';
	protected $_action = '';
	protected $_actionControllers = array();
	protected $_options = array();
	public function __construct(){
		$this->_baseUri = uri::fromString('/');
		$this->_relativeUri = uri::fromRequestUri();
		$this->_me = new ReflectionClass(get_class($this));
	}
	public function registerActionController($action, $controller){
		$this->_actionControllers[$action] = $controller;
	}
	
	/**
	 * Executing before run() deprecated
	 */
	public function onConstruct(){

	}
	/**
	 * Executing before run()
	 */
	public function onRun(){

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
		echo '<title>Страница не найдена</title>';
		echo '<body>'.$message.'</body>';
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
				if ($this->getHttpMethod() == 'GET') $this->_header();
				call_user_func_array(array($this, $methodName), $this->_getArgs($methodName, $pathArgs));
				if ($this->getHttpMethod() == 'GET') $this->_footer();
				return;
			}
		}

		if ($this->_action){
			
			$uc = ucfirst($this->_action);
			$this->_makeChildUri(array($action));
			$initFunction = 'init'.$uc;
			
			if ($controller = kanon::getActionController(get_class($this), $this->_action)){
				$this->runController($controller);
				return;
			}
			
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
				//$this->_initIndex();
				call_user_func_array(array($this, '_initIndex'), $this->_getArgs('_initIndex'));
				$this->_header();
				call_user_func_array(array($this, 'index'), $this->_getArgs('index'));
				//$this->index();
				$this->_footer();
			}
		}
	}
}