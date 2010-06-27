<?php
require_once dirname(__FILE__).'/event.php';
class eventDispatcher implements ArrayAccess{
	protected $_listeners = array();
	public function attach($name, $listener){
		if (is_callable($listener)){
			if (!isset($this->_listeners[$name])) $this->_listeners[$name] = array();
			$this->_listeners[$name][] = $listener;
		}
	}
	public function detach($name, $listener){
		if (!isset($this->_listeners[$name])) return false;
		foreach ($this->_listeners[$name] as $k => $xlistener){
			if ($listener === $xlistener){
				unset($this->_listeners[$name][$k]);
			}
		}
	}
	public function hasListeners($name){
		if (!isset($this->_listeners[$name])){
			$this->_listeners[$name] = array();
		}
		return (boolean) count($this->_listeners[$name]);
	}
	public function getListeners($name){
		if (!isset($this->_listeners[$name])) return array();
		return $this->_listeners[$name];
	}
	public function notify(event $event){
		$args = func_get_args();
		foreach ($this->getListeners($event->getName()) as $listener){
			call_user_func_array($listener, $args);
		}
		return $event;
	}
	public function notifyUntil(event $event){
		$args = func_get_args();
		foreach ($this->getListeners($event->getName()) as $listener){
			if (call_user_func_array($listener, $args)){
				$event->setProcessed(true);
				break;
			}
		}
		return $event;
	}
	public function offsetExists($name){
		return $this->hasListeners($name);
	}
	public function offsetSet($name, $value){
		$this->attach($name, $value);
	}
	public function offsetUnset($name){
		unset($this->_listeners[$name]); // detach all
	}
	public function offsetGet($name){
		return $this->getListeners($name);
	}
}