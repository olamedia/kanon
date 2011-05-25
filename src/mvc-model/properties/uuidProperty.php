<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * uuidProperty
 *
 * @package kanon
 * @subpackage model
 * @author olamedia
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */
class uuidProperty extends stringProperty{
    public function preSave(){
        if ($this->getValue() == ''){
            $this->setValue(uuid::v4());
        }
    }
}

