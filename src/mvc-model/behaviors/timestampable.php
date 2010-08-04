<?php
require_once dirname(__FILE__).'/../modelBehavior.php';
class timestampable extends modelBehavior{
	protected $_properties = array(
		'createdAt' => array(
			'class'=>'creationTimestampProperty',
			'field'=>'created_at',
			),
		'modifiedAt' => array(
			'class'=>'modificationTimestampProperty',
			'field'=>'modified_at',
			),
	);
}