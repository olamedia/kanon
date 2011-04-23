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
 * yFormControlSet
 *
 * @package yuki
 * @subpackage forms
 * @author olamedia
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */
class yFormControlSet implements IteratorAggregate{
    protected $_prefix = null;
    protected $_key = null;
    protected $_controls = array();
    /**
     * Type of layout, see templates folder
     * Possible values: 
     * fieldset: Groups related controls and shows a border around them. (<fieldset><legend>)
     * list|ul: Unordered list, each control inside <li>.
     * ol: Ordered list, each control inside <li>.
     * block: Vertical layout - each control on a new line (inside <div>).
     * inline: Shows all controls on a same line, if possible (each inside span).
     * table: Not-so-good, but may be useful for variable-length labels.
     * page: Groups controls as page in a multi-page form.
     * @var string 
     */
    protected $_layout = 'block';
    protected $_blockContainer = 'ol';
    protected $_requiredTitle = 'required'; // <abbr title="required">*</abbr>
    protected $_optionalTitle = 'optional'; // <em>(optional)</em>
    /**
     * Control set label.
     * For fieldset layout it displayed as <legend>
     * For other types it displayed as <h#>
     * @var string
     */
    protected $_label = null;
    /**
     *
     * @param string $name
     * @return yFormControl
     */
    public function getControl($name){
        if (!isset($this->_controls[$name])){
            // FIXME throw exception
            return;
        }
        if (is_array($this->_controls[$name])){
            $info = $this->_controls[$name];
            $class = $info['class'];
            $inputName = isset($info['name'])?$info['name']:$name;
            $control = new $class($inputName);
            $control->setPrefix($this->_prefix);
            $control->setKey($this->_key);
            if (isset($info['label'])){
                $control->setLabelText($info['label']);
            }
            if (isset($info['help'])){
                $control->setHelpText($info['help']);
            }
            if (isset($info['required'])){
                $control->setRequired(!!$info['required']);
            }
            if (isset($info['validator'])){
                $validators = $info['validator'];
                if (is_string($validators)){ // 'myValidator'
                    $control->addValidator(new $validators());
                }elseif (is_array($validators)){ // array(validator1, validator2)
                    foreach ($validators as $k=>$v){
                        if (is_string($k) && is_array($v)){ // array(... 'myValidator' => array(options) ...)
                            $validator = new $k($v);
                        }elseif (is_int($k) && is_string($v)){
                            $validator = new $v(); // array(..., 'myValidator', ...)
                        }else{
                            continue; // skip anything else
                        }
                        $control->addValidator($validator);
                    }
                }
            }
            $this->_controls[$name] = $control;
        }
        return $this->_controls[$name];
    }
    public function processKey(){
        foreach ($this->_controls as $name=>$c){
            $control = $this->getControl($name); // force convert to object
            $control->processKey();
        }
    }
    public function getValues(){
        $a = array();
        foreach ($this->_controls as $name=>$c){
            $a[$name] = strval($this->getControl($name)->getValue());
        }
        return $a;
    }
    public function setLabel($label){
        $this->_label = $label;
        return $this;
    }
    public function getLabel(){
        return $this->_label;
    }
    /**
     * Sets name prefix.
     * @param string $prefix Name prefix
     * @return yFormControl
     */
    public function setPrefix($prefix){
        $this->_prefix = $prefix;
        foreach ($this->_controls as $c){
            if (is_object($c)){
                $c->setPrefix($prefix);
            }
        }
        return $this;
    }
    /**
     * Gets name prefix.
     * @return mixed Name prefix string or null
     */
    public function getPrefix(){
        return $this->_prefix;
    }
    /**
     * Sets input key.
     * @param mixed $key Input key
     * @return yFormControl
     */
    public function setKey($key){
        $this->_key = $key;
        foreach ($this->_controls as $c){
            if (is_object($c)){
                $c->setKey($key);
            }
        }
        return $this;
    }
    /**
     * Gets input key.
     * @return mixed Input key
     */
    public function getKey(){
        return $this->_key;
    }
    public function getKeys(){
        $keys = array();
        foreach ($this->_controls as $name=>$c){
            $control = $this->getControl($name);
            $keys = array_merge($keys, $control->getKeys());
        }
        return array_unique($keys);
    }
    public function process(){
        foreach ($this->getKeys() as $key){
            $this->setKey($key);
            $this->processKey();
        }
    }
    public function getSectionHtml($key = null){
        $tpl = dirname(__FILE__).'/templates/'.$this->_layout.'.php';
        return include $tpl;
    }
    public function getFormHtml($key = null){
        $h = '<form method="POST" action="" enctype="multipart/form-data">';
        $h .= $this->getSectionHtml($key);
        $h .= '</form>';
        return $h;
    }
    public function __toString(){
        return (string) $this->getFormHtml();
    }
    protected $_iterator = null;
    /**
     * @return yFormResultIterator
     */
    public function getCurrentIterator(){
        return $this->_iterator;
    }
    /**
     * @return yFormResultIterator
     */
    public function getIterator(){
        $this->_iterator = new yFormResultIterator($this);
        return $this->_iterator;
    }
}