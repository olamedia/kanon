<?php
require_once 'PHPUnit/Framework.php';
require_once '../../../kanon-framework.php';
class extensionSample extends extension{
	public $___methods = array('method');
	public function method(){
		
	}
	/**
	 * 
	 * @param extendable $extendable
	 */
	public function setUp($extendable){
		$extendable->___set('x', $a = 1);
	}
}
class extensionTest extends PHPUnit_Framework_TestCase{
	public function testExtension(){
		$extendable = new extendable();
		$this->assertNull($extendable->___get('x'));
		
		$extension = new extensionSample();
		$extendable->extend($extension);
		$reflection = new ReflectionObject($extendable);
		$method = $reflection->getMethod('method');
		$this->assertTrue(is_object($method));
		$this->assertEquals(1, $extendable->___get('x'));
	}
}
