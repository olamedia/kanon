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
 */
class yFileSystem{
    protected static $_instances = array();
    protected $_uriMap = array();
    /**
     * Unique id
     * @var int
     */
    protected $_id = null;
    public function touch($path){
        $this->getResource($path)->touch();
    }
    public function upload($tmp, $path){
        $this->getResource($path)->upload($tmp);
    }
    public function getResourceContents($yResource){
        throw new BadMethodCallException('Not implemented');
    }
    public function unlinkResource($yResource){
        throw new BadMethodCallException('Not implemented');
    }
    public function resourceExists($yResource){
        throw new BadMethodCallException('Not implemented');
    }
    /**
     * @param string $path 
     * @return yFilesystemResource
     */
    public function getResource($path){
        throw new BadMethodCallException('Not implemented');
    }
    public function touchResource($yResource){
        throw new BadMethodCallException('Not implemented');
    }
    public function uploadResource($tmp, $yFile){
        throw new BadMethodCallException('Not implemented');
    }
    public function makeResourceDirectory($yResource){
        throw new BadMethodCallException('Not implemented');
    }
    public function removeResource($yResource, $force = false){
        throw new BadMethodCallException('Not implemented');
    }
    public function getResourceUri($yResource){
        $rPath = $yResource->getPath();
        $bestMatchLength = 0;
        $bestMatch = null;
        foreach ($this->_uriMap as $path=>$uri){
            $l = strlen($path);
            if (substr($rPath, 0, $l) === $path){
                if ($l >= $bestMatchLength){
                    $bestMatchLength = $l;
                    $bestMatch = $path;
                }
            }
        }
        if ($bestMatch !== null){
            $path = $bestMatch;
            $l = $bestMatchLength;
            $uri = $this->_uriMap[$path];
            $rel = substr($rPath, $l + 1);
            if ($rel === false){
                $rel = '';
            }
            echo "URI Found: ".$uri."\nMapped to $path\nLocal path: $rPath\nRelative: $rel\n\n";
            return $uri.$rel;
        }
        var_dump($this);
        throw new Exception('Resource "'.$yResource->getPath().'" not mapped to uri');
    }
    public function mapUri($uri, $path){
        $this->_uriMap[$path] = $uri;
    }
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

