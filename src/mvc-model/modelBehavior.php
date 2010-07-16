<?php
class modelBehavior{
	protected $_modelName = '';
	protected $_options = array();
	public function __construct($model, $options){
		$this->_modelName = get_class($model);
		$this->_options = $options;
	}
	public function setUp($model){
		
	}
	/*public function preSave();
	public function postSave();
	public function preInsert();
	public function postInsert();
	public function preUpdate();
	public function postUpdate();
	public function preDelete();
	public function postDelete();*/
}