<?php
require_once 'PHPUnit/Framework.php';
 
class controllerTest extends PHPUnit_Framework_TestCase{
	public function testCss(){
		$c = new controller();
		$this->assertEquals('', $c->getCss());
		$c->css('.class{text-align: center;}');
	}
}