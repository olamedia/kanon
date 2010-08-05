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
	protected static $_prefetchOnCount = true;
	public static function enable(){
		self::$_enabled = true;
	}
	public static function isEnabled(){
		return self::$_enabled;
	}
	public static function prefetchOnCount(){
		return self::$_prefetchOnCount;
	}
	public static function enablePrefetchOnCount(){
		self::$_prefetchOnCount = true;
	}
	public static function disablePrefetchOnCount(){
		self::$_prefetchOnCount = false;
	}
	public static function disable(){
		self::$_enabled = false;
	}
	public static function getResult($resultSet, $count=false){
		$cacheKey = md5($count?$resultSet->getCountSql():$resultSet->getSql());
		if (isset(self::$_cache[$cacheKey])){
			return self::$_cache[$cacheKey];
		}
		return false;
	}
	/**
	 * @param modelResultSet $resultSet 
	 */
	public static function cache($resultSet, $results = null, $count=false){
		$cacheKey = md5($count?$resultSet->getCountSql():$resultSet->getSql());
		if ($results===null){
			$results = array();
			foreach ($resultSet as $result){
				$results[] = $result;
			}
		}
		self::$_cache[$cacheKey] = $results;
	}
}
?>
