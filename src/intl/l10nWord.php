<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Word argument for l10n::_ method
 *
 * @author olamedia
 */
class l10nWord {
    protected $_word = '';
    protected $_num = 1;
    protected $_gender = 'male';
    public function __construct($word){
        $this->_word = (string) $word;
    }
    public function gender($gender = null){
        if ($gender === null) return $this->_gender;
        $this->_gender = $gender;
        return $this;
    }
    public function num($num = null){
        if ($num === null) return $this->_num;
        $this->_num = $num;
        return $this;
    }
    public function __toString(){
        return (string) $this->_word;
    }
}

