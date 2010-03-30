<?php
require_once 'src/mvc-model/model.php';
require_once 'src/mvc-model/modelStorage.php';

class user extends model{
	protected $_fields = array(
	'host' => 'Host',
	'user' => 'User',
	'password' => 'Password',
	);
}
class helpCategory extends model{
	protected $_fields = array(
	'id' => 'help_category_id',
	'name' => 'name',
	'parentId' => 'parent_category_id',
	'url' => 'url'
	);
	protected $_classes = array(
	'id' => 'integerProperty',
	'name' => 'stringProperty',
	'parentId' => 'integerProperty',
	'url' => 'stringProperty'
	);
	protected $_primaryKey = array('id');
	protected $_foreignKeys = array(
	'parentId' => array('helpCategory', 'id')
	);
}
class helpTopic extends model{
	protected $_fields = array(
	'id' => 'help_topic_id',
	'name' => 'name',
	'categoryId' => 'help_category_id',
	'description' => 'description',
	'example' => 'example',
	'url' => 'url'
	);
	protected $_foreignKeys = array(
	'categoryId' => array('helpCategory', 'id')
	);
}
class helpRelation extends model{
	protected $_fields = array(
	'topicId' => 'help_topic_id',
	'keywordId' => 'help_keyword_id',
	);
	protected $_foreignKeys = array(
	'topicId' => array('helpTopic', 'id'),
	'keywordId' => array('helpKeyword', 'id')
	);
}
class helpKeyword extends model{
	protected $_fields = array(
	'id' => 'help_keyword_id',
	'name' => 'name',
	);
}
class groupImage extends model{
	protected $_fields = array(
	'id' => 'id',
	'groupId' => 'group_id',
	'filename' => 'filename',
	'createdAt' => 'created_at'
	);
	protected $_foreignKeys = array(
	'groupId' => array('group', 'id')
	);
}
/*$storage = kanon::getModelStorage()
->connect('mysql:host=localhost;port=3307;dbname=db', 'root', 'password')
;*/
$storage = kanon::getModelStorage()
->connect('mysql:unix_socket=/usr/KANOJO/mysqld.sock;dbname=mysql', 'root', '')
;
$storage->registerCollection('helpCategory', 'help_category');
$storage->registerCollection('helpTopic', 'help_topic');
$storage->registerCollection('helpRelation', 'help_relation');
$storage->registerCollection('helpKeyword', 'help_keyword');

//$storage->getConnection();
/*echo '<pre>';
 storageRegistry::dump();
 echo '</pre>';*/


$categories = helpCategory::getCollection();
echo $categories->getCreateSql();
//$topics = helpTopic::getCollection();
/*$categories = helpRelation::getCollection();*/
/*$keywords = helpKeyword::getCollection();
$list = $categories->select($topics, $categories->id->max())->where($keywords->name->is("'SELECT'"));
//var_dump($list);
echo '<div style="padding: 7px; border: solid 1px #eee; margin: 4px;">'.$list->getSql().'</div>';
echo '<pre>';
foreach ($list as $result){
	list($category, $topic, $sum) = $result;
	var_dump($category->toArray(), $topic->toArray(), $sum);
	echo '<hr />';
}/*
var_dump($list);
//var_dump($users->getFieldNames());
$list = $users->select()
->where($users->id->lt(5))
->having(userPost::getCollection()->login->like('4'))
->desc($users->id)
->groupBy($users->id)
->limit(3);
//$users->select()->orderBY()
echo '<pre>';
//var_dump($list);*/