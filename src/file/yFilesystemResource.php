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
 * yFilesystemResource
 *
 * @package yuki
 * @subpackage file
 * @author olamedia
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */
class yFilesystemResource{
    protected $_path = '';
    protected $_fsid = null;
    public function __construct($path, $fileSystem){
        $this->_path = $path;
        $this->_fsid = $fileSystem->getId();
    }
    /**
     *
     * @return yFileSystem
     */
    public function getFileSystem(){
        return yFileSystem::get($this->_fsid);
    }
    public function getResource($path = ''){
        return $this->getFileSystem()->getResource($this->_path.($path === ''?'':'/'.$path));
    }
    public function setPath($path){
        $this->_path = (string) $path;
        return $this;
    }
    public function getPath(){
        return $this->_path;
    }
    public function __toString(){
        return $this->_path;
    }
    /**
     * Attempts to set the access and modification times of the file. 
     * If the file does not exist, it will be created.
     * @return yFile 
     */
    public function touch(){
        $this->getFileSystem()->touchResource($this);
        return $this;
    }
    /**
     * Upload file to location of this resource
     * @param string $tmp Local temporary file name
     */
    public function upload($tmp){
        $this->getFileSystem()->uploadResource($tmp, $this);
    }
    public function getUri(){
        return $this->getFileSystem()->getResourceUri($this);
    }
    public function unlink(){
        return $this->getFileSystem()->unlinkResource($this);
    }
    public function exists(){
        return $this->getFileSystem()->resourceExists($this);
    }
    public function getContents(){
        return $this->getFileSystem()->getResourceContents($this);
    }
    public function remove($force = false){
        return $this->getFileSystem()->removeResource($this, $force);
    }
    public function mkDir(){
        return $this->getFileSystem()->makeResourceDirectory($this);
    }
}

