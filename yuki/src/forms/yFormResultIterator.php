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
 * yFormResultIterator
 *
 * @package yuki
 * @subpackage forms
 * @author olamedia
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */
class yFormResultIterator implements Iterator{
    /**
     *
     * @var yFormControlSet
     */
    protected $_form = null;
    protected $_keys = array();
    public function __construct($form){
        $this->_form = $form;
    }
    public function current(){
        $this->_form->setKey(current($this->_keys));
        $this->_form->processKey();
        return $this->_form->getValues();
    }
    public function key(){
        return current($this->_keys);
    }
    public function next(){
        return next($this->_keys);
    }
    public function rewind(){
        $this->_keys = $this->_form->getKeys();
        reset($this->_keys);
    }
    public function valid(){
        $key = key($this->_keys);
        return ($key !== null && $key !== false);
    }
}

