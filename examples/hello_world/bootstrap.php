<?php
/**
 * $Id$
 */
require_once '../../kanon-framework.php';
class helloWorld extends controller{
	public function index(){
		echo 'Hello world!';
	}
	/** !Route GET $firstName/$lastName */
	public function hello($firstName, $lastName){
		echo 'Hello '.$firstName.' '.$lastName.'!';
	}
}
kanon::run('helloWorld');