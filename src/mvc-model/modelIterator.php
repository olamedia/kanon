<?php
class modelIterator implements Iterator{
	private $_model = null;
	private $_classes = array();
	private $_iterator = null;
	public function __construct($model, $classes){
		$this->_model = $model;
		$this->_classes = $classes;
		$this->_iterator = new ArrayIterator($this->_classes); 
	}
	function rewind() {
        $this->_iterator->rewind();
    }
    function current() {
       $propertyName = $this->_iterator->key();
       return $this->_model->$propertyName;
    }
    function key() {
        return $this->_iterator->key();
    }
    function next() {
        $this->_iterator->next();
    }
    function valid() {
        return $this->_iterator->valid();;
    }
}