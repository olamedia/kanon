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
 * yHtmlHelper
 *
 * @package yuki
 * @subpackage html
 * @author olamedia
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */
class yHtmlHelper{
    protected static $_instance = null;
    protected $_dom = null;
    /**
     *
     * @return yHtmlHelper 
     */
    public static function getInstance(){
        if (self::$_instance === null){
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    /**
     * @return DOMDocument 
     */
    public function getDom(){
        if ($this->_dom === null){
            $this->_dom = new DOMDocument('1.0', 'UTF-8');
            $this->_dom->preserveWhiteSpace = false;
            $this->_dom->formatOutput = true;
        }
        return $this->_dom;
    }
    public static function format($source){
        $doc = new DOMDocument();
        $doc->preserveWhiteSpace = false;
        $doc->formatOutput = true;
        $doc->loadXML($source);
        return $doc->saveXML();
    }
}

