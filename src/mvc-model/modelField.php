<?php
#require_once dirname(__FILE__).'/modelExpression.php';
#require_once dirname(__FILE__).'/modelAggregation.php';
class modelField{
	/**
	 * @var modelCollection
	 */
	protected $_collectionId = null;
	protected $_fieldString = null;
	protected $_fieldName = null;
	protected $_fieldUniqueId = null;
	public function getCollectionId(){
		return $this->_collectionId;
	}
	public function getCollection(){
		return modelCollection::getInstanceById($this->getCollectionId());
	}
	public function getUniqueId(){
		return $this->_fieldUniqueId;
	}
	public function __construct($collection, $fieldName){
		$this->_collectionId = $collection->getUniqueId();
		$this->_fieldName = $fieldName;
		$this->_fieldString = $this->_collectionId.'.`'.$this->_fieldName.'`';
		$this->_fieldUniqueId = kanon::getUniqueId('modelField:'.$this->_collectionId.'.'.$this->_fieldName);
	}
	public function getName(){
		return $this->_fieldName;
	}
	/**
	 * @return modelCollection
	 */
	/*public function getCollection(){
		return $this->_collection;
	}*/
	public function __toString(){
		return $this->_fieldString;
	}
	/**
	 * = $value
	 * @param $value
	 */
	public function is($value){
		return new modelExpression($this, '=', $value);
	}
	/**
	 * != $value
	 * @param $value
	 */
	public function not($value){
		return new modelExpression($this, '<>', $value);
	}
	public function min(){
		return new modelAggregation($this, 'MIN');
	}
	public function max(){
		return new modelAggregation($this, 'MAX');
	}
	public function sum(){
		return new modelAggregation($this, 'SUM');
	}
	public function count(){
		return new modelAggregation($this, 'COUNT');
	}
	public function avg(){
		return new modelAggregation($this, 'AVG');
	}
	/**
	 * < $value
	 * @param $value
	 */
	public function lt($value){
		return new modelExpression($this, '<', $value);
	}
	/**
	 * <= $value
	 * @param $value
	 */
	public function lte($value){
		return new modelExpression($this, '<=', $value);
	}
	/**
	 * > $value
	 * @param $value
	 */
	public function gt($value){
		return new modelExpression($this, '>', $value);
	}
	/**
	 * >= $value
	 * @param $value
	 */
	public function gte($value){
		return new modelExpression($this, '>=', $value);
	}
	public function in($value){
		return new modelExpression($this, 'IN', $value);
	}
	public function notIn($value){
		return new modelExpression($this, 'NOT IN', $value);
	}
	public function like($value){
		return new modelExpression($this, 'LIKE', $value);
	}
	public function notLike($value){
		return new modelExpression($this, 'NOT LIKE', $value);
	}
}