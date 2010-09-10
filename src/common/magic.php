<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of magic
 *
 * @author olamedia
 */
final class magic{
    private static $_a = array();
    public static function set($name, $value){
        self::$_a[$name] = $value;
    }
    public static function get($name){
        return isset(self::$_a[$name])?self::$_a[$name]:false;
    }
    public static function call($name){
        $args = func_get_args();
        array_shift($args);
        $callable = self::get($name);
        if ($callable){
            if (is_callable($callable)){
                return call_user_func_array($callable, $args);
            }
            if (is_string($callable)){
                if (is_file($callable)){
                    return include $callable;
                }
            }
        }
        throw new Exception('No such magic');
    }
}

