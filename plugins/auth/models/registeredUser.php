<?php
class registeredUser extends model{
	protected $_properties = array(
		'id' => array(
			'class' => 'idProperty',
			'field' => 'id',
			'primaryKey' => true,
			'autoIncrement' => true,
		),
		'salt' => array(
			'class' => 'randomHashProperty',
			'field' => 'salt',
		),
		'password' => array(
			'class' => 'passwordHashProperty',
			'field' => 'password',
		),
	);
	protected $_actAs = array(
	'timestampable'
	);
}