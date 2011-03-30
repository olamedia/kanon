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
 * yTextInput
 *
 * @package yuki
 * @subpackage forms
 * @author olamedia
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */
class yTextInput extends yFormControl{
    public function __construct($name){
        parent::__construct('input', array(
            'type'=>'text',
        ));
        $this->setName($name);
        $this->setAttribute('value', '');
    }
    public function getKeys(){
        $name = $this->_getFullName();
        if (isset($_POST[$name])){
            if (is_array($_POST[$name])){
                return array_keys($_POST[$name]);
            }else{
                return array(null);
            }
        }
        return array();
    }
}

