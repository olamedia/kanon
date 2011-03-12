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
 * yPageMetaCollection
 *
 * @package yuki
 * @subpackage page
 * @author olamedia
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @version SVN: $Id$
 */
class yPageMetaCollection{
    protected $_meta = array();
    /**
     * Set meta tag content (rewrite old one)
     * @param type $name
     * @param type $content 
     */
    public function set($name, $content, $isHttp = false){
        $this->_forceGet($name)->setContent($content, $isHttp);
    }
    /**
     * Add content part (ex. noindex,nofollow)
     * @param type $name
     * @param type $content 
     */
    public function push($name, $content){
        $this->_forceGet($name)->pushContent($content);
    }
    public function pop($name, $content){
        $this->_forceGet($name)->popContent($content);
    }
    public function replace($name, $old, $new){
        $this->pop($name, $old);
        $this->push($name, $new);
    }
    /**
     * Retrieve meta tag
     * @param type $name 
     */
    public function get($name, $default = null){
        $name = strtolower($name);
        return isset($this->_meta[$name])?$this->_meta[$name]:$default;
    }
    /**
     * 
     * @param type $name 
     * @return yMetaTag
     */
    protected function _forceGet($name){
        $name = strtolower($name);
        if (!isset($this->_meta[$name])){
            $this->_meta[$name] = yHtmlTag::create('meta', array('name'=>$name));
        }
        return $this->_meta[$name];
    }
    public function __toString(){
        $eol = "\r\n";
        return implode($eol, $this->_meta);
    }
    public function toArray(){
        return $this->_meta;
    }
}

