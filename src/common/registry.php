<?php
/**
 * $Id$
 */
class registry implements ArrayAccess{
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
	public function offsetExists($offset){
		return isset($this->_vars[$offset]);
	}
	public function offsetGet($offset){
		return $this->__get($offset);
	}
	public function offsetSet($offset, $value){
		$this->__set($offset, $value);
	}
	public function offsetUnset($offset){
		unset($this->_vars[$offset]);
	}
}