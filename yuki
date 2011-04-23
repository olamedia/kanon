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
 * @property-write innerText
 */
class yHtmlTag implements ArrayAccess{
    public $tagName = 'html';
    protected $_attr = array();
    protected $_isSelfClosed = false;
    public $childNodes = array();
    public $attributes = array();
    public function hasAttributes(){
        return!!count($this->attributes);
    }
    public function hasAttribute($name){
        return isset($this->attributes[$name]);
    }
    public function setAttribute($name, $value){
        $this->attributes[$name] = new yHtmlAttribute($name, $value);
        return $this;
    }
    public function removeAttribute($name){
        unset($this->attributes[$name]);
        return $this;
    }
    /**
     * Gets tag attribute.
     * @param string $name
     * @param mixed $default
     * @return yHtmlAttribute
     */
    public function getAttribute($name, $default = null){
        return isset($this->attributes[$name])?$this->attributes[$name]:$default;
    }
    public function hasChildNodes(){
        return!!count($this->childNodes);
    }
    public function appendChild($child){
        $this->childNodes[] = $child;
        return $this;
    }
    protected static $_tagMap = array(
        'head'=>'yHeadTag',
        'meta'=>'yMetaTag'
    );
    protected static $_selfClosedTags = array(
        'br', 'img', 'meta', 'link', 'input'
    );
    public function setText($text = ''){
        $textNode = new yTextNode($text);
        if ($this->hasChildNodes()){
            $this->childNodes = array();
        }
        $this->appendChild($textNode);
        return $this;
    }
    public static function create($name = 'html', $attr = array(), $closed = false){
        if (isset(self::$_tagMap[$name])){
            $class = self::$_tagMap[$name];
            return new $class($attr);
        }
        return new yHtmlTag($name, $attr, $closed);
    }
    public function __construct($name = 'html', $attr = array(), $closed = false){
        $this->tagName = $name;
        foreach ($attr as $k=>$v){
            $this->setAttribute($k, $v);
        }
        if (in_array($name, self::$_selfClosedTags)){
            $closed = true;
        }
        $this->_isSelfClosed = $closed;
    }
    public function __toString(){
        $attrs = array($this->tagName);
        foreach ($this->attributes as $node){
            $attrs[] = $node->getAttributeString();
        }
        if ($this->_isSelfClosed){
            $open = '<'.implode(' ', $attrs).'>'; // /
            $close = '';
        }else{
            $open = '<'.implode(' ', $attrs).'>';
            $close = '</'.$this->tagName.'>';
        }
        $inner = '';
        if (!$this->_isSelfClosed){
            $inner = implode('', $this->childNodes);
        }
        return $open.$inner.$close;
    }
    public function offsetExists($offset){
        return $this->hasAttribute($offset);
    }
    /**
     * Sets attribute.
     * 
     * @param string $offset
     * @return yHtmlAttribute
     */
    public function offsetGet($offset){
        return $this->getAttribute($offset);
    }
    public function offsetSet($offset, $value){
        $this->setAttribute($offset, $value);
    }
    public function offsetUnset($offset){
        $this->removeAttribute($offset);
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
    public function __set($name, $value){
        if ($name === 'innerText'){
            $this->setText($value);
        }else{
            $this->setAttribute($name, $value);
        }
    }
    public function __get($name){
        return $this->getAttribute($name, '');
    }
}
