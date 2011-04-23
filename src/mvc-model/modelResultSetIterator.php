<?php
class modelResultSetIterator implements Iterator{
	/**
	 * @var modelResultSet
	 */
	protected $_resultSet = null;
	protected $_current = null;
	protected $_key = 0;
	public function __construct($resultSet){
		$this->_resultSet = $resultSet;
	}
	protected function _fetch(){
		$this->_current = $this->_resultSet->fetch();
		return $this->_current;
	}
	public function rewind(){
		$this->_resultSet->reset();
		return $this->_fetch();
	}
	public function current(){
		return $this->_current;
	}
	public function key(){
		return $this->_key;
	}
	public function next(){
		$this->_key++;
		return $this->_fetch();
	}
	public function valid(){
		return ($this->current() !== false);
	}
}