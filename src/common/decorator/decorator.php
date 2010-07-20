<?php
class objectDecorator{
	protected $_decoratedObject = null;
	public function __construct($object){
		$this->_decoratedObject = $object;
	}
	public function __set($name, $value){
		$this->_decoratedObject->{$name} = $value;
	}
	public function __get($name){
		return $this->_decoratedObject->{$name};
	}
	public function __call($name, $arguments){
		return call_user_func_array(array($this->_decoratedObject, $name), $arguments);
	}
	public function __toString(){
		return (string) $this->_decoratedObject;
	}
}