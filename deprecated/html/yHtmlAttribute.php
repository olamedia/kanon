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
 * yHtmlAttribute
 *
 * @package yuki
 * @subpackage html
 * @author olamedia
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @version SVN: $Id$
 */
class yHtmlAttribute{
    protected $_name;
    protected $_value = '';
    protected $_delimiter = ',';
    public function __construct($name, $value = ''){
        $this->_name = $name;
        $this->_value = $value;
    }
    public function setName($name){
        $this->_name = $name;
    }
    public function getName(){
        return $this->_name;
    }
    public function set($value){
        $this->_value = $value;
    }
    public function get(){
        return is_array($this->_value)?implode($this->_delimiter, $this->_value):$this->_value;
    }
    public function push($value){
        if (!is_array($this->_value)){
            $this->_value = array();
        }
        $this->_value[$value] = $value;
    }
    public function pop($value){
        if (!is_array($this->_value)){
            $this->_value = array();
        }
        unset($this->_value[$value]);
    }
    public function __toString(){
        return $this->_name.'="'.htmlspecialchars($this->get()).'"';
    }
}

