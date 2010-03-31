<?php
/**
 * $Id$
 */
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
		application::getInstance();
	}
	public function app(){
		return $this->getApplication();
	}
	/**
	 * Set base path for /images/, /css/ etc
	 * @param string $path
	 */
	public function setBasePath($path){
		$this->getRegistry()->basePath = $path;
		return $this;
	}
	public function getBasePath($path = null){
		if ($path !== null){
			return realpath($this->getBasePath().$path).'/';
		}
		if ($this->getRegistry()->basePath === null){
			return realpath(dirname(__FILE__).$this->_relativeBasePath).'/';
		}else{
			return realpath($this->getRegistry()->basePath).'/';
		}
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
		$this->getRegistry()->cssIncludes = array_merge($this->getRegistry()->cssIncludes, array($uri));
	}
	public function css($cssString){
		$this->getRegistry()->plainCss .= $cssString;
	}
	public function js($jsString, $scriptSlot = 'default'){
		$this->getRegistry()->plainJs[$scriptSlot] = $this->getRegistry()->plainJs[$scriptSlot].$jsString;
	}
	public function requireJs($uri){
		$this->getRegistry()->javascriptIncludes = array_merge($this->getRegistry()->javascriptIncludes, array($uri));
	}
	public function getHeadContents(){
		$h = '<!DOCTYPE html>'; // html5
		$h .= '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
		$h .= '<title>'.$this->getTitle().'</title>';
		if (is_array($this->getRegistry()->cssIncludes)){
			foreach ($this->getRegistry()->cssIncludes as $url){
				$h .= '<link rel="stylesheet" type="text/css" href="'.$url.'" />';
			}
		}
		if (strlen($this->getRegistry()->plainCss)){
			$h .= '<style type="text/css">';
			$h .= $this->getRegistry()->plainCss;
			$h .= '</style>';
		}
		if (is_array($this->getRegistry()->javascriptIncludes)){
			foreach ($this->getRegistry()->javascriptIncludes as $url){
				$h .= '<script type="text/javascript" src="'.$url.'"></script>';
			}
		}
		if (is_array($this->getRegistry()->plainJs)){
			foreach ($this->getRegistry()->plainJs as $plainJs){
				$h .= '<script type="text/javascript">';
				$h .= $plainJs;
				$h .= '</script>';
			}
		}
		$h .= '<link rel="shortcut icon" href="/favicon.ico" />';
		return $h;
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