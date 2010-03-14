<?php
require_once dirname(__FILE__).'/controllerPrototype.php';
require_once dirname(__FILE__).'/applicationRegistry.php';
class controller extends controllerPrototype{
	protected $_startTime = null;
	public function __construct(){
		$this->_startTime = microtime(true);
		parent::__construct();
	}
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
	public function appendToBreadcrumb($links = array()){
		if (count($links)){
			if (!is_array($this->getRegistry()->breadcrumb)){
				$this->getRegistry()->breadcrumb = array();
			}
			foreach ($links as $link){
				$this->getRegistry()->breadcrumb[] = $link;
			}
		}
		return $this;
	}
	public function getBreadcrumb(){
		if (!is_array($this->getRegistry()->breadcrumb)){
			$this->getRegistry()->breadcrumb = array();
		}
		return $this->getRegistry()->breadcrumb;
	}
	public function viewBreadcrumb(){
		echo implode(" → ", $this->getBreadcrumb());
	}
	public function getUser(){
		return isset($_SESSION['site_user'])?$_SESSION['site_user']:null;
	}
	public function getUserId(){
		return is_object($this->getUser())?$this->getUser()->id->getValue():0;
	}
	public function requireCss($uri){
		if (!is_array($this->getRegistry()->cssIncludes)){
			$this->getRegistry()->cssIncludes = array();
		}
		$this->getRegistry()->cssIncludes[] = $uri;
	}
	public function requireJs($uri){
		if (!is_array($this->getRegistry()->javascriptIncludes)){
			$this->getRegistry()->javascriptIncludes = array();
		}
		$this->getRegistry()->javascriptIncludes[] = $uri;
	}
	protected function &getDatabase($name = null){
		if ($name === null){
			return $this->getRegistry()->defaultDatabase;
		}
		if (!is_array($this->getRegistry()->databases)){
			$this->getRegistry()->databases = array();
		}
		return isset($this->getRegistry()->databases[$name])?$this->getRegistry()->databases[$name]:null;
	}
	
}