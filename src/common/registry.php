<?php
/**
 * $Id$
 */
class registry implements ArrayAccess, IteratorAggregate, Countable{
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
		if (!isset($this->_vars[$key])){
			$this->_vars[$key] = new registry();
		}
		return $this->_vars[$key];
	}
	public function __isset($key){ 
		return isset($this->_vars[$key]);
	}
	public function offsetExists($offset){
		return array_key_exists($offset, $this->_vars);
	}
	public function offsetGet($offset){
		return $this->__get($offset);
	}
	public function offsetSet($offset, $value){
		if ($offset == ''){
			$this->_vars[] = $value;
			return;
		}
		$this->__set($offset, $value);
	}
	public function offsetUnset($offset){
		unset($this->_vars[$offset]);
	}
	public function getIterator(){
		return new ArrayIterator($this->_vars);
	}
	public function count(){
		return count($this->_vars);
	}
	public function __toString(){
		return implode('',$this->_vars); 
	}
}