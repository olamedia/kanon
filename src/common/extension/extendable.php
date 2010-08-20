<?php
class extendable{
	/**
	 * 
	 * @var array Array of callables
	 */
	protected $___methods = array();
	/**
	 * @param extension $extension
	 */
	public function extend($extension){
		$extension->setUpMethods($this);
		$extension->setUp($this);
	}
	/**
	 * Get class property
	 * @param string $name
	 */
	public function &___get($name){
		return $this->{$name};
	}
	/**
	 * Set class property
	 * @param string $name
	 * @param mixed $value
	 */
	public function ___set($name, &$value){
		return $this->{$name} = &$value;
	}
	/**
	 * Lookup external methods
	 * @param string $name
	 * @param array $arguments
	 */
	public function __call($name, $arguments){
		if (isset($this->___methods[$name])){
			$callable = $this->___methods[$name];
			if (is_callable($callable)){
				array_unshift($arguments, $this);
				return call_user_func_array($callable, $arguments);
			}
		}
                if ($name == '__destruct') return;
		throw new BadMethodCallException('Tried to call unknown method '.get_class($this).'::'.$name);
	}
}