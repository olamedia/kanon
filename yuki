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
 * yTextNode
 *
 * @package yuki
 * @subpackage html
 * @author olamedia
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */
class yTextNode{
    protected $_value = '';
    /**
     * Constructor.
     * @param string $text 
     */
    public function __construct($text = ''){
        $this->_value = $text;
    }
    /**
     * Sets node value.
     * @param string $value
     * @return yTextNode 
     */
    public function setValue($value){
        $this->_value = $value;
        return $this;
    }
    /**
     * Gets node value.
     * @return string
     */
    public function getValue(){
        return $this->_value;
    }
    /**
     * Returns escaped string value of node.
     * @return string Escaped node value.
     */
    public function __toString(){
        return htmlspecialchars($this->_value, ENT_NOQUOTES);
    }
}

