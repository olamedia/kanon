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
 * yUriScheme
 *
 * @see http://tools.ietf.org/html/rfc3986#section-3.1
 *
 * @package yuki
 * @subpackage uri
 * @author olamedia
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @version SVN: $Id: yUriScheme.php 109 2011-02-19 08:11:02Z olamedia@gmail.com $
 */
class yUriScheme{
    protected $_scheme = '';
    public function clear(){
        $this->_scheme = '';
    }
    public function loadString($schemeString){
        $this->_scheme = $schemeString;
    }
    public function __toString(){
        return (string) $this->_scheme.(strlen($this->_scheme)?':':'');
    }
}

