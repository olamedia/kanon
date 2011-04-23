<?php

class module{
    protected $_name = '';
    protected $_isLoaded = false;
    protected $_autoload = array();
    protected $_classes = array();
    public static function getInstance($name){
        return new self($name);
    }
    protected function __construct($name){
        $this->_name = $name;
    }
    public function __toString(){
        return $this->_name;
    }
    public function getName(){
        return $this->_name;
    }
    public function getModels(){
        $this->_load();
        return isset($this->_classes['model'])?array_keys($this->_classes['model']):array();
    }
    protected function _load(){
        if ($this->_isLoaded)
            return;
        static $types = array(
    'model', 'modelProperty',
    'controller',
    'controlSet', 'control'
        );
        $modulePath = kanon::getBasePath().'/modules/'.$this->_name;
        if (is_file($modulePath.'/module.php') && is_php($modulePath.'/module.php')){
            include $modulePath.'/module.php';
            $this->_autoload = $autoload;
            foreach ($this->_autoload as $class=>$filename){
                $classFilename = $modulePath.'/'.$filename;
                if (is_file($classFilename) && is_php($classFilename)){
                    require_once $classFilename;
                    $type = '';
                    $r = new ReflectionClass($class);
                    foreach ($types as $checkType){
                        if ($r->isSubclassOf($checkType))
                            $type = $checkType;
                    }
                    $this->_classes[$type][$class] = $filename;
                }
            }
        }
        $this->_isLoaded = true;
    }
}