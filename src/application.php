<?php
require_once dirname(__FILE__).'/frontController.php';
class application extends frontController{
	private static $_instance = null;
	public function __construct(){
		parent::__construct();
		header($_SERVER['SERVER_PROTOCOL']." 200 OK");
		header("Content-Type: text/html; charset=utf-8");
		@set_magic_quotes_runtime(false);
		$this->startSession('.'.$this->getDomainName());
		if (get_magic_quotes_gpc()){
			$this->_stripSlashesDeep($_GET);
			$this->_stripSlashesDeep($_POST);
		}
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
		if (isset($this->getRegistry()->plainCss)){
			$h .= '<style type="text/css">';
			$h .= $this->getRegistry()->plainCss;
			$h .= '</style>';
		}
		if (is_array($this->getRegistry()->javascriptIncludes)){
			foreach ($this->getRegistry()->javascriptIncludes as $url){
				$h .= '<script type="text/javascript" src="'.$url.'"></script>';
			}
		}
		$h .= '<link rel="shortcut icon" href="/favicon.ico" />';
		return $h;
	}
	public static function getInstance($controllerClassName = null){
		if (self::$_instance === null && $controllerClassName !== null){
			self::$_instance = new $controllerClassName();
		}
		return self::$_instance;
	}
	
}
function app(){
	return application::getInstance();
}