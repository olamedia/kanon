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
 * yUriFragment
 *
 * @see http://tools.ietf.org/html/rfc3986#section-3.5
 *
 * @package yuki
 * @subpackage uri
 * @author olamedia
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @version SVN: $Id: yUriFragment.php 109 2011-02-19 08:11:02Z olamedia@gmail.com $
 */
class yUriFragment{
    protected $_fragment = '';
    public function loadString($fragmentString){
        $this->_fragment = $fragmentString;
    }
    public function __toString(){
        return (string) (strlen($this->_fragment)?'#':'').$this->_fragment;
    }
}

