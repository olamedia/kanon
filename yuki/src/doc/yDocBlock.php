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
 * yDocBlock
 *
 * @package yuki
 * @subpackage doc
 * @author olamedia
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @version SVN: $Id: yDocBlock.php 109 2011-02-19 08:11:02Z olamedia@gmail.com $
 */
class yDocBlock{
    private $_annotations = array();
    private $_description = '';
    private $_shortDescription = '';
    public function loadString($docBlock){
        $lines = explode("\n", $docBlock);
        array_shift($lines); // /**
        array_pop($lines); // */
        $desc = array();
        foreach ($lines as $k=>$line){
            $lines[$k] = $line = ltrim($line, "* \t");
            if (strlen($line) && $line{0} == '@'){
                // annotation
                if (preg_match("#^@([a-z]+)(\s+(.*))?$#ims", $line, $subs)){
                    $this->_annotations[$subs[1]][] = isset($subs[3])?$subs[3]:'';
                }
            }else{
                $desc[] = $line;
            }
        }
        $this->_description = implode("\n", $desc);
        $this->_shortDescription = count($desc)?trim(reset($desc)):'';
    }
    public function getDescription(){
        return $this->_description;
    }
    public function getShortDescription(){
        return $this->_shortDescription;
    }
    public function getAnnotation($name){
        if (isset($this->_annotations[$name])){
            $a = $this->_annotations[$name];
            return array_shift($a);
        }
        return null;
    }
    public function getAnnotations($name){
        if (isset($this->_annotations[$name])){
            $a = $this->_annotations[$name];
            return $a;
        }
        return null;
    }
    /**
     * Checks if the method has annotation
     * @param string $name
     * @return boolean TRUE if the method has annotation, otherwise FALSE 
     */
    public function hasAnnotation($name){
        return isset($this->_annotations[$name]);
    }
    /**
     *
     * @param string $docBlock
     * @return yDocBlock 
     */
    public static function fromString($docBlock){
        $b = new self();
        $b->loadString($docBlock);
        return $b;
    }
}

