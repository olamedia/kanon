<?php

/**
 * $Id$
 */
class application{// extends frontController{
    private static $_instance = null;
    public static function getInstance($controllerClassName = null){
        if (self::$_instance === null && $controllerClassName !== null){
            self::$_instance = new $controllerClassName();
            response::setStatus(200);
            response::setCharset('utf-8');
            // autoload all dependencies before session start
            kanon::loadAllModules();
            @set_magic_quotes_runtime(false);
            frontController::startSession('.'.uri::getDomainName());
            if (get_magic_quotes_gpc ()){
                frontController::_stripSlashesDeep($_GET);
                frontController::_stripSlashesDeep($_POST);
            }
            kanon::callDeferred(); // call all deferred by modules functions
        }
        return self::$_instance;
    }
    public static function run($applicationClass){
        $app = self::getInstance($applicationClass);
        $app->setBasePath(kanon::getBasePath());
        $baseUrl = kanon::getBaseUri();
        $app->setBaseUri($baseUrl);
        $app->run();
    }
}