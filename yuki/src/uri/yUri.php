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
 * yUri represents an URI
 * URI = scheme ":" hier-part [ "?" query ] [ "#" fragment ]
 * @see http://tools.ietf.org/html/rfc3986#section-1.1.1
 * @package yuki
 * @subpackage uri
 * @author olamedia
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @version SVN: $Id: yUri.php 109 2011-02-19 08:11:02Z olamedia@gmail.com $
 * @param yUriScheme $_scheme
 * @param yUriAuthority $_authority
 * @param yUriPath $_path
 * @param yUriQuery $_query
 * @param yUriFragment $_fragment
 */
class yUri{
    /**
     * @internal
     * @var yUriScheme 
     */
    protected $_scheme = null;
    /**
     * @internal
     * @var yUriAuthority 
     */
    protected $_authority = null;
    /**
     * @internal
     * @var yUriPath 
     */
    protected $_path = null;
    /**
     * @internal
     * @var yUriQuery 
     */
    protected $_query = null;
    /**
     * @internal
     * @var yUriFragment 
     */
    protected $_fragment = null;
    /**
     * Loads and parse URI string into components.
     * @param string $uriString
     * @return yUri
     */
    public function loadString($uriString){
        // 1. Remove fragment
        $this->_fragment = new yUriFragment();
        $fp = strpos($uriString, '#');
        if ($fp !== false){
            $this->_fragment->loadString(substr($uriString, $fp + 1));
            $uriString = substr($uriString, 0, $fp);
        }
        // 2. Remove query
        $this->_query = new yUriQuery();
        $qp = strpos($uriString, '?');
        if ($qp !== false){
            $this->_query->loadString(substr($uriString, $qp + 1));
            $uriString = substr($uriString, 0, $qp);
        }
        // 3. Remove scheme
        $this->_scheme = new yUriScheme();
        $sp = strpos($uriString, ':');
        if ($sp !== false){ // if defined(R.scheme) then
            $this->_scheme->loadString(substr($uriString, 0, $sp));
            $uriString = substr($uriString, $sp + 1);
        }
        // 4, Remove authority
        $this->_authority = new yUriAuthority();
        $ap = strpos($uriString, '//');
        if ($ap === 0){
            // 4.1 Lookup path start
            $pp = strpos($uriString, '/', 2);
            if ($pp !== false){
                // "//" authority path-absolute
                $this->_authority->loadString(substr($uriString, 2, $pp - 2));
                $uriString = substr($uriString, $pp); // leave /
            }else{
                // "//" authority path-empty
                $this->_authority->loadString(substr($uriString, 2));
                $uriString = '';
            }
        }
        // 5. Load path
        $this->_path = new yUriPath();
        $this->_path->loadString($uriString);
        return $this;
    }
    /**
     * Create new yUri instance from uri string
     * @param string $uriString
     * @return yUri
     */
    public static function fromString($uriString){
        $uri = new yUri();
        $uri->loadString($uriString);
        return $uri;
    }
    /**
     *
     * @return yUriScheme 
     */
    public function getScheme(){
        return $this->_scheme;
    }
    /**
     *
     * @return yUriAuthority
     */
    public function getAuthority(){
        return $this->_authority;
    }
    /**
     *
     * @return yUriPath
     */
    public function getPath(){
        return $this->_path;
    }
    /**
     * Makes relative uri if possible or return untouched
     * @param string $uri
     */
    public function getRelativeTo($uri){
        $rel = clone $this;
        if (is_string($uri)){
            $uri = yUri::fromString($uri);
        }
        if (strval($uri->getScheme()) === strval($rel->getScheme())){
            $rel->getScheme()->clear();
            if (strval($uri->getAuthority()) === strval($rel->getAuthority())){
                $rel->getAuthority()->clear();
                $rel->getPath()->setAbsolute(false);
            }
        }
        return $rel;
    }
    /**
     * The __toString method allows a class to decide how it will react when it is treated like a string.
     * @return string
     */
    public function __toString(){
        return (string)
        $this->_scheme.
        $this->_authority.
        $this->_path.
        $this->_query.
        $this->_fragment;
    }
    public function __clone(){
        $this->_scheme = clone $this->_scheme;
        $this->_authority = clone $this->_authority;
        $this->_path = clone $this->_path;
        $this->_query = clone $this->_query;
        $this->_fragment = clone $this->_fragment;
    }
}

