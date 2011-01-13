<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of l10nMessage
 *
 * @author olamedia
 */
class l10nMessage{
    protected $_msg = '';
    protected $_lmsg = null;
    protected $_args = array();
    protected $_locale = 'ru';
    public function __construct($msg){
        $this->_msg = $msg;
    }
    public function setLocale($locale = 'ru'){
        $this->_locale = $locale;
    }
    public function setArguments($args){
        $this->_args = $args;
    }
    public function getLocalizedMessage(){
        
        return $this->_msg;
    }
    public function __toString(){
        return (string) $this->getLocalizedMessage();
    }
}

