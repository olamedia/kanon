<?php
class event implements ArrayAccess{
	protected $_subject = null;
	protected $_name = '';
	protected $_parameters = array();
	protected $_processed = false;
	protected $_value = null;
	public function __construct($subject, string $name, $parameters = array()){
		$this->_subject = $subject;
		$this->_name = $name;
		$this->_parameters = $parameters;
	}
	public function getName(){
		return $this->_name;
	}
	public function getSubject(){
		return $this->_subject;
	}
	public function setProcessed($processed = true){
		$this->_processed = (boolean) $processed;
	}
	public function isProcessed(){
		return $this->_processed;
	}
	public function setReturnValue($value){
		$this->_value = $value;
	}
	public function getReturnValue(){
		return $this->_value;
	}
	public function hasParameter($name){
		return ($this->_parameters[$name] !== null);
	}
	public function setParameter($name, $value){
		$this->_parameters[$name] = $value;
	}
	public function getParameter($name){
		if ($this->_parameters[$name] === null){
			throw new InvalidArgumentException(sprintf('The event "%s" has no "%s" parameter.', $this->_name, $name));
		}
		return $this->_parameters[$name];
	}
	public function offsetExists(string $name){
		return ($this->_parameters[$name] !== null); // faster than array_key_exists or isset
	}
	public function offsetSet($name, $value){
		$this->_parameters[$name] = $value;
	}
	public function offsetUnset($name){
		unset($this->_parameters[$name]);
	}
	public function offsetGet($name){
		if ($this->_parameters[$name] === null){
			throw new InvalidArgumentException(sprintf('The event "%s" has no "%s" parameter.', $this->_name, $name));
		}
		return $this->_parameters[$name];
	}
}