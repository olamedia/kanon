<?php
/**
 * $Id$
 */
class application extends frontController{
	private static $_selfInstance = null;
	private static $_instance = null;
	public static function getInstance($controllerClassName = null){
		if (self::$_instance === null && $controllerClassName !== null){
			// autoload class and all dependencies before session start
			self::$_instance = new $controllerClassName();
		}else{
			return self::$_instance;
		}
		kanon::loadAllModules();
		header($_SERVER['SERVER_PROTOCOL']." 200 OK");
		header("Content-Type: text/html; charset=utf-8");
		@set_magic_quotes_runtime(false);
		frontController::startSession('.'.uri::getDomainName());
		if (get_magic_quotes_gpc()){
			frontController::_stripSlashesDeep($_GET);
			frontController::_stripSlashesDeep($_POST);
		}
		kanon::callDeferred(); // call all deferred by modules functions
		return self::$_instance;
	}
}