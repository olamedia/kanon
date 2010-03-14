<?php
class frontController extends controller{
	public function startSession($domain, $expire = 360000) {
		session_set_cookie_params($expire, '/', $domain);
		@session_start();
		// Reset the expiration time upon page load
		if (isset($_COOKIE[session_name()])){
			setcookie(session_name(), $_COOKIE[session_name()], time() + $expire, "/", $domain);
		}
	}
}