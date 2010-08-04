<?php
require_once dirname(__FILE__).'/../modelBehavior.php';
class versionableBehavior extends modelBehavior{
	public function setUp($model){
		$model->hasProperty('version', array(
		'class'=>'versionProperty',
		'field'=>'version',
		));
	}
}