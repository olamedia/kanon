<?php
class modelExpression{
	protected $_left = null;
	protected $_operator = '=';
	protected $_right = null;
	protected $_escapeRight = true;
	public function __construct($left, $operator, $right, $escapeRight = true){
		$this->_left = $left;
		$this->_operator = $operator;
		$this->_right = $right;
		if ($this->_right instanceof modelProperty){
			$this->_right = $this->_right->getValue();
		}
		/*if (is_array($this->_right)){
			$this->_right = implode(",", $this->_right);
		}*/
	}
	public function getArguments(){
		return array($this->_left, $this->_right);
	}
	public function getLeft(){
		return $this->_left;
	}
	public function getRight(){
		if ($this->_right instanceof modelField){
			return (string) $this->_right;
		}
		if (is_array($this->_right)){
			return $this->_right;
		}
		if ($this->_escapeRight){
			return "'".addslashes($this->_right)."'";
		}
		return $this->_right;
	}
	public function __toString(){
		$right = $this->getRight();
		if (in_array(strtoupper($this->_operator), array('IN','NOT IN'))){
			if (is_array($this->_right)){
				$right = implode(",", $this->_right);
			}else{
				$right = $this->_right;
			}
			$right = '('.$right.')';
		}
		return $this->getLeft().' '.$this->_operator.' '.$right;
	}
}