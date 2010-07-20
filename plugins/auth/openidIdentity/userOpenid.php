<?php
class userOpenid extends model{
	protected $_properties = array(
		'openid' => array(
			'class' => 'stringProperty',
			'field' => 'openid',
			'primaryKey' => true,
		),
		'userId' => array(
				'class' => 'idProperty',
				'field' => 'user_id',
				'foreignKeys' => array(
					'registeredUser' => 'id', 
				),
		),
	);
	protected $_actAs = array(
	'timestampable'
	);
}