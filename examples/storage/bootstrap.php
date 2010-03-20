<?php
/**
 * $Id: bootstrap.php 19 2010-03-14 21:26:32Z olamedia $
 */
require_once '../../kanon-framework.php';
class storageExample extends controller{
	public function onConstruct(){
		kanon::getStorage('icons')
			->setPath('.')
			->setUrl($this->rel());
		kanon::getStorage('images')
			->setPath('./images')
			->setUrl($this->rel('images'));
	}
	public function index(){
		$storage = kanon::getStorage('icons');
		echo 'Path:'.$storage->getPath('favicon.ico').'<br />';
		echo 'Url:'.$storage->getUrl('favicon.ico').'<br />';
		$storage = kanon::getStorage('images');
		echo 'Path:'.$storage->getPath('test.jpg').'<br />';
		echo 'Url:'.$storage->getUrl('test.jpg').'<br />';
	}
}
kanon::run('storageExample');