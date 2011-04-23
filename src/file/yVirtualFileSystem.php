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
    /**
     * yVirtualFileSystem instance.
     * @var yVirtualFileSystem
     */
    protected static $_instance = null;
    /**
     * Splitter for alias & path parts
     * @var string
     */
    protected static $_splitter = '::'; // \/ - directory, : - disk at windows
    /**
     * Alias => Resource map
     * @var array
     */
    protected $_map = array();
    /**
     * Gets splitter currently in use
     * @return string
     */
    public static function getSplitter(){
        return self::$_splitter;
    }
    /**
     * Gets yVirtualFileSystem instance.
     * @return yVirtualFileSystem
     */
    public static function getInstance(){
        if (self::$_instance === null){
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    /**
     * Gets filesystem resource.
     * @param string $path 
     * @return yFilesystemResource
     */
    public function getResource($path){
        $p = strpos($path, self::$_splitter);
        $alias = ($p === false)?$path:substr($path, 0, $p);
        $path = ($p === false)?'':substr($path, $p + strlen(self::$_splitter));
        if ($path === false){
            $path = '';
        }
        //var_dump($path);
        //var_dump($this->_map[$alias]);
        if (isset($this->_map[$alias])){
            return $this->_map[$alias]->getResource($path);
        }else{
            throw new Exception('Virtual directory was mot mapped');
        }
    }
    /**
     * @todo remove??
     * @param yFilesystemResource $yResource 
     */
    public function getResourceUri($yResource){
        return $yResource->getFileSystem()->getResourceUri($yResource);
    }
    /**
     * Maps given URI to given path.
     * Makes call to real filesystem object.
     * @param string $uri
     * @param string $path 
     * @return yFileSystem
     */
    public function mapUri($uri, $path){
        $res = $this->getResource($path);
        return $res->getFileSystem()->mapUri($uri, $res->getPath());
    }
    /**
     * Maps alias to real filesystem
     * map('local', $localFs->getRoot());
     * map('system', $localFs->getDirectory(realpath(dirname(__FILE__).'/../..')));
     * map('web', $localFs->getDirectory(realpath($_SERVER['DOCUMENT_ROOT'])));
     * @param string $alias
     * @param yFilesystemResource $resource
     * @return yVirtualFileSystem
     */
    public function map($alias, $resource){
        $this->_map[$alias] = $resource;
        return $this;
    }
}

