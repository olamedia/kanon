<?php

#require_once dirname(__FILE__).'/stringProperty.php';

class passwordHashProperty extends stringProperty{
    protected $_dataSize = 32;
    public function getValue(){
        return '';
    }
    public function getSalt($regenerate = false){
        if (is_object($this->_options['salt'])){
            $salt = &$this->_options['salt'];
            if ($salt instanceof randomHashProperty){
                if ($regenerate) $salt->generate();
                $key = $salt->getValue();
            }else{
                srand((double) microtime() * 1000000);
                $key = $this->_options['salt']->getInternalValue();
                if ($regenerate){
                    $key = md5(uniqid(rand()));
                    $this->_options['salt']->setValue($key);
                }
            }
            return $key;
        }else{
            throw new Exception('salt is not set in passwordHashProperty');
        }
    }
    public function setValue($value){
        if ($value !== ''){
            $hash = $this->getHash($value, true);
            parent::setValue($hash);
        }
    }
    public function equals($password){
        return $this->getInternalValue() == $this->getHash($password);
    }
    public function getHash($password,$regenerate = false){
        return md5(md5($password).$this->getSalt($regenerate));
    }
}