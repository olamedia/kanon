<?php

/**
 * Extended css parser (sass inspired http://sass-lang.com/)
 * 
 * $scss = new scss('style.css');
 * echo $scss;
 * $scss->save('style.css');
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
    public function __construct($filename){
        $this->_filename = $filename;
    }
    public function &getSource(){
        if ($this->_source === null){
            $this->_source = file_get_contents($this->_filename);
            $this->_stripComments();
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
}

