<?php

/**
 * Description of request
 *
 * @author olamedia
 */
class request{
    public static function isCli(){
        return (PHP_SAPI == 'cli');
    }
    public static function isAjax(){
        return 'XMLHttpRequest' == self::getHttpHeader('X-Requested-With');
    }
    public static function getMethod(){
        return $_SERVER['REQUEST_METHOD'];
    }
    public static function getHttpHeader($name, $default = null){
        return self::getServerParameter('HTTP_'.strtoupper(strtr($name, '-', '_')), $default);
    }
    public static function getServerParameter($name, $default = null){
        return isset($_SERVER[$name])?$_SERVER[$name]:$default;
    }
}

