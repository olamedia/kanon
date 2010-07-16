<?php
class versionableBehavior extends modelBehavior{
	public function setUp($model){
		$model->hasProperty('version', array(
		'class'=>'versionProperty',
		'field'=>'version',
		));
	}
}