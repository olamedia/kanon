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
 * yHtmlTag
 *
 * @package yuki
 * @subpackage html
 * @author olamedia
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @version SVN: $Id$
 */
class yHtmlTag implements ArrayAccess{
    protected $_name = '';
    protected $_attr = array();
    protected $_isSelfClosed = false;
    public function __construct($name = 'html', $attr = array()){
        $this->_name = $name;
        foreach ($attr as $k=>$v){
            $this->offsetSet($k, $v);
        }
    }
    public function setName($name){
        $this->_name = $name;
    }
    public function getName(){
        return $this->_name;
    }
    public function __toString(){
        $name = strtolower($this->_name);
        if ($this->_isSelfClosed){
            return '<'.$name.' '.implode(' ', $this->_attr).' />';
        }
        return '<'.$name.' '.implode(' ', $this->_attr).'></'.$name.'>';
    }
    public function offsetExists($offset){
        return array_key_exists($offset, $this->_attr);
    }
    public function set($name, $value){
        $this->_attr[$name] = new yHtmlAttribute($name, $value);
    }
    /**
     * Sets attribute.
     * 
     * @param string $offset
     * @return yHtmlAttribute
     */
    public function offsetGet($offset){
        return $this->_attr[$offset];
    }
    public function offsetSet($offset, $value){
        $this->_attr[$offset] = new yHtmlAttribute($offset, $value);
    }
    public function offsetUnset($offset){
        unset($this->_attr[$offset]);
    }
    /**
     *
     * @param string $name
     * @return yHtmlAttribute
     */
    public function forceAttribute($name){
        if (!isset($this->_attr[$name])){
            $this->_attr[$name] = new yHtmlAttribute($name, '');
        }
        return $this->_attr[$name];
    }
    public function open(){
        $this->_isSelfClosed = false;
    }
    public function close(){
        $this->_isSelfClosed = true;
    }
}

require 'yHtmlAttribute.php';

$tag = new yHtmlTag('div');
$tag['class'] = 'fancy';
$tag['style'] = 'color:#fff;';
$tag->close();
echo $tag;