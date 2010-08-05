<?php
#require_once dirname(__FILE__).'/stringProperty.php';
class passwordHashProperty extends stringProperty{
	protected $_dataSize = 32;
	public function getValue(){
		return '';
	}
	public function getSalt(){
		if (is_object($this->_options['salt'])){
			$salt = &$this->_options['salt'];
			if ($salt instanceof randomHashProperty){
				$salt->generate();
				$key = $salt->getValue();
			}else{
				srand((double) microtime() * 1000000);
				$key = md5(uniqid(rand()));
				$this->_options['salt']->setValue($key);
			}
			return $key;
		}else{
			throw new Exception('salt is not set in passwordHashProperty');
		}
	}
	public function setValue($value){
		if ($value !== ''){
			$hash = md5(md5($value).$this->getSalt());
			$this->_value = $hash;
		}
	}
	public function equals($password){
		return $this->getInternalValue() == md5(md5($password).$this->getSalt());
	}
}