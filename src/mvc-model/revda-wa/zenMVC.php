<?php
class zenMVC{ 
	protected static $_finalControllerName = null;
	protected static $_controllersBreadCrumb = array();
	protected static $_placeFilters = array();
	public static function setFinalControllerName($className){
		self::$_controllersBreadCrumb[] = $className;
		self::$_finalControllerName = $className;
	}
	public static function addPlaceFilter($filterType, $filterValue){
		self::$_placeFilters[$filterType] = $filterValue;
	}
	public static function getPlaceFilters(){
		return self::$_placeFilters;
	}
	public static function getFinalControllerName(){
		return self::$_finalControllerName;
	}
	public static function getControllersBreadcrumb(){
		return self::$_controllersBreadCrumb;
	}
}
class registry{
	/**
	 * @desc the vars array
	 * @access private
	 */
	private $_vars = array();
	/**
	 * @desc set undefined vars
	 * @param string $index
	 * @param mixed $value
	 * @return void
	 */
	public function __set($key, $value){
		$this->_vars[$key] = $value;
	}
	/**
	 * @desc get variables
	 * @param mixed $index
	 * @return mixed
	 */
	public function __get($key){
		if (isset($this->_vars[$key])) return $this->_vars[$key];
		return null;
	}
}
class uri{
	//protected $_defaultIndex = 'index.php';
	protected $_path = array();
	protected $_args = array();
	
