<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * yCommentNode
 *
 * @package yuki
 * @subpackage html
 * @version SVN: $Id$
 * @revision SVN: $Revision$
 * @date $Date$
 * @author olamedia
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */
class yJsCommentNode{
    protected $_value = '';
    public function __construct($text = ''){
        $this->_value = $text;
    }
    public function getValue(){
        return $this->_value;
    }
    public function __toString(){
        return '//<!--'."\r\n".
        ($this->_value)."\r\n".
        '//-->';
    }
}

