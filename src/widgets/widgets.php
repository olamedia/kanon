<?php
/**
 * Description of widgets
 *
 * @author olamedia
 */
class widgets {
    protected static $_widgets = array();
    public static function register($id, $widget){
        self::set($id, $widget);
    }
    public static function set($id, $widget){
        self::$_widgets[$id] = $widget;
    }
    public static function get($id, $default = null){
        return isset(self::$_widgets[$id])?self::$_widgets[$id]:$default;
    }
    public static function dump(){
        var_dump(self::$_widgets);
    }
}

