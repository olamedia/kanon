<?php
require_once 'PHPUnit/Framework.php';
require_once dirname(__FILE__).'/../controller.php';
class controllerTest extends PHPUnit_Framework_TestCase{
	protected function setUp(){
		$_SERVER['REQUEST_URI'] = '/';
	}
	public function testCss(){
		$c = new controller();
		$this->assertEquals('', $c->getCss());
		$c->css('.class{text-align: center;}');
		$this->assertEquals('<style type="text/css">.class{text-align: center;}</style>', $c->getCss());
	} 
}