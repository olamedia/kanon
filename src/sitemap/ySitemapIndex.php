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
 * ySitemapIndex
 *
 * @package yuki
 * @subpackage sitemap
 * @author olamedia
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @version SVN: $Id$
 */
class ySitemapIndex{
    protected static $_instance = null;
    protected $_map = array();
    protected $_controller = null;
    protected $_controllers = array();
    /**
     * @return ySitemapIndex
     */
    public static function getInstance(){
        if (self::$_instance === null){
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    public function setController($sitemapController){
        $this->_controller = $sitemapController;
        return $this;
    }
    public function registerController($name, $controllerName){
        $this->_controllers[$name] = $controllerName;
        $this->add(ySitemap::create('')->setName($name));
    }
    public function getControllers(){
        return $this->_controllers;
    }
    /**
     *
     * @param ySitemap $sitemap 
     */
    public function add($sitemap){
        $sitemap->setOutputMode('index');
        $this->_map[] = $sitemap;
    }
    protected function _prepare(){
        foreach ($this->_map as $sitemap){
            $sitemap->setController($this->_controller);
        }
    }
    public function __toString(){
        $this->_prepare();
        return '<?xml version="1.0" encoding="UTF-8"?>'.
        '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.
        implode("\n", $this->_map).
        '</sitemapindex>';
    }
}

