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
    /**
     * Filesystem instances
     * Array key is an id of filesystem
     * @var array
     */
    protected static $_instances = array();
    /**
     * URI => path map
     * @var array
     */
    protected $_uriMap = array();
    /**
     * Unique filesystem id
     * @var int
     */
    protected $_id = null;
    /**
     * Creates file or modifies atime+mtime of resource at given path.
     * @param string $path Path of resource
     */
    public function touch($path){
        return $this->getResource($path)->touch();
    }
    /**
     * Uploads local file into given location
     * @param string $tmp Local file name
     * @param string $path Path in this filesystem
     */
    public function upload($tmp, $path){
        return $this->getResource($path)->upload($tmp);
    }
    /**
     * Gets contents of resource.
     * @abstract
     * @param yFilesystemResource $yResource
     * @return string
     */
    public function getResourceContents($yResource){
        throw new BadMethodCallException('Not implemented');
    }
    /**
     * Removes resource at given location if possible
     * @abstract
     * @param yFilesystemResource $yResource
     * @return yFilesystemResource
     */
    public function unlinkResource($yResource){
        throw new BadMethodCallException('Not implemented');
    }
     /**
     * Checks if resource exists.
     * @param yFilesystemResource $yResource
     * @return boolean
     */
    public function resourceExists($yResource){
        throw new BadMethodCallException('Not implemented');
    }
    /**
     * Gets filesystem resource.
     * @abstract
     * @param string $path 
     * @return yFilesystemResource
     */
    public function getResource($path){
        throw new BadMethodCallException('Not implemented');
    }
    /**
     * Creates file or modifies atime+mtime of resource
     * @abstract
     * @param yFilesystemResource $yResource 
     */
    public function touchResource($yResource){
        throw new BadMethodCallException('Not implemented');
    }
    /**
     * Uploads local file into given location.
     * @abstract
     * @param string $tmp Local file name.
     * @param yFilesystemResource $yResource Location to upload
     */
    public function uploadResource($tmp, $yFile){
        throw new BadMethodCallException('Not implemented');
    }
    /**
     * Makes directory at given location.
     * @abstract
     * @param yFilesystemResource $yResource 
     * @return yDirectory
     */
    public function makeResourceDirectory($yResource){
        throw new BadMethodCallException('Not implemented');
    }
    /**
     * Removes resource at given location.
     * If second paremeter is true, forces recursive removing.
     * @abstract
     * @param yFilesystemResource $yResource 
     * @param boolean $force
     */
    public function removeResource($yResource, $force = false){
        throw new BadMethodCallException('Not implemented');
    }
    /**
     * Gets URI for given resource if possible.
     * URI-path mapping is possible using mapURI() method.
     * @throws Exception
     * @param yFilesystemResource $yResource
     * @return string URI
     */
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
            //echo "URI Found: ".$uri."\nMapped to $path\nLocal path: $rPath\nRelative: $rel\n\n";
            return $uri.$rel;
        }
        //var_dump($this);
        throw new Exception('Resource "'.$yResource->getPath().'" not mapped to uri');
    }
    /**
     * Maps given URI to given path.
     * @param string $uri
     * @param string $path 
     * @return yFileSystem
     */
    public function mapUri($uri, $path){
        $this->_uriMap[$path] = $uri;
        return $this;
    }
    /**
     * Gets unique id
     * @staticvar int $i
     * @return int
     */
    public function getId(){
        static $i = 0;
        if ($this->_id === null){
            $this->_id = ++$i; // a little strange way <_<
        }
        return $this->_id;
    }
    /**
     * Constructor.
     */
    public function __construct(){
        $this->register();
    }
    /**
     * Gets filesystem by id.
     * @param integer $id
     * @return yFileSystem
     */
    public static function get($id){
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
    /**
     * Gets string representation of filesystem ("class#id")
     * @return string
     */
    public function __toString(){
        return get_class($this).'#'.$this->getId();
    }
}

