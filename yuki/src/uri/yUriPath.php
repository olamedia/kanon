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
 * yUriPath
 *
 * @see http://tools.ietf.org/html/rfc3986#section-3.3
 *
 * @package yuki
 * @subpackage uri
 * @author olamedia
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @version SVN: $Id: yUriPath.php 109 2011-02-19 08:11:02Z olamedia@gmail.com $
 */
class yUriPath{
    protected
    $_isAbsolute = false,
    $_segments = array(),
    $_isDir = false;
    public function setAbsolute($absolute = true){
        $this->_isAbsolute = $absolute;
    }
    public function loadString($pathString){
        $this->_segments = array();
        $this->_isAbsolute = false;
        $this->_isDir = false;
        if (strlen($pathString)){
            // 1. Check if absolute
            $this->_isAbsolute = $pathString{0} == '/';
            // 2. Check if directory
            $this->_isDir = $pathString[strlen($pathString) - 1] == '/';
            // 3. Explode path
            $segments = explode('/', $pathString);
            foreach ($segments as $segment){
                if (strlen($segment)){
                    if ($segment == '.'){
                        // do nothing - current directory
                    }elseif ($segment === '..'){
                        if (count($this->_segments) && (end($this->_segments) !== '..')){
                            // pop out last segment
                            array_pop($this->_segments);
                        }else{
                            // leave
                            $this->_segments[] = $segment;
                        }
                    }else{
                        $this->_segments[] = $segment;
                    }
                }
            }
        }
    }
    public function __toString(){
        return (string)
        ($this->_isAbsolute?'/':'').
        implode('/', $this->_segments).
        (($this->_isDir && count($this->_segments))?'/':'');
    }
}

