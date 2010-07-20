<?php
class userEmail extends model{
	protected $_properties = array(
		'email' => array(
			'class' => 'stringProperty',
			'field' => 'email',
			'primaryKey' => true,
		),
		'userId' => array(
				'class' => 'idProperty',
				'field' => 'user_id',
				'foreignKey' => array(
					'registeredUser' => 'id', 
				),
		),
	);
	protected $_actAs = array(
	'timestampable'
	);
}