	public function setPath($path){
		$this->_path = $path;
	}
	public function getPath(){
		return $this->_path;
	}
	public function getBasePath($shift = 0){
		reset($this->_path);
		for($i=0;$i<$shift;$i++) next($this->_path);
		return current($this->_path);
	}
	public function setArgs($args){
		$this->_args = $args;
	}
	public function getArgs(){
		return $this->_args;
	}
	public static function utf8UrlDecode($str) {
		$str = preg_replace("/%u([0-9a-f]{3,4})/i","&#x\\1;",urldecode($str));
		return html_entity_decode($str,null,'UTF-8');;
	}
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
				$path[$k] = urldecode($v);//uri::utf8UrlDecode
			}
		}
		foreach ($args as $k => $v){
			if ($v == '') unset($args[$k]);
		}
		$uri->setPath($path);
		$uri->setArgs($args);
		//var_dump($args);
		//echo $uriString;
		//$uri->setPath();
		return $uri;
	}
	public static function fromRequestUri(){
		return uri::fromString($_SERVER['REQUEST_URI']);
	}
	public function subtractBase($baseUri){
		$basepath = $baseUri->getPath();
		$path = $this->_path;
		foreach ($basepath as $basedir){
			$dir = array_shift($path);
			if ($dir !== $basedir){
				// Exception
				throw new Exception('base dir not found');
			}
		}
		$this->_path = $path;
	}
	public function __toString(){
		return '/'.implode('/',$this->_path);
	}
}
class controller{
	protected $_baseUri;
	protected $_relativeUri;
	protected $_childUri = '';
	protected $_me; // ReflectionClass
	protected $_parent = null;
	protected $_options = array();
	protected $_action = '';
	protected $_registry = null;
	public function getDomainName(){
		$da = explode(".", $_SERVER['SERVER_NAME']);
		$first = reset($da);
		if ($first == 'www'){
			array_shift($da);
		}
		return implode(".", $da);
	}
	public function &getRegistry(){
		if ($this->_registry === null) $this->_registry = new registry();
		return $this->_registry;
	}
	public function getNextAction(){
		$actions = $this->getNextActions();
		return array_shift($actions);
	}
	public function getNextActions(){
		$actions = $this->_relativeUri->getPath();
		array_shift($actions);
		return $actions;
	}
	public function setOptions($options = array()){
		$this->_options = $options;
	}
	public function rel($uriString){ // rel('blog') -> .../blog
		$relUri = uri::fromString($uriString);
		
		$relUri->setPath(array_merge($this->_baseUri->getPath(),$relUri->getPath()));
		return $relUri;
	}
	public function setParent($parentController){
		$this->_parent = $parentController;
	}
	public function getParent(){
		return $this->_parent;
	}
	public function __construct(){
		$this->_baseUri = uri::fromString('/');
		$this->_relativeUri = uri::fromRequestUri();
		$this->_me = new ReflectionClass(get_class($this));
	}
	public function onConstruct(){
		
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
	protected function _action($action){
		//echo '_action('.$action.')';
		return $this->_show404(' Directory "'.$action.'" not found in '.get_class($this).'<br /><img src="/images/404.png" />');
	}
	protected function _header(){
		$c = $this;
		$ca = array();
		while (($c = $c->getParent()) !== null) {
			$ca[] = $c;
		}
		$last = null;
		$ca = array_reverse($ca);
		foreach ($ca as $c) {
			if ($last != get_class($c)){
				//echo '<p>'.get_class($c).' header</p>';
				if (get_class($c) !== 'application') echo "\r\n".'<div class="'.get_class($c).'_wrapper">';
				$c->header();
				echo "\r\n".'<div class="'.get_class($c).'_content">';
				$last = get_class($c);
			}
		}
		//echo '<p>'.get_class($this).' header</p>';
		if ($last !== get_class($this)) {
				if (get_class($this) !== 'application') echo "\r\n".'<div class="'.get_class($this).'_wrapper">';
			$this->header();
			echo "\r\n".'<div class="'.get_class($this).'_content">';
		}
	}
	protected function _footer(){
		echo '</div>'."\r\n";
		$this->footer();
		$c = $this;
		$ca = array();
		$last = null;
		while (($c = $c->getParent()) !== null) {
			if ($last != get_class($c)){
				$ca[] = $c;
				$last = get_class($c);
			}
		}
		foreach ($ca as $c) {
			echo '</div>'."\r\n";
			$c->footer();
			if (get_class($c) !== 'application') echo '</div>'."\r\n";
		}
	}
	public function header(){
		//echo '<p>'.get_class($this).' header</p>';
		//var_dump($this->_baseUri);
	}
	public function _initIndex(){
		//echo 'index';
	}
	public function index(){
		//echo 'index';
	}
	public function footer(){
		//echo '<p>'.get_class($this).' footer</p>';
	}
	public function run(){
		$this->onConstruct();
		$class = get_class($this);
		zenMVC::setFinalControllerName($class);
		//var_dump($this->_relativeUri->getBasePath());
		if ($action = $this->_relativeUri->getBasePath()){
			$this->_action = $action;
			$uc = ucfirst($action);
			$actionFunction = 'action'.$uc;
			$initFunction = 'init'.$uc;
			$showFunction = 'show'.$uc;
			// prepare uri
			$childUri = clone $this->_baseUri;
			$path = $childUri->getPath();
			$path[] = $action;
			$childUri->setPath($path);
			$this->_childUri = strval($childUri);
			$methodFound = false;
			if (method_exists($this, $initFunction)){
				$methodFound = true;
				$method = $this->_me->getMethod($initFunction);
				$parameters = $method->getParameters();
				$args = array();
				foreach ($parameters as $p){
					$name = $p->getName();
					//$defaultValue = $p->isDefaultValueAvailable()?$p->getDefaultValue():null;
					if (isset($_GET[$name])){
						$value = $_GET[$name];
					}elseif (isset($_POST[$name])){
						$value = $_POST[$name];
					}else{
						$value = $p->isDefaultValueAvailable()?$p->getDefaultValue():null;
					}
					$args[] = $value;//$name
				}
				call_user_func_array(array($this, $initFunction), $args);
			}
			if (method_exists($this, $actionFunction)){
				$methodFound = true;
				call_user_func(array($this, $actionFunction));
			}
			if (method_exists($this, $showFunction)){
				$methodFound = true;
				$method = $this->_me->getMethod($showFunction);
				$parameters = $method->getParameters();
				$args = array();
				foreach ($parameters as $p){
					$name = $p->getName();
					//$defaultValue = $p->isDefaultValueAvailable()?$p->getDefaultValue():null;
					if (isset($_GET[$name])){
						$value = $_GET[$name];
					}elseif (isset($_POST[$name])){
						$value = $_POST[$name];
					}else{
						$value = $p->isDefaultValueAvailable()?$p->getDefaultValue():null;
					}
					$args[] = $value;//$name
				}
				$this->_header();
				call_user_func_array(array($this, $showFunction), $args);
				$this->_footer();
			}
			if (!$methodFound){
				//var_dump($this);
				return $this->_action($action);
			}
		}else{
			//echo 'hERE'.get_class($this);
			if (method_exists($this, 'customIndex')){
				$this->customIndex();
			}else{
				//echo 'hERE'.get_class($this);
				$this->_initIndex();
				$this->_header();
				$this->index();
				$this->_footer();
			}
		}
	}
	public function forwardTo($class, $relativePath = '', $options = array()){
		//echo 'FWD';
		$controller = new $class();
		$controller->setParent($this);
		$controller->setBaseUri($this->rel($relativePath), false);
		$controller->setRelativeUriFromBase($this->_baseUri);
		$controller->setOptions($options);
		if (method_exists($controller, 'customRun')){
			$controller->customRun();
		}else{
			$controller->run();
		}
	}
	public function runController($class, $options = array()){
		$controller = new $class();
		$controller->setParent($this);
		$controller->setBaseUri($this->_childUri);
		$controller->setOptions($options);
		if (method_exists($controller, 'customRun')){
			$controller->customRun();
		}else{
			$controller->run();
		}
	}
	protected function _show403($message){
		header($_SERVER['SERVER_PROTOCOL']." 403 Forbidden");
		echo $message;
		exit;
	}
	protected function _show404($message){
		header($_SERVER['SERVER_PROTOCOL']." 404 Not found");
		echo $message.str_repeat('&nbsp; ', 100);
		exit;
	}
}