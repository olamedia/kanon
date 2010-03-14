<?php
require_once '../../kanon-framework.php';
class helloWorld extends controller{
	public function index(){
		echo 'Hello world!';
		echo '<hr />';
		var_dump($_SERVER);
	}
}
kanon::run('helloWorld');