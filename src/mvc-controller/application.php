<?php
/**
 * $Id$
 */
require_once dirname(__FILE__).'/frontController.php';
class application extends frontController{
	private static $_selfInstance = null;
	private static $_instance = null;
	/*public static function __construct(){
		parent::__construct();


		}*/
	public static function getInstance($controllerClassName = null){
		/*if (self::$_selfInstance === null){
			self::$_selfInstance = new self();

			}*/
		if (self::$_instance === null && $controllerClassName !== null){
			// autoload class and all dependencies before session start
			self::$_instance = new $controllerClassName();
		}else{
			return self::$_instance;
		}
		header($_SERVER['SERVER_PROTOCOL']." 200 OK");
		header("Content-Type: text/html; charset=utf-8");
		@set_magic_quotes_runtime(false);
		frontController::startSession('.'.uri::getDomainName());
		if (get_magic_quotes_gpc()){
			frontController::_stripSlashesDeep($_GET);
			frontController::_stripSlashesDeep($_POST);
		}
		return self::$_instance;
	}

}
function app(){
	return application::getInstance();
}