<?php
require_once dirname(__FILE__).'/modelExpression.php';
require_once dirname(__FILE__).'/modelAggregation.php';
class modelField{
	protected $_collection = null;
	protected $_fieldName = null;
	public function __construct($collection, $fieldname){
		$this->_collection = $collection;
		$this->_fieldName = $fieldname;
	}
	public function getCollection(){
		return $this->_collection;
	}
	public function __toString(){
		return $this->_collection->getUniqueId().'.`'.$this->_fieldName.'`';
	}
	public function getUniqueId(){
		return $this->_collection->getUniqueId().'__'.$this->_fieldName;
	}
	public function is($value){
		return new modelExpression($this, '=', $value);
	}
	public function sum(){
		$as = $this->getUniqueId('sql');
		return new modelAggregation($this, 'SUM', $as);
	}
	public function avg(){
		$as = $this->getUniqueId('sql');
		return new modelAggregation($this, 'AVG', $as);
	}
	public function lt($value){
		return new modelExpression($this, '<', $value);
	}
	public function gt($value){
		return new modelExpression($this, '>', $value);
	}
	public function in($value){
		return new modelExpression($this, 'IN', $value);
	}
	public function like($value){
		return new modelExpression($this, 'LIKE', $value);
	}
}