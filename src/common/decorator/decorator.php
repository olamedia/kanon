<?php
class objectDecorator{
	const DECORATED_PROPERTY = '_decoratedObject';
	protected $_decoratedObject = null;
	public function __construct($object){
		$this->{self::DECORATED_PROPERTY} = $object;
	}
	public function __set($name, $value){
		$this->{self::DECORATED_PROPERTY}->{$name} = $value;
	}
	public function __get($name){
		return $this->{self::DECORATED_PROPERTY}->{$name};
	}
	public function __call($name, $arguments){
		return call_user_func_array(array($this->{self::DECORATED_PROPERTY}, $name), $arguments);
	}
	public function __toString(){
		return (string) $this->{self::DECORATED_PROPERTY};
	}
}