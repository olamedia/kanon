<?php
require_once dirname(__FILE__).'/plugin.php';
class plugins{
	public static $_plugins = array();
	public static $_autoload = array();
	public static function getPluginsPath(){
		static $path = null;
		if ($path === null){
			$path = realpath(dirname(__FILE__).'/../../../plugins').'/';
		}
		return $path; 
	}
	public static function load($name){
		if (self::isLoaded($name)) return;
		$title = '';
		$description = '';
		$autoload = array();
		require_once(self::getPluginsPath().$name.'/plugin.php');
		$plugin = new plugin($name);
		$plugin->setTitle($title);
		$plugin->setDescription($description);
		self::$_plugins[$name] = $plugin;
		foreach ($autoload as $class => $filename){
			self::$_autoload[$class] = self::getPluginsPath().$name.'/'.$filename;
		}
	}
	public static function loadAll(){
		static $loaded = false;
		if ($loaded) return;
		$loaded = true;
		foreach (glob(self::getPluginsPath().'*') as $d){
			if (is_dir($d)){
				if (is_file($d.'/plugin.php')){
					self::load(basename($d));
				}
			}
		}
	}
	public static function isLoaded($name){
		return isset(self::$_plugins[$name]);
	}
	public static function autoload($class){
		if (isset(self::$_autoload[$class])){
			require_once self::$_autoload[$class];
			return true;
		}
		return false;
	}
}