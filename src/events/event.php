<?php
class event{
	protected $_subject = '';
	protected $_name = '';
	protected $_parameters = '';
	public function __construct($subject, $name, $parameters = array()){
		$this->_subject = $subject;
		$this->_name = $name;
		$this->_parameters = $parameters;
	}
	public function getName(){
		return $this->_name;
	}
}