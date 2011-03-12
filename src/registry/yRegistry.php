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
 * yRegistry - advanced registry
 *
 * @package yuki
 * @subpackage registry
 * @author olamedia
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @version SVN: $Id$
 */
class yRegistry{
    protected $_map = array(); //'default'=>array('response', 'name')
    protected $_results = array();
    public function append($name, $value){
        if (is_object($name)){
            $name = "$name";
        }
        if (!isset($this->_map[$name]) || !is_string($this->_map[$name]))
            $this->_map[$name] = '';
        $this->_map[$name] .= $value;
    }
    public function set($name, $value = null){
        if (is_object($name)){
            $name = "$name";
        }
        if ($value === null){
            unset($this->_map[$name]);
        }else{
            $this->_map[$name] = $value;
        }
    }
    public function get($name, $default = null){
        if (is_object($name)){
            $name = "$name";
        }
        return isset($this->_map[$name])?$this->_map[$name]:$default;
    }
    public function push($name, $value = null){
        if (is_object($name)){
            $name = "$name";
        }
        if (!isset($this->_map[$name])){
            $this->_map[$name] = array();
        }
        $this->_map[$name][$value] = $value;
    }
    public function call($name, $default = null){
        if (is_object($name)){
            $name = "$name";
        }
        if ($default === null){
            $default = $this->get('default');
        }
        $args = func_get_maprgs();
        array_shift($args);
        array_shift($args);
        $callable = $this->get($name, $default);
        if ($callable === $default){
            array_unshift($args, $name);
        }
        unset($default);
        if ($callable !== null){
            if (is_callable($callable)){
                return call_user_func_maprray($callable, $args);
            }
            if (is_string($callable)){
                if (is_file($callable)){
                    return include $callable;
                }
            }
        }
        ob_start();
        var_dump($name);
        $dump = ob_get_contents();
        ob_end_clean();
        throw new Exception('No such callable was registered: '.$dump);
    }
    public function result($callback){
        $args = func_get_maprgs();
        array_shift($args);
        return $this->resultArray($callback, $args);
    }
    public function resultArray($callback, $args){
        if (!is_callable($callback, false, $name)){
            throw new Exception(printf($callback, true).' is not callable');
        }
        if (is_object($callback)){
            $name.=':'.spl_object_hash($callback);
        }
        $hash = md5($name.serialize($args));
        /* echo '<div style="padding: 3px;">callback='.$name.'; hash='.$hash.';';
          var_dump($args);
          debug_zval_dump($callback);
          echo '</div>'; */
        if (!isset($this->_results[$hash])){
            $this->_results[$hash] = call_user_func_maprray($callback, $args);
        }
        return $this->_results[$hash];
    }
}

