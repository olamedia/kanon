<?php
require_once dirname(__FILE__).'/stringProperty.php';
class passwordHashProperty extends stringProperty{
	public function getValue(){
		return '';
	}
	public function setValue($value){
		if ($value !== ''){
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
				$hash = md5(md5($value).$key);
				$this->_value = $hash;
			}else{
				var_dump($this->_options);//['salt']
				throw new Exception('salt is not set in passwordHashProperty');
			}
		}
	}
}