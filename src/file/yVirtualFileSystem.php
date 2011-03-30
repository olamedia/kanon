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
 * yVirtualFileSystem
 *
 * @package yuki
 * @subpackage file
 * @author olamedia
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @version SVN: $Id: yVirtualFileSystem.php 150 2011-02-20 10:06:54Z olamedia@gmail.com $
 */
class yVirtualFileSystem extends yFileSystem{
    protected static $_instance = null;
    protected $_map = array();
    public static function getInstance(){
        if (self::$_instance === null){
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    /**
     * Gets resource by name.
     * @param string $path
     */
    public function getResource($path){
        $p = strpos($path, ':');
        $alias = ($p === false)?$path:substr($path, 0, $p);
        $path = ($p === false)?'':substr($path, $p + 1);
        var_dump($alias);
        if (isset($this->_map[$alias])){
            return $this->_map[$alias]->getResource($path);
        }else{
            // return virtual resource.
            //$this->_map[$alias] = new yDirectory($path);
        }
    }
    /**
     * Maps alias to real filesystem
     * map('local', $localFs->getRoot());
     * map('system', $localFs->getDirectory(realpath(dirname(__FILE__).'/../..')));
     * map('web', $localFs->getDirectory(realpath($_SERVER['DOCUMENT_ROOT'])));
     * @param string $alias
     * @param yDirectory $directory
     */
    public function map($alias, $resource){
        $this->_map[$alias] = $resource;
    }
}

