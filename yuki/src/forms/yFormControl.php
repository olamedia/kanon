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
 * yFormControl
 *
 * @package yuki
 * @subpackage forms
 * @uses yHtmlTag
 * @author olamedia
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */
class yFormControl extends yHtmlTag{
    /**
     * Prefix of the input name
     * null:        name="name"
     * "prefix":    name="prefix_name"
     * @var string Default null
     */
    protected $_prefix = null;
    /**
     * Name of the input, required
     * "name":      name="name"
     * @var string Default null
     */
    protected $_name = null;
    /**
     * Unique id for using multiple inputs with the same name
     * null:        name="name"
     * 4587:        name="name[4587]"
     * @var string Default null
     */
    protected $_key = null;
    protected $_uniqId = null;
    protected $_value = null;
    protected $_label = null;
    protected $_help = '';
    protected $_validators = array();
    protected $_errorMessages = array();
    protected $_isRequired = false;
    public function __construct($name = 'input', $attr = array()){
        parent::__construct($name, $attr);
        $this->_label = new yHtmlTag('label');
    }
    protected function _getId(){
        if ($this->_key !== null && $this->_key !== true){
            return $this->_key;
        }
        if ($this->_uniqId === null){
            $this->_uniqId = ++self::$_ai;
        }
        return $this->_uniqId;
    }
    protected function _getFullName(){
        return ($this->_prefix === null?'':$this->_prefix.'_').$this->_name;
    }
    protected function _getFullKey(){
        return ($this->_key === null?
                '':
                ($this->_key === true?
                        '[]':
                        (is_int($this->_key)?
                                '['.$this->_key.']':
                                "['".str_replace("'", "\\'", $this->_key)."']"
                        )
                )
        );
    }
    protected static $_ai = 0;
    protected function _getFullKeyId(){
        return ($this->_key === null?
                '':
                '-'.$this->_getId()
        );
    }
    protected function _getHtmlName(){
        return $this->_getFullName().$this->_getFullKey();
    }
    protected function _getHtmlId(){
        return $this->_getFullName().$this->_getFullKeyId();
    }
    protected function _updateName(){
        $this->setAttribute('id', $this->_getHtmlId());
        $this->setAttribute('name', $this->_getHtmlName());
    }
    /**
     * Sets name prefix.
     * @param string $prefix Name prefix
     * @return yFormControl
     */
    public function setPrefix($prefix){
        $this->_prefix = $prefix;
        $this->_updateName();
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
     * Sets input name.
     * @param string $name Input name
     * @return yFormControl
     */
    public function setName($name){
        $this->_name = $name;
        $this->_updateName();
        return $this;
    }
    /**
     * Gets input name.
     * @return string Input name
     */
    public function getName(){
        return $this->_name;
    }
    /**
     * Sets help message.
     * @param string $help Help message.
     * @return yFormControl
     */
    public function setHelpHtml($help){
        $this->_help = $help;
        return $this;
    }
    /**
     * Sets help message.
     * @param string $help Help message.
     * @return yFormControl
     */
    public function setHelpText($help){
        $this->_help = new yTextNode($help);
        return $this;
    }
    /**
     * Gets help message.
     * @return string Help message.
     */
    public function getHelpHtml(){
        return $this->_help;
    }
    /**
     * Sets input key.
     * @param mixed $key Input key
     * @return yFormControl
     */
    public function setKey($key){
        $this->_key = $key;
        $this->_updateName();
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
        
    }
    public function setValue($value){
        $this->setAttribute('value', $value);
        return $this;
    }
    public function getValue(){
        return $this->getAttribute('value');
    }
    public function setRequired($required = true){
        $this->_isRequired = true;
        return $this;
    }
    public function isRequired(){
        return $this->_isRequired;
    }
    public function setLabelText($text){
        $this->_label->setText($text);
        return $this;
    }
    public function setLabelHtml($html){
        $this->_label->setText('');
        $this->_label->appendChild($html);
        return $this;
    }
    /**
     * @return yHtmlTag
     */
    public function getLabel(){
        $this->_label['for'] = $this->getAttribute('id');
        return $this->_label;
    }
    public function addValidator($validator, $options = array()){
        if (is_string($validator)){
            $this->_validators[$validator] = new $validator($options);
        }elseif (is_object($validator)){
// preconfigured
            $this->_validators[get_class($validator)] = $validator;
        }
    }
    public function removeValidator($validator){
        if (is_string($validator)){
            unset($this->_validators[$validator]);
        }elseif (is_object($validator)){
            unset($this->_validators[get_class($validator)]);
        }
    }
    public function addErrorMessage($message){
        $this->_errorMessages[] = $message;
    }
    public function getErrorMessages(){
        return $this->_errorMessages;
    }
    public function validate(){
        $this->_errorMessages = array();
        foreach ($this->_validators as $validator){
            // if string or array we can create it here...
            if (is_object($validator)){
                try{
                    $validator->validate($this);
                }catch(yValidatorException $e){
                    $this->addErrorMessage($e->getMessage());
                }
            }
        }
    }
    public function processKey(){
        if (isset($_POST[$this->_getFullName()])){
            if ($this->_key === null){
                $this->setValue($_POST[$this->_getFullName()]);
            }else{
                $this->setValue($_POST[$this->_getFullName()][$this->_key]);
            }
        }
        $this->validate();
    }
}

