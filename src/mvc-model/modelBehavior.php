<?php
class modelBehavior extends extension{
	protected $_modelName = '';
	protected $_options = array();
	protected $_properties = array();
	public function __construct($model, $options){
		$this->_modelName = get_class($model);
		$this->_options = $options;
	}
	/**
	 * 
	 * @param model $model
	 */
	public function setUp($model){
		//echo ' setUp ';
		$properties = $model->___get('_properties');
		//var_dump($properties);
		foreach ($this->_properties as $propertyName => $propertyInfo){
			$properties[$propertyName] = $propertyInfo;
		}
		$model->___set('_properties', $properties);
		$properties = $model->___get('_properties');
		//var_dump($properties);
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