<?php
require_once dirname(__FILE__).'/application.php';
class kanon{
	public static function run($applicationClass, $baseUrl = '/', $basePath = null){
		if ($basePath === null){
			$trace = debug_backtrace();
			$file = $trace[0]['file'];
			$basePath = dirname($file);
		}
		$app = application::getInstance($applicationClass);
		$app->run();
	}
}