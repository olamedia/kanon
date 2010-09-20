<?php
/**
 * Description of magic
 *
 * @author olamedia
 */
final class magic{
    private static $_a = array('default'=>array('response', 'magic'));
    public static function append($magic, $value){
        if (!isset(self::$_a[$magic]) || !is_string(self::$_a[$magic]))
            self::$_a[$magic] = '';
        self::$_a[$magic] .= $value;
    }
    public static function set($magic, $value = null){
        if ($value === null){
            unset(self::$_a[$magic]);
        }else{
            self::$_a[$magic] = $value;
        }
    }
    public static function get($magic, $default = null){
        return isset(self::$_a[$magic])?self::$_a[$magic]:$default;
    }
    public static function call($magic, $default = null){
        if ($default === null){
            $default = self::get('default');
        }
        $args = func_get_args();
        array_shift($args);
        array_shift($args);
        $callable = self::get($magic, $default);
        if ($callable === $default){
            array_unshift($args, $magic);
        }
        unset($default);
        if ($callable !== null){
            if (is_callable($callable)){
                return call_user_func_array($callable, $args);
            }
            if (is_string($callable)){
                if (is_file($callable)){
                    return include $callable;
                } 
            }
        }
        ob_start();
        var_dump($magic);
        $dump = ob_get_contents();
        ob_end_clean();
        throw new Exception('No such magic: '.$dump);
    }
}

