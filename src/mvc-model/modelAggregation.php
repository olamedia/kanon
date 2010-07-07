<?php
class modelAggregation{
	protected $_argument = null;
	protected $_function = 'SUM';
	protected $_as = '';
	public function __construct($argument, $function){
		$this->_argument = $argument;
		$this->_function = $function;
		$this->_as = kanon::getUniqueId();//'sql'
	}
	public function getArguments(){
		return array($this->_argument);
	}
	public function getAs(){
		return $this->_as;
	}
	public function __toString(){
		return $this->_function.'('.$this->_argument.') AS '.$this->_as;
	}
}