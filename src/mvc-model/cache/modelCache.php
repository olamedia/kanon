<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of modelCache
 *
 * @author olamedia
 */
class modelCache{
	protected static $_cache = array();
	protected static $_enabled = false;
	public static function enable(){
		self::$_enabled = true;
	}
	public static function isEnabled(){
		return self::$_enabled;
	}
	public static function disable(){
		self::$_enabled = false;
	}
	public static function getResult($resultSet){
		$cacheKey = md5($resultSet->getSql());
		if (isset(self::$_cache[$cacheKey])){
			return self::$_cache[$cacheKey];
		}
		return false;
	}
	/**
	 * @param modelResultSet $resultSet 
	 */
	public static function cache($resultSet){
		$cacheKey = md5($resultSet->getSql());
		$results = array();
		foreach ($resultSet as $result){
			$results[] = $result;
		}
		self::$_cache[$cacheKey] = $results;
	}
}
?>
