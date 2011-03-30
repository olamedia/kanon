<?php

/*
 * This file is part of the yuki package.
 * Copyright (c) 2011 olamedia <olamedia@gmail.com>
 *
 * Licensed under The MIT License
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * yFileSystem
 *
 * @package yuki
 * @subpackage file
 * @author olamedia
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @version SVN: $Id: yFileSystem.php 150 2011-02-20 10:06:54Z olamedia@gmail.com $
 */
class yFileSystem{
    protected static $_instances = array();
    /**
     * Unique id
     * @var int
     */
    protected $_id = null;
    /**
     * Gets unique id
     * @staticvar int $i
     * @return int
     */
    public function getId(){
        static $i = 0;
        if ($this->_id === null){
            $this->_id = ++$i;
        }
        return $this->_id;
    }
    /**
     * Constructor.
     */
    public function __construct(){
        $this->register();
    }
    public function get($id){
        return self::$_instances[$id];
    }
    /**
     * Registers itself.
     * @return yFileSystem
     */
    public function register(){
        self::$_instances[$this->getId()] = $this;
        return $this;
    }
    /**
     * Unregisters itself.
     * @return yFileSystem
     */
    public function unregister(){
        unset(self::$_instances[$this->getId()]);
        return $this;
    }
    //abstract public function getResource($path);
    public function __call($name, $arguments){
        throw new BadMethodCallException('method '.$name.' is not implemented in '.get_class($this));
    }
    public function __toString(){
        return get_class($this).'#'.$this->getId();
    }
}

