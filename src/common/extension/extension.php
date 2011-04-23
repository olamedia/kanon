<?php
class extension{
	protected $___methods = array();
	/**
	 * Extend $extendable with new methods
	 * @param extendable $extendable
	 */
	public function setUpMethods($extendable){
		$extendableMethods = $extendable->___get('___methods');
		foreach ($this->___methods as $method){
			$extendableMethods[$method] = array($this, $method);
		}
		$extendable->___set('___methods', $extendableMethods);
	}
	/**
	 * 
	 * @param extendable $extendable
	 */
	public function setUp($extendable){
		if (!($extendable instanceof extendable)){
			throw new InvalidArgumentException('');
		}
	}
}