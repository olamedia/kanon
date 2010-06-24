<?php
require_once dirname(__FILE__).'/event.php';
class eventDispatcher{
	protected $_listeners = array();
	public function attach(string $name, $listener){
		if (is_callable($listener)){
			if (!isset($this->_listeners[$name])) $this->_listeners[$name] = array();
			$this->_listeners[$name][] = $listener;
		}
	}
	public function detach(string $name, $listener){
		if (!isset($this->_listeners[$name])) return false;
		foreach ($this->_listeners[$name] as $k => $xlistener){
			if ($listener === $xlistener){
				unset($this->_listeners[$name][$k]);
			}
		}
	}
	public function hasListeners(string $name){
		if (!isset($this->_listeners[$name])){
			$this->_listeners[$name] = array();
		}
		return (boolean) count($this->_listeners[$name]);
	}
	public function getListeners(string $name){
		if (!isset($this->_listeners[$name])) return array();
		return $this->_listeners[$name];
	}
	public function notify(event $event){
		foreach ($this->getListeners($event->getName()) as $listener){
			call_user_func_array($listener, func_get_args());
		}
		return $event;
	}
	public function notifyUntil(event $event){
		foreach ($this->getListeners($event->getName()) as $listener){
			if (call_user_func_array($listener, func_get_args())){
				$event->setProcessed(true);
				break;
			}
		}
		return $event;
	}
}