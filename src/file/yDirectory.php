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
 * yDirectory
 *
 * @package yuki
 * @subpackage file
 * @author olamedia
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @version SVN: $Id: yDirectory.php 150 2011-02-20 10:06:54Z olamedia@gmail.com $
 */
class yDirectory{
    protected $_path = '';
    protected $_fsid = null;
    public function __construct($path = '', $fileSystem){
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
    public function getResource($path){
        return $this->getFileSystem()->getResource($this->_path.'/'.$path);
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
}

