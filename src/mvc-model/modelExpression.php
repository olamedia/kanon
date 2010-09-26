<?php
class modelExpression{
	/**
	 * @var modelField
	 */
	protected $_left = null;
	protected $_operator = '=';
	protected $_right = null;
	protected $_escapeRight = true;
	protected $_or = array();
	protected $_and = array();
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
	public function _or($expression){
		$expression = new modelExpression($this,'OR',$expression, false);
		return $expression;
	}
	public function _and($expression){
		$expression = new modelExpression($this,'AND',$expression, false);
		return $expression;
	}
	public function setLeft($left){
		$this->_left = $left;
	}
	public function setRight($right){
		$this->_right = $right;
	}
	public function getArguments(){
		return array($this->_left, $this->_right);
	}
	public function getLeft(){
		return $this->_left;
	}
	public function quote($string){
		if ($this->_left instanceof modelField){
			$collection = $this->_left->getCollection();
			return $collection->getStorage()->quote($string);
		}
		throw new Exception('');
	}
	public function getRight(){
		if ($this->_right instanceof modelField){
			return (string) $this->_right;
		}
		if (is_array($this->_right)){
			return $this->_right;
		}
		if ($this->_escapeRight){
			return $this->quote($this->_right);
		}
		return $this->_right;
	}
        protected function _quote($a){
            if (is_array($a)){
		$aa = array();
                foreach ($a as $x){
                    $aa[] = $this->_quote($x);
                }
                return $aa;
            }else{
                if (is_integer($a)){
                    return $a;
                }else{
                    return "'".$a."'";
                }
            }
        }
	public function __toString(){
		$right = $this->getRight();
                $r = $this->_quote($this->_right);
		if (in_array(strtoupper($this->_operator), array('IN','NOT IN'))){
			if (is_array($r)){
				if (!count($r)) return '';
				$right = implode(",", $r);
			}else{
				$right = $r;
			}
			$right = '('.$right.')';
		}
		if (in_array(strtoupper($this->_operator), array('OR','AND'))){
			$s = '('.$this->getLeft().') '.$this->_operator.' ('.$right.')';
		}else{
			$s = $this->getLeft().' '.$this->_operator.' '.$right;
		}
		return $s;
	}
}