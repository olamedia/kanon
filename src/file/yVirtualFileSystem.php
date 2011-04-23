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
 */
class yVirtualFileSystem extends yFileSystem{
    protected static $_instance = null;
    protected static $_splitter = ':'; // \/ - directory, : - disk at windows
    public static function getSplitter(){
        return self::$_splitter;
    }
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
        $p = strpos($path, self::$_splitter);
        $alias = ($p === false)?$path:substr($path, 0, $p);
        $path = ($p === false)?'':substr($path, $p + 1);
        if ($path === false){
            $path = '';
        }
        var_dump($path);
        //var_dump($this->_map[$alias]);
        if (isset($this->_map[$alias])){
            return $this->_map[$alias]->getResource($path);
        }else{
            throw new Exception('Virtual directory was mot mapped');
        }
    }
    /**
     * @param yFilesystemResource $yResource 
     */
    public function getResourceUri($yResource){
        return $yResource->getFileSystem()->getResourceUri($yResource);
    }
    public function mapUri($uri, $path){
        $res = $this->getResource($path);
        $res->getFileSystem()->mapUri($uri, $res->getPath());
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

