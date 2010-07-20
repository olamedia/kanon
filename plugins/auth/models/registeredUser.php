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
			'primaryKey' => true,
			'autoIncrement' => true,
		),
		'password' => array(
			'class' => 'passwordHashProperty',
			'field' => 'password',
			'primaryKey' => true,
			'autoIncrement' => true,
		),
	);
	protected $_actAs = array(
	'timestampable'
	);
}