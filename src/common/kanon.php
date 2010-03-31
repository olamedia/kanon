<?php
/**
 * $Id$
 */
require_once dirname(__FILE__).'/../mvc-controller/application.php';
require_once dirname(__FILE__).'/../mvc-model/modelCollection.php';
require_once dirname(__FILE__).'/fileStorage.php';
class kanon{
	private static $_uniqueId = 0;
	private static $_basePath = null;
	private static $_loadedModules = array();
	private static $_autoload = array();
	public static function autoload($class){
		if (isset(self::$_autoload[$class])){
			require_once self::$_autoload[$class];
		}
	}
	/**
	 * Get named file storage
	 * @param string $storageName
	 * @return fileStorage
	 */
	public static function getUniqueId(){
		$id = self::$_uniqueId;
		$id = strval(base_convert($id, 10, 26));
		$shift = ord("a") - ord("0");
		for ($i = 0; $i < strlen($id); $i++){
			$c = $id{$i};
			if (ord($c) < ord("a")){
				$id{$i} = chr(ord($c)+$shift);
			}else{
				$id{$i} = chr(ord($c)+10);
			}
		}
		self::$_uniqueId++;
		return $id;
	}
	public static function getStorage($storageName = 'default'){
		return fileStorage::getStorage($storageName);
	}
	/**
	 *
	 * @param string $storageName
	 * @return modelStorage
	 */
	public static function getModelStorage($storageName = 'default'){
		return modelStorage::getInstance($storageName);
	}
	public static function getCollection($modelName){
		return modelCollection::getCollection($modelName);
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
	/**
	 * Redirect with custom HTTP code
	 */
	public static function redirect($url = null, $httpCode = 303){
		header("Location: ".$url, true, $httpCode);
		header("Content-type: text/html; charset=UTF-8");
		echo '<body onload="r()">';
		echo '<noscript>';
		echo '<meta http-equiv="refresh" content="0; url=&#39;'.htmlspecialchars($url).'&#39;">';
		echo '</noscript>';
		echo '<script type="text/javascript" language="javascript">';
		echo 'function r(){location.replace("'.$url.'");}';
		echo '</script>';
		echo '</body>';
		exit;
	}
	public static function loadModule($module){
		if (isset(self::$_loadedModules[$module])) return true;
		$modulePath = self::getBasePath().'/modules/'.$module.'/';
		$moduleFile = $modulePath.'module.php';
		if (is_file($moduleFile)){
			self::$_loadedModules[$module] = true;
			$autoload = array();
			require_once $moduleFile;
			if (count($autoload)){
				foreach ($autoload as $k => $v){
					self::$_autoload[$k] = $modulePath.$v;
				}
			}
			return true;
		}
		return false;
	}
	public static function getBasePath(){
		if (self::$_basePath === null){
			$trace = debug_backtrace();
			$file = $trace[1]['file'];
			self::$_basePath = dirname($file);
		}
		return self::$_basePath;
	}
	public static function run($applicationClass){
		//spl_autoload_register(array(self, 'autoload'));
		$app = application::getInstance($applicationClass);
		$app->setBasePath(self::getBasePath());
		$baseUrl = kanon::getBaseUri();
		$app->setBaseUri($baseUrl);
		$app->run();
	}
}
function __autoload($name) {
	kanon::autoload($name);
}