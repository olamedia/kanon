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
 * yFile
 *
 * @package yuki
 * @subpackage file
 * @author olamedia
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @version SVN: $Id: yFile.php 150 2011-02-20 10:06:54Z olamedia@gmail.com $
 */
class yFile{
    protected $_filename = '';
    protected $_fsid = null;
    public function __construct($filename, $fileSystem){
        $this->_filename = $filename;
        $this->_fsid = $fileSystem->getId();
    }
    public function getFileSystem(){
        return yFileSystem::get($this->_fsid);
    }
    public function __call($name, $arguments){
        array_unshift($arguments, $this);
        return call_user_func_array(array($this->getFileSystem(), $name), $arguments);
    }
    public function __toString(){
        return (string) $this->_filename;
    }
}

