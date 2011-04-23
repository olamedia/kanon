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
 * yUriQuery implements query part of URI
 *
 * @see http://tools.ietf.org/html/rfc3986#section-3.4
 *
 * @package yuki
 * @subpackage uri
 * @author olamedia
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @version SVN: $Id: yUriQuery.php 109 2011-02-19 08:11:02Z olamedia@gmail.com $
 */
class yUriQuery implements ArrayAccess{
    private $_query = array();
    /**
     * Retrieves a query parameter.
     * @param string $name
     * @param mixed $default
     * @return string query parameter or $default
     */
    public function get($name, $default = null){
        return $this->__isset($name)?$this->_query[$name]:$default;
    }
    /**
     * Sets a query parameter.
     * @param string $name
     * @param string $value
     * @return yUriQuery
     */
    public function set($name, $value = ''){
        $this->_query[$name] = $value;
        return $this;
    }
    /**
     * Clears all current query parameters.
     * @return yUriQuery
     */
    public function clear(){
        $this->_query = array();
        return $this;
    }
    public function __get($name){
        return $this->get($name);
    }
    public function __set($name, $value){
        $this->set($name, $value);
    }
    public function __isset($name){
        return array_key_exists($name, $this->_query);
    }
    public function __unset($name){
        unset($this->_query[$name]);
    }
    public function offsetGet($offset){
        return $this->get($offset);
    }
    public function offsetSet($offset, $value){
        $this->set($offset, $value);
    }
    public function offsetExists($offset){
        return $this->__isset($offset);
    }
    public function offsetUnset($offset){
        $this->__unset($offset);
    }
    public function loadString($queryString){
        $this->clear();
        $args = explode('&', $queryString);
        foreach ($args as $arg){
            $p = strpos($arg, '=');
            if ($p === false){
                $this->set($arg);
            }else{
                $this->set(
                        substr($arg, 0, $p),
                        substr($arg, $p + 1)
                );
            }
        }
        return $this;
    }
    public static function fromString($queryString){
        $query = new yUriQuery();
        $query->loadString($queryString);
        return $query;
    }
    public function __toString(){
        $args = array();
        foreach ($this->_query as $name=>$value){
            if ($value === ''){
                $args[] = $name;
            }else{
                $args[] = $name.'='.$value;
            }
        }
        $queryString = implode('&', $args);
        return (string) (strlen($queryString)?'?':'').$queryString;
    }
}

