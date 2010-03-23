<?php
class modelExpression{
	protected $_left = null;
	protected $_operator = '=';
	protected $_right = null;
	public function __construct($left, $operator, $right){
		$this->_left = $left;
		$this->_operator = $operator;
		$this->_right = $right;
		if ($this->_right instanceof property){
			$this->_right = $this->_right->getValue();
		}
	}
	public function getLeft(){
		return $this->_left;
	}
	public function getRight(){
		return $this->_right;
	}
	public function __toString(){
		$right = $this->getRight();
		if (strtoupper($this->_operator) == 'IN'){
			if (is_array($right)){
				$right = implode(",", $right);
			}
			$right = '('.$right.')';
		}
		return $this->getLeft().' '.$this->_operator.' '.$right;
	}
}