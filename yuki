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
 * yAutoloader - implementation for __autoload()
 * 
 * @package yuki
 * @subpackage autoload
 * @author olamedia
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @version SVN: $Id: yAutoloader.php 132 2011-02-19 22:54:49Z olamedia@gmail.com $
 */
class yAutoloader{
    protected $_locationPrefix = '';
    protected $_classes = array();
    public function getClassPath($class){
        if (isset($this->_classes[$class])){
            return $this->_locationPrefix.$this->_classes[$class];
        }
    }
    /**
     * Register given classes within current autoloader.
     * @param array $classes
     * @param string $locationPrefix 
     */
    public function add($classes, $locationPrefix = ''){
        foreach ($classes as $class=>$location){
            $this->_classes[$class] = $locationPrefix.$location;
        }
    }
    /**
     * Handles autoloading of classes.
     * @param string $class A class name.
     * @return boolean Returns true if the class has been loaded
     */
    public function autoload($class){
        if (($path = $this->getClassPath($class))){
            //echo $path;
            require $path;
            return true;
        }
        return false;
    }
}

