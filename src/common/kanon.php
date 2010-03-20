<?php
/**
 * $Id$
 */
require_once dirname(__FILE__).'/application.php';
require_once dirname(__FILE__).'/fileStorage.php';
class kanon{
	/**
	 * Get named file storage
	 * @param string $storageName
	 * @return fileStorage
	 */
	public static function getStorage($storageName){
		return fileStorage::getStorage($storageName);
	}
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
	public static function run($applicationClass){
		$app = application::getInstance($applicationClass);
		$trace = debug_backtrace();
		$file = $trace[0]['file'];
		$basePath = dirname($file);
		$app->setBasePath($basePath);
		$baseUrl = kanon::getBaseUri();
		$app->setBaseUri($baseUrl);
		$app->run();
	}
}