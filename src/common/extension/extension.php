<?php
class extension{
	protected $___methods = array();
	/**
	 *
	 * @param extendable $extendable
	 */
	public function setUpMethods($extendable){
		$extendableMethods = $extendable->___get('___methods');
		foreach ($this->___methods as $method){
			$extendableMethods[$method] = array($this, $method);
		}
	}
	/**
	 *
	 * @param extendable $extendable
	 */
	public function setUp($extendable){
		
	}
}