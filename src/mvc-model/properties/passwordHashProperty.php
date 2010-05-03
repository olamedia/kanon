<?php
class passwordHashProperty extends stringProperty{
	public function getValue(){
		return '';
	}
	public function setValue($value){
		if ($value !== ''){
			if (is_object($this->_options['salt'])){
				srand((double) microtime() * 1000000);
				$key = md5(uniqid(rand()));
				$hash = md5(md5($value).$key);
				$this->_options['salt']->setValue($key);
				$this->_value = $hash;
			}else{
				var_dump($this->_options['salt']);
				throw new Exception('salt is not set in passwordHashProperty');
			}
		}
	}
}