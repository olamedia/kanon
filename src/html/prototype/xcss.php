<?php

/**
 * Extended css parser (sass inspired http://sass-lang.com/)
 * 
 * $xcss = new xcss('style.xcss');
 * echo $xcss;
 * $xcss->save('style.css');
 *
 * @author olamedia
 *
 * xcss syntax:
 *
 *
 * $blue: #3bbfce;
 * $margin: 16px;
 * 
 *
 */
class xcss{
    protected $_filename = null;
    protected $_source = '';
    protected $_blocks = array();
    protected $_vars = array();
    const
    XCSS_BLOCK = 1,
    XCSS_EXPRESSION = 2;
    public function __construct($filename){
        $this->_filename = $filename;
    }
    public function &getSource(){
        if ($this->_source === null){
            $this->_source = file_get_contents($this->_filename);
            $this->_stripComments();
            $this->_explode();
        }
        return $this->_source;
    }
    protected function _stripComments(){
        while (($p = strpos($this->_source, '/*')) !== false){
            if (($cp = strpos($this->_source, '*/', $p)) !== false){
                $this->_source = substr($this->_source, 0, $p).substr($this->_source, $cp);
            }
        }
    }
    protected function _explode(){
        while ($block = $this->_getBlock()){
            $this->_blocks[] = $block;
        }
    }
    protected function _getBlock(){
        $type = null;
        $context = null;
        $content = null;
        // 1. looking for { or ;
        list($c, $p) = $this->_getBlockStart();
        if ($p === false){
            // no opening char, return
            return false;
        }
        if ($c == ';'){
            $type = self::XCSS_EXPRESSION;
            $content = substr($this->_source, 0, $p);
        }else{
            $type = self::XCSS_BLOCK;
            $context = substr($this->_source, 0, $p);
            $content = array();
            $offset = $p;
            while ($expression = $this->_getBlock()){
                list($type, $context, $content) = $expression;
                if ($type != self::XCSS_EXPRESSION){
                    break;
                }
                $content[] = $expression;
            }
        }
        return array($type, $context, $content);
    }
    protected function _getBlockStart($offset = 0){
        // 1. looking for { or ;
        $bp = strpos($this->_source, '{', $offset);
        $dp = strpos($this->_source, ';', $offset);
        if ($bp === false || $dp < $bp)
            return array(';', $dp);
        return array('{', $bp);
    }
}

