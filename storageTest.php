<?php
require_once 'src/mvc-model/model.php';
require_once 'src/mvc-model/modelStorage.php';
$storage = kanon::getModelStorage()
	->connect('mysql:host=localhost;port=3307;dbname=db', 'root', 'password')
	;
$storage = kanon::getModelStorage()
	->connect('mysql:unix_socket=/usr/KANOJO/mysqld.sock;dbname=mysql', 'root', 'ghbrjkbcnfvytnflvbyfvlf')
	;
$storage->registerCollection('user', 'users');
$storage->getConnection();
/*echo '<pre>';
storageRegistry::dump();
echo '</pre>';*/

class user extends model{
	protected $_fields = array(
	'id' => 'id',
	'groupId' => 'group_id',
	'login' => 'login',
	'createdAt' => 'created_at'
	);
	protected $_foreignKeys = array(
	'groupId' => array('group', 'id')
	);
}
class group extends model{
	protected $_fields = array(
	'id' => 'id',
	'login' => 'login',
	'createdAt' => 'created_at'
	);
}
$users = user::getCollection();
var_dump($users->getFieldNames());
$list = $users->select()
	->where($users->id->lt(5))
	->having($users->login->like('4'))
	->desc($users->id)
	->groupBy($users->id)
	->limit(3);
echo '<div style="padding: 7px; border: solid 1px #eee; margin: 4px;">'.$list->getSql().'</div>';
echo '<pre>';
var_dump($list);