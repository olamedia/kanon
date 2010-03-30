<?php
require_once dirname(__FILE__).'/../model.php';

/*$p1 = new stringProperty('p1');
$p2 = new stringProperty('p2');
$p2->setValue('value');
$p1->setValue($p2);

$p3 = clone $p1;

$p2->setValue('valueChanged');

echo '<pre>';
var_dump($p3);*/

class category extends model{
	protected $_classes = array(
	'id' => 'integerProperty',
	'title' => 'stringProperty',
	);
	public function save(){
		echo 'Saving ';
	}
}
class item extends model{
	protected $_classes = array(
	'id' => 'integerProperty',
	'categoryId' => 'integerProperty',
	'title' => 'stringProperty',
	);
}
$item = new item();
$item2 = new item();
$category = new category();
$category->id = 0;
$item->categoryId = $category->id;
$item2->categoryId = $category->id;


$category->id = 3;
$item->id = 10;
$item2->id = 100;
echo '<pre>';
echo $item->categoryId.' ';
echo $item2->categoryId.' ';
echo $item->id.' ';
echo $item2->id.' ';

