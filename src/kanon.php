<?php
require_once dirname(__FILE__).'/application.php';
class kanon{
	public static function getBaseUri(){
		$requestUri = $_SERVER['REQUEST_URI'];
		$scriptUri = $_SERVER['SCRIPT_NAME'];
		$max = min(strlen($requestUri), strlen($scriptUri));
		$cmp = 0;
		for ($l = 1; $l <= $max; $l++){
			if (substr_compare($requestUri, $scriptUri, 0, $l, true) === 0){
				$cmp = $l;
			}
		}
		return substr($requestUri, 0, $cmp);
	}
	public static function run($applicationClass){//, $baseUrl = '/', $basePath = null
		$app = application::getInstance($applicationClass);
		//if ($basePath === null){
			$trace = debug_backtrace();
			$file = $trace[0]['file'];
			$basePath = dirname($file);
			$app->setBasePath($basePath);
		//}
		// ["REQUEST_URI"]=> string(40) "/kanon-framework/examples/hello_world/ok"
		// ["SCRIPT_NAME"]=> string(51) "/kanon-framework/examples/hello_world/bootstrap.php" 
		$baseUrl = kanon::getBaseUri();
		//echo 'Base PATH: '.$basePath."<br />";
		//echo 'Base URL: '.$baseUrl."<br />";
		$app->setBaseUri($baseUrl);
		//var_dump($app);
		$app->run($baseUrl);//$baseUrl
	}
}