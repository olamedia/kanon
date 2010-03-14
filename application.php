<?php
class application extends frontController{
	private $_instance = null;
	private function __construct(){
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
	public function getInstance($controllerClassName){
		if ($this->_instance === null){
			$this->_instance = new $controllerClassName();
		}
		return $this->_instance;
	}
}