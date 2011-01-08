<?php
/**
 * $Id$
 */
#require_once dirname(__FILE__).'/plugin/plugins.php';
#require_once dirname(__FILE__).'/handlers/kanonErrorHandler.php';
#require_once dirname(__FILE__).'/handlers/kanonExceptionHandler.php';
#require_once dirname(__FILE__).'/../mvc-controller/application.php';
#require_once dirname(__FILE__).'/../mvc-model/modelCollection.php';
#require_once dirname(__FILE__).'/fileStorage.php';
#require_once dirname(__FILE__).'/keep.func.php';

class kanon{
	private static $_uniqueId = 0;
	private static $_uniqueIdMap = array();
	private static $_basePath = null;
	private static $_fileStorages = array();
	private static $_loadedModules = array();
        private static $_moduleClass = array();
	private static $_autoload = array();
	private static $_actionControllers = array();
	private static $_menu = array();
	private static $_deferredFunctions = array();
	private static $_finalController = array();
	private static $_preferredThemes = array();
	public static function app(){
		return application::getInstance();
	}
	public static function setTheme(){
		$args = func_get_args();
		foreach ($args as $arg){
			if (is_array($arg)){
				foreach ($arg as $theme){
					self::$_preferredThemes[] = $theme;
				}
			}else{
				self::$_preferredThemes[] = $arg;
			}
		}
	}
	public static function getThemedViewFilename($filename){
		$basePath = self::getBasePath();
		$themesPath = $basePath.'/themes/';
		$modulesPath = $basePath.'/modules/';
		if (substr($filename, 0, strlen($modulesPath))!=$modulesPath){
			return $filename; // not in modules
		}
		$rel = substr($filename, strlen($modulesPath), strlen($filename)-strlen($modulesPath));
		//echo $rel;
		$a = explode('/', $rel);
		$moduleName = array_shift($a);
		//echo ' module='.$moduleName;
		//echo array_shift($a);
		if ('views'==array_shift($a)){
			$rel = $moduleName.'/'.implode('/', $a);
		}
		foreach (self::$_preferredThemes as $themeName){
			$themedFilename = $themesPath.$themeName.'/'.$rel;
			//echo ' themed='.$themedFilename;
			if (is_file($themedFilename)){
				return $themedFilename;
			}
		}
		return $filename;
	}
	public static function setFinalController($controller){
		self::$_finalController[] = $controller;
	}
	public static function getFinalController(){
		return end(self::$_finalController);
	}
	public static function getFinalControllers(){
		return self::$_finalController;
	}
	public static function onShutdown(){
		keep($_SESSION); // do not destroy models
	}
	public static function defer($function){
		self::$_deferredFunctions[] = $function;
	}
	public static function callDeferred(){
		foreach (self::$_deferredFunctions as $f){
			call_user_func($f);
		}
	}
	public static function registerAutoload($autoload, $dirname = null){
		foreach ($autoload as $class => $f){
			if ($dirname!==null){
				$f = $dirname.$f;
			}
			self::$_autoload[$class] = $f;
		}
	}
	public static function autoload($class){
                $time = microtime(true);
		if (isset(self::$_autoload[$class])){
			require_once self::$_autoload[$class];
                        profiler::getInstance()->addSql("autoload($class)", $time);
			return true;
		}
                profiler::getInstance()->addSql("autoload($class)", $time);
		return false;
	}
	/**
	 * Get named file storage
	 * @param string $storageName
	 * @return fileStorage
	 */
	public static function getUniqueId($uniqueString = null){
		if ($uniqueString!==null&&isset(self::$_uniqueIdMap[$uniqueString])){
			return self::$_uniqueIdMap[$uniqueString];
		}
		$id = self::$_uniqueId;
		$id = strval(base_convert($id, 10, 26));
		$shift = ord("a")-ord("0");
		for ($i = 0; $i<strlen($id); $i++){
			$c = $id{$i};
			if (ord($c)<ord("a")){
				$id{$i} = chr(ord($c)+$shift);
			}else{
				$id{$i} = chr(ord($c)+10);
			}
		}
		self::$_uniqueId++;
		if ($uniqueString!==null){
			self::$_uniqueIdMap[$uniqueString] = $id.'_';
		}
		return $id.'_';
	}
	public static function getStorage($name){
		return self::$_fileStorages[$name];
	}
	public static function setStorage($name, $storage){
		self::$_fileStorages[$name] = $storage;
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
		return modelCollection::getInstance($modelName);
	}
	public static function getBaseUri(){
		$requestUri = $_SERVER['REQUEST_URI'];
		$scriptUri = $_SERVER['SCRIPT_NAME'];
                if (preg_match("#^(.*)/[^/]+\.php$#imsu",$scriptUri,$subs)){
                    $scriptUri = $subs[1];
                }
		$max = min(strlen($requestUri), strlen($scriptUri));
		$cmp = 0;
		for ($l = 1; $l<=$max; $l++){
			if (substr_compare($requestUri, $scriptUri, 0, $l, true)===0){
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
	public static function getModules(){
		self::loadAllModules();
		return array_keys(self::$_loadedModules);
	}
	public static function getModuleClasses($module){
		return self::$_moduleClass[$module];
	}
	public static function loadModule($module){
		if (isset(self::$_loadedModules[$module]))
			return true;
		$modulePath = self::getBasePath().'/modules/'.$module.'/';
		$moduleFile = $modulePath.'module.php';
		if (is_file($moduleFile)){
			self::$_loadedModules[$module] = true;
			$autoload = array();
			require_once $moduleFile;
			if (count($autoload)){
				foreach ($autoload as $k => $v){
                                        self::$_moduleClass[$module][$k] = $k;
					self::$_autoload[$k] = $modulePath.$v;
				}
			}
			return true;
		}
		return false;
	}
	public static function loadAllModules(){
		static $loaded = false;
		if ($loaded)
			return;
		$loaded = true;
		$path = self::getBasePath();
		foreach (glob($path.'/modules/*') as $d){
			if (is_dir($d)){
				if (is_file($d.'/module.php')){
					self::loadModule(basename($d));
				}
			}
		}
	}
	public static function getBasePath(){
		if (self::$_basePath===null){
			$trace = debug_backtrace();
			//var_dump($trace);
			$last = end($trace);
			$file = $last['file']; //[1]
			self::$_basePath = dirname($file);
		}
		return self::$_basePath;
	}
	public static function run($applicationClass){
            application::run($applicationClass);
	}
	public static function registerActionController($controller, $action, $controller2){
		self::$_actionControllers[$controller][$action] = $controller2;
	}
	public static function registerMenuITem($controller, $title, $rel){
		self::$_menu[$controller][$title] = $rel;
	}
	public static function getActionController($controller, $action){
		if (isset(self::$_actionControllers[$controller][$action])){
			return self::$_actionControllers[$controller][$action];
		}
		return false;
	}
	public static function getActionControllers($controller){
		if (isset(self::$_actionControllers[$controller])){
			return self::$_actionControllers[$controller];
		}
		return false;
	}
	public static function setActionControllers($controller, $actionControllers){
        	self::$_actionControllers[$controller] = $actionControllers;
	}
	public static function getMenu($controller){
		if (isset(self::$_menu[$controller])){
			return self::$_menu[$controller];
		}
		return false;
	}
}