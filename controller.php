<?php
class controller extends controllerPrototype{
	public function getRegistry(){
		return applicationRegistry::getInstance();
	}
	public function getApplication(){
		// @todo
	}
	public function app(){
		return $this->getApplication();
	}
	/**
	 * Set html page <title> 
	 * @param string $title
	 * @return controller
	 */
	public function setTitle($title){
		$this->getRegistry()->title = $title;
		return $this;
	}
	public function getTitle(){
		return $this->getRegistry()->title;
	}
}