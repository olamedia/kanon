<?php
/**
 * $Id$
 */
class registry{
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
		if (isset($this->_vars[$key])) return $this->_vars[$key];
		return null;
	}
}