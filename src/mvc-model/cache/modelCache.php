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
    protected static $_defaultEnabled = true;
    protected static $_prefetchOnCount = false;
    public static function enable($defaultEnabled = false){
        self::$_enabled = true;
        self::$_defaultEnabled = $defaultEnabled;
    }
    public static function isEnabled(){
        return self::$_enabled;
    }
    public static function isEnabledByDefault(){
        return self::$_defaultEnabled;
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
        if (!$resultSet->isCacheEnabled())
            return false;
        $cacheKey = md5($count?$resultSet->getCountSql():$resultSet->getSql());
        // try to use Memcache
        if ($memcache = self::getMemcache()){
            if ($results = $memcache->get($cacheKey)){
                return $results;
            }
        }
        if (isset(self::$_cache[$cacheKey])){
            return self::$_cache[$cacheKey];
        }
        return false;
    }
    /**
     * @param modelResultSet $resultSet 
     */
    public static function cache($resultSet, $results = null, $count=false){
        if (!$resultSet->isCacheEnabled())
            return;
        $lifetime = $resultSet->getCacheLifetime();
        if ($lifetime === null)
            $lifetime = 300;
        $cacheKey = md5($count?$resultSet->getCountSql():$resultSet->getSql());
        if ($results === null){
            $results = array();
            foreach ($resultSet as $result){
                $results[] = $result;
            }
        }

        // try to use Memcache
        if ($memcache = self::getMemcache()){
            if ($memcache->set($cacheKey, $results, false, $lifetime)){
                return;
            }
        }
        self::$_cache[$cacheKey] = $results;
    }
    protected static $_memcache = null;
    protected static $_memcacheEnabled = false;
    public static function getMemcache(){
        if (!self::$_memcacheEnabled)
            return null;
        if (self::$_memcache === null){
            if (class_exists('Memcache')){
                self::$_memcache = new Memcache;
                if (self::$_memcache->connect('localhost', 11211)){
                    return self::$_memcache;
                }
            }
            self::$_memcache = false;
        }
        return self::$_memcache;
    }
}
