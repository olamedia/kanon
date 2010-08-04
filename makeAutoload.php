<?php
require_once dirname(__FILE__).'/src/common/functions/is_php.php';

class autoloadGenerator{
	protected $_classes = array();
	protected $_functions = array();
	protected $_declaredClasses = array();
	protected $_definedFunction = array();
	public function create($filename){
		$this->_declaredClasses = get_declared_classes();
		$this->_definedFunction = get_defined_functions();
		$this->lookup(dirname(__FILE__).'/');
	}
	public function lookFile($f){
		if (is_php(f)){
			require_once $f;
			$declaredClasses = get_declared_classes();
			$definedFunction = get_defined_functions();
			$newClasses = array_diff($this->_declaredClasses, $declaredClasses);
			$newFunctions = array_diff($this->_definedFunction, $definedFunction);
			var_dump($newClasses);
			var_dump($newFunctions);
		}
	}
	public function lookup($dir){
		foreach (glob($dir.'*') as $f){
			if (is_dir($f)){
				$this->lookup($f);
			}elseif (is_file($f)){
				$this->lookFile($f);
			}
		}
	}
}