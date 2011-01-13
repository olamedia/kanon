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
    protected $_realLocale = 'ru';
    protected $_file = null;
    protected $_line = null;
    public function __construct($msg){
        $this->_msg = $msg;
        $bt = debug_backtrace();
        $t = $bt[1];
        $this->_file = $t['file'];
        $this->_line = $t['line'];
        //echo $this->_file;
        //var_dump($bt);
    }
    public function setLocale($locale = 'ru'){
        $this->_locale = $locale;
    }
    public function setArguments($args){
        $this->_args = $args;
    }
    protected function _getTemplate(){
        $d = dirname($this->_file);
        $f = basename($this->_file);
        $lcPath = $d.'/locale/'.$this->_locale.'/'.$f;
        l10n::loadFile($this->_locale, $lcPath);
        $this->_lmsg = l10n::getTemplate($this->_locale, $this->_msg);
        echo $lcPath.' ';
    }
    protected function _applyForms(){
        $changed = false;
        if (preg_match_all("#\{(GENDER|PLURAL):\\\$([^\|{]+)((\|([^|{]+))+)\}#ims", $this->_lmsg, $subs)){
            //var_dump($subs);
            foreach ($subs[1] as $k => $call){
                $match = $subs[0][$k];
                $word = $this->_args[intval($subs[2][$k])];
                var_dump($word);
                $forms = explode('|', $subs[3][$k]);
                array_shift($forms);
                switch ($call){
                    case 'GENDER':
                        $form = ruLanguage::gender($word->gender(), $forms);
                        echo 'form:'.$form.' ';
                        $this->_lmsg = str_replace($match, $form, $this->_lmsg);
                        break;
                    case 'PLURAL':
                        $form = ruLanguage::plural($word->num(), $forms);
                        echo 'form:'.$form.' ';
                        $this->_lmsg = str_replace($match, $form, $this->_lmsg);
                        break;
                }
                echo 'called '.$call.' on '.$word.' with ';
                var_dump($forms);
                echo '<hr />';
            }
        }
        if ($changed) $this->_applyForms();
    }
    public function getLocalizedMessage(){
        $this->_getTemplate();
        $this->_applyForms();
        return $this->_lmsg;
    }
    public function __toString(){
        return (string) $this->getLocalizedMessage();
    }
}

