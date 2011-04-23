<?php

/*
 * This file is part of the yuki package.
 * Copyright (c) 2011 olamedia <olamedia@gmail.com>
 *
 * This source code is release under the MIT License.
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * yFileInput
 *
 * @package yuki
 * @subpackage forms
 * @author olamedia
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */
class yFileInput extends yFormControl{
    protected $_value = '';
    protected $_info = null;
    public function setInfo($info){
        $this->_info = $info;
        return $this;
    }
    public function getInfo(){
        return $this->_info;
    }
    public function setValue($value){
        $this->_value = $value;
        return $this;
    }
    public function getValue(){
        return $this->_value;
    }
    public function __construct($name){
        parent::__construct('input', array(
                    'type'=>'file',
                ));
        $this->setName($name);
        $this->setAttribute('value', '');
    }
    public function processKey(){
        if (isset($_FILES[$this->_getFullName()])){
            $f = &$_FILES[$this->_getFullName()];
            if ($this->_key === null){
                $this->setValue($f['tmp_name']);
                $this->setInfo($f);
            }else{
                $this->setValue($f['tmp_name'][$this->_key]);
                $info = array();
                foreach ($f as $k=>$v){
                    if (isset($f[$k][$this->_key])){
                        $info[$k] = $f[$k][$this->_key];
                    }
                }
                $this->setInfo($info);
            }
        }
    }
}

