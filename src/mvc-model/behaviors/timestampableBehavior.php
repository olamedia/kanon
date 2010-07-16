<?php
class timestampableBehavior extends modelBehavior{
	public function setUp($model){
		$model->hasProperty('createdAt', array(
		'class'=>'creationTimestampProperty',
		'field'=>'created_at',
		));
		$model->hasProperty('modifiedAt', array(
		'class'=>'modificationTimestampProperty',
		'field'=>'modified_at',
		));
	}
}