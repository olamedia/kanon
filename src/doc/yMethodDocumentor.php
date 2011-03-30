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

//require_once dirname(__FILE__).'/../debug/yDebugger.php';

/**
 * yMethodDocumentor - wrapper around ReflectionMethod
 * Provides helper methods for html generation
 *
 * @package yuki
 * @subpackage doc
 * @author olamedia
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @version SVN: $Id: yMethodDocumentor.php 127 2011-02-19 22:13:41Z olamedia@gmail.com $
 */
class yMethodDocumentor{
    /**
     * @var ReflectionMethod 
     */
    protected $_method = null;
    public function __construct(ReflectionMethod $method){
        $this->_method = $method;
    }
    /**
     *
     * @return string Method synopsis html
     */
    public function getSynopsisHtml(){
        $docBlock = yDocBlock::fromString($this->_method->getDocComment());
        $h = '';
        $h .= '<div class="method">';
        if ($docBlock->hasAnnotation('return')){
            $h .= ''.reset(explode(' ', $docBlock->getAnnotation('return'))).' ';
        }
        /* if ($this->_method->isPublic()){
          $h .= 'public ';
          } */
        if ($this->_method->isPrivate()){
            $h .= 'private ';
        }
        if ($this->_method->isProtected()){
            $h .= 'protected ';
        }
        if ($this->_method->isStatic()){
            $h .= 'static ';
        }
        $h .= '<a href="#method.'.$this->_method->getName().'">'.$this->_method->getName().'</a>';
        $h .= '(';
        $h .= $this->getArgString();
        $h .= ');';
        $h .= '</div>';
        return $h;
    }
    public function getHref(){
        return $this->_method->getDeclaringClass()->getName().'.html#method.'.$this->_method->getName();
    }
    public function getLink(){
        return '<a href="#method.'.$this->_method->getName().'">'.$this->_method->getName().'</a>';
    }
    public function getSectionHtml(){
        $docBlock = yDocBlock::fromString($this->_method->getDocComment());
        $sdesc = $docBlock->getShortDescription();
        $h = '';
        $h .= '<div style="padding: 4px;border: solid 1px #666;">';

        $h .= '<a name="method.'.$this->_method->getName().'"></a>';
        $h .= '<div style="font-size: 11px;">';
        $h .= '<div class="classsynopsis">';
        $h .= $this->getSynopsisHtml();
        $h .= '</div>';

        /* $h .= '<h3>'.$this->_method->getDeclaringClass()->getName().'::'.$this->_method->getName();
          if (strlen($sdesc)){
          $h .= '<span style="font-weight: normal; font-size: 14px;"> — '.$sdesc.'</span>';
          }
          $h .= '</h3>'; */
        //$h .= '<h4>Description</h4>';
        //$h .= '<div class="classsynopsis">';
        $h .= '<div style="padding: 7px; margin-bottom: 2em; border: dashed 1px #eee; margin-tOP; 1em;">';
        if ($docBlock->hasAnnotation('param')){
            $params = $docBlock->getAnnotations('param');
            foreach ($params as $param){
                $a = explode(' ', $param);
                $type = array_shift($a);
                $name = array_shift($a);
                $desc = implode(' ', $a);
                $h .= '<div><b style="color: #683B00;">'.$name.'</b> ('.$type.') '.$desc.'</div>';
            }
        }
        $desc = explode("\n", $docBlock->getDescription());
        if (count($desc)){
            $h .= '<div style="margin-top: 1em; margin-bottom: 1em;">';
            $first = array_shift($desc);
            $h .= '<div><h3>'.$first.'</h3></div>';
            $h .= implode("<br />", $desc);
            $h .= '</div>';
        }
        if ($docBlock->hasAnnotation('return')){
            $h .= '<div><b>returns</b> ';
            $h .= reset(explode(' ', $docBlock->getAnnotation('return'))).' ';
            $h .= '</div>';
        }
        $h .= '</div>';
        $h .= '</div>';
        $h .= '<h3>Source Code</h3>';
        $startLine = $this->_method->getStartLine();
        $endLine = $this->_method->getEndLine();
        $h .= '<pre class="brush: php">';
        $h .= htmlspecialchars(yDebugger::getLinesSource($this->_method->getFileName(), $startLine, $endLine));
        $h .= '</pre>';
        $h .= '</div>';
        return $h;
    }
    public function getReturns(){
        if ($docBlock->hasAnnotation('return')){
            return reset(explode(' ', $docBlock->getAnnotation('return'))).' ';
        }
        return '';
    }
    public function getSummaryHtml(){
        $docBlock = yDocBlock::fromString($this->_method->getDocComment());
        $sdesc = $docBlock->getShortDescription();
        $h = '';
        //$h .= '<a name="method.'.$this->_method->getName().'"></a>';
        $h .= '<div class="summary" style="padding-bottom: 5px;margin-bottom: 5px;border-bottom: 1px solid #AAA;">';
        //$h .= '<h3>';
        $h .= $this->getSynopsisHtml();
        //$h .= '</h3>';
        $h .= '<div style="font-size: 11px; margin-top: 1em;">'.$sdesc.'</div>';
        $h .= '</div>';
        return $h;
    }
    public function getArgString(){
        $args = array();
        $optArgs = array();
        foreach ($this->_method->getParameters() as $parameter){
            //$parameter = new ReflectionParameter();
            $arg = '$'.$parameter->getName().'';
            if ($parameter->isDefaultValueAvailable()){
                $val = $parameter->getDefaultValue();
                $arg .= ' = ';
                if (is_string($val)){
                    $arg .= '"'.$val.'"';
                }else{
                    $arg .= $val;
                }
            }
            $parameter->isPassedByReference();
            /* if ($parameter->isOptional()){
              $optArgs[] = $arg;
              }else{ */
            $args[] = $arg;
            //}
        }
        $argString = implode(', ', $args);
        if (count($optArgs)){
            $optString = implode(' [, ', $optArgs).str_repeat(']', count($optArgs) - 1);
        }else{
            $optString = '';
        }
        if (strlen($optString)){
            if (strlen($argString)){
                $argString .= ' [, '.$optString.']';
            }else{
                $argString = '['.$optString.']';
            }
        }
        return '<span class="param">'.(strlen($argString)?$argString:'').'</span>';
    }
}

