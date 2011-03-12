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
 * ySitemapUrlSet
 *
 * @package yuki
 * @subpackage sitemap
 * @author olamedia
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @version SVN: $Id$
 */
class ySitemapUrlSet implements ArrayAccess, Countable{
    protected $_map = array();
    protected $_controller = null;
    public function setController($sitemapController){
        $this->_controller = $sitemapController;
        return $this;
    }
    /**
     *
     * @param ySitemapUrl $url 
     */
    public function add($url){
        $this->_map[$url->getLocation()] = $url;
        return $this;
    }
    public function remove($url){
        unset($this->_map[$url->getLocation()]);
        return $this;
    }
    public function has($url){
        return isset($this->_map[$url->getLocation()]);
    }
    public function get($url, $default = null){
        return $this->has($url)?$this->_map[$url->getLocation()]:$default;
    }
    public function offsetExists($offset){
        return $this->has($offset);
    }
    public function offsetGet($offset){
        return $this->get($offset);
    }
    public function offsetSet($offset, $value){
        if ($offset === null){
            $this->add($value);
        }
    }
    public function offsetUnset($offset){
        $this->remove($offset);
    }
    public function count(){
        return count($this->_map);
    }
    protected function _prepare(){
        foreach ($this->_map as $url){
            $url->setController($this->_controller);
        }
    }
    public function __toString(){
        $this->_prepare();
        return '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n".
        implode("\n", $this->_map)."\n".
        '</urlset>';
    }
    public static function create(){
        return new ySitemapUrlSet();
    }
}
