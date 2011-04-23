<?php
class zenEvents{
	private static $_handlers = array();
	//private static $_globalHandlers = array();
	protected static function getCallbackId($callback){
		if (is_string($callback)) return $callback;
		if (is_array($callback)){
			list($class, $function) = $callback;
			if (is_object($class)){
				$class = get_class($class);
			}
			return $class.'::'.$function;
		}
		return 'undefined';
	} 
	public static function connect($subject, $callback){
		$subjectId = self::getCallbackId($subject);
		$callbackId = self::getCallbackId($callback);
		self::$_handlers[$subjectId][$callbackId] = $callback;
	}
	public static function disconnect($subject, $callback){
		$subjectId = self::getCallbackId($subject);
		$callbackId = self::getCallbackId($callback);
		if (isset(self::$_handlers[$subjectId]) && isset(self::$_handlers[$subjectId][$callbackId])){
			unset(self::$_handlers[$subjectId][$callbackId]);
		}
	}
	public static function getEventHandlers($subject){
		$subjectId = self::getCallbackId($subject);
		//echo 'Subject ID: '.$subjectId.'<br />';
		if (isset(self::$_handlers[$subjectId])){
			return self::$_handlers[$subjectId];
		}
		return array();
	}
	public static function getAllEventHandlers(){
		return self::$_handlers;
	}
}

class zenEvent{ // Observable
     private $_name;
	 private $_sender;
     public function getName(){
          return $this->_name;
     }    
     public function __construct($name){
          $this->_name = $name;
     }
	 public function setSender($sender){
		$this->_sender = $sender;
	 }
	 public function getSender(){
		return $this->_sender;
	 }
	 public function raise(){ // notifyObservers
		//echo 'Raising '.$this->getName().':<br />';
		$args = func_get_args();
		if (!$args) $args = array();
		array_unshift($args, $this);
		$senderClass = get_class($this->_sender);
		$handlers = zenEvents::getEventHandlers(array($senderClass, $this->getName()));
		//echo 'Handlers:<br />';
		//var_dump($handlers);
		foreach ($handlers as $handler){
			//var_dump($handler);
			if (is_array($handler)){
				list($object, $function) = $handler;
				if (!is_object($object)){
					$object = new $object();
				}
				$callable = array($object, $function);
				if(!is_callable($callable)) {
					throw new Exception("Callable only works on is_callable's!"); 
				}
				$result = call_user_func_array($callable, $args);
			}else{
				$result = call_user_func_array($handler, $args);
			}
			if (!$result){
				break;
			}
		}
		//echo '<br />';
	 }
}

class zenEventEnabledClass{
	protected function _dispatch($eventName){
		$args = func_get_args();
		$eventName = array_shift($args);
		$this->_dispatch_array($eventName, $args);
	}
	protected function _dispatch_array($eventName, $args){
		$event = new zenEvent($eventName);
		$event->setSender($this);
		// $event->raise();
		call_user_func_array(array($event, 'raise'), $args);
	}
}
class zenExtendableClass extends zenEventEnabledClass{
	public function __call($name, $arguments){
		$this->_dispatch_array('__call::'.$name, $arguments);
	}
}
function zenExtendClass($className, $className2){
	$ref = new ReflectionClass($className2);
	$methods = $ref->getMethods(ReflectionMethod::IS_PUBLIC);
	foreach ($methods as $method){
		$methodName = $method->name;
		//echo $methodName;
		zenEvents::connect(array($className, '__call::'.$methodName), array($className2, $methodName));
	}
	//var_dump(zenevents::getAllEventHandlers());
}
class myClass extends zenExtendableClass{
	public function __construct(){
		$this->_dispatch('onLoad', time());
	}
	public function hello(){
		$this->_dispatch('onHello');
	}
	public function __destruct(){
		$this->_dispatch('onUnload');
	}
}
class myClassExtender{
	public function handleLoad($event, $time){ // handleClassnameMethod
		echo '<div>"'.get_class($event->getSender()).'" dispatched: '.$event->getName().'. Handled by '.__METHOD__.'.</div>';
		echo '<div>onMyClassLoad '.$time.'</div>';
	}
	public function handleUnload($event){
		echo '<div>"'.get_class($event->getSender()).'" dispatched: '.$event->getName().'. Handled by '.__METHOD__.'.</div>';
		echo '<div>onMyClassUnload</div>';
	}
}
class a{
	public function world(){
		echo ' World! ';
	}
}

zenEvents::connect(array('myClass', 'onLoad'), array('myClassExtender', 'handleLoad'));
//zenEvents::connect(array('myClass', 'onLoad'), array('myClassExtender', 'onMyClassLoad'));
zenEvents::connect(array('myClass', 'onUnload'), array('myClassExtender', 'handleUnload'));
$a = new myClass();
//zenExtendClass('myClass', 'a');
//$a->world();
$a = null; 