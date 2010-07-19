<?php
class commentPrototype extends model{
	protected $_properties = array(
		'id' => array(
			'class' => 'idProperty',
			'field' => 'id',
			'primaryKey' => true,
			'autoIncrement' => true,
		),
		'threadId' => array(
			'class' => 'idProperty',
			'field' => 'thread_id',
		),
		'text' => array(
			'class' => 'stringProperty',
			'field' => 'text',
		),
	);
	protected $_actAs = array('timestampable');
}