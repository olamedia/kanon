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
 * yLocalFileSystem
 *
 * @package yuki
 * @subpackage file
 * @author olamedia
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @version SVN: $Id: yLocalFileSystem.php 150 2011-02-20 10:06:54Z olamedia@gmail.com $
 */
class yLocalFileSystem extends yFileSystem{
    public function getRoot(){
        return $this->getResource('/');
    }
    public function getResource($path){
        if (is_file($path)){
            return new yFile($path, $this);
        }
        if (is_dir($path)){
            return new yDirectory($path, $this);
        }
        return false;
    }
    public function copy($resource, $target){
        
    }
}

