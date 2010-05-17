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
	public function rewind(){
		$this->_resultSet->reset();
		$this->_current = $this->_resultSet->fetch();
	}
	public function current(){
		return $this->_current;
	}
	public function key(){
		return $this->_key;
	}
	public function next(){
		$this->_key++;
		$this->_current = $this->_resultSet->fetch();
		return $this->_current;
	}
	public function valid(){
		return ($this->current() !== false);
	}
}