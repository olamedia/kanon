<?php
/**
 * $Id$
 */
require_once dirname(__FILE__).'/controller.php';
class frontController extends controller{
	public static function startSession($domain, $expire = 360000) {
		ini_set("session.gc_maxlifetime", $expire);
		session_set_cookie_params($expire, '/', $domain);
		if (!session_start()){
			throw new Exception('session can\'t start');
		}
		// Reset the expiration time upon page load
		if (isset($_COOKIE[session_name()])){
			setcookie(session_name(), $_COOKIE[session_name()], time() + $expire, "/", $domain);
		}
		//echo 'Session start';
	}
	public static function _stripSlashesDeep(&$value){
		$value = is_array($value) ?
		array_map(array(self,'_stripSlashesDeep'), $value) :
		stripslashes($value);
		return $value;
	}

}
/*
 * $app = application::getInstance('/');
 * $app->run('/');
 */