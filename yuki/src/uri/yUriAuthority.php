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
 * yUriAuthority
 * 
 * authority = [ userinfo "@" ] host [ ":" port ]
 * 
 * The authority component is preceded by a double slash ("//") and is
 * terminated by the next slash ("/"), question mark ("?"), or number
 * sign ("#") character, or by the end of the URI.
 * 
 * @see http://tools.ietf.org/html/rfc3986#section-3.2
 *
 * @package yuki
 * @subpackage uri
 * @author olamedia
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @version SVN: $Id: yUriAuthority.php 109 2011-02-19 08:11:02Z olamedia@gmail.com $
 */
class yUriAuthority{
    protected
    $_userinfo = null,
    $_host = null,
    $_port = null;
    public function clear(){
        $this->_host = '';
    }
    /**
     *
     * @param string $authorityString
     * @return yUriAuthority
     */
    public function loadString($authorityString){
        // FIXME: support userinfo & port
        $this->_host = $authorityString;
        return $this;
    }
    public function __toString(){
        return (string) (strlen($this->_host)?'//':'').$this->_host;
    }
}

