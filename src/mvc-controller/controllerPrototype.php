<?php

/**
 * $Id$
 */
#require_once dirname(__FILE__) . '/../common/uri.php';

class controllerPrototype{
    /**
     *
     * @var ReflectionClass
     */
    protected $_me = null;
    protected $_parent = null;
    protected $_baseUri = null;
    protected $_relativeUri = null;
    protected $_childUri = '';
    protected $_action = '';
    protected $_type = 'html';
    protected $_actionControllers = array();
    protected $_options = array();
    protected $_useAssets = false;
    public function assets(){
        //var_dump($this->_relativeUri);
        $path = $this->_relativeUri->getPath();
        array_shift($path);
        $path = implode('/', $path);
        //echo $path;
        $filename = realpath(dirname($this->_me->getFileName()).'/../assets/'.$path);
        if (is_file($filename)){
            $mimes = array(
                'png'=>'image/png',
                'jpeg'=>'image/jpeg',
                'jpg'=>'image/jpeg',
                'gif'=>'image/gif',
            );
            $ext = end(explode('.', $filename));
            if (isset($mimes[$ext])){
                header('Content-type: '.$mimes[$ext]);
            }
            readfile($filename);
            exit;
        }
        $this->notFound();
    }
    public function asset($asset){
        $this->_useAssets = true;
        return $this->rel('assets/'.$asset);
    }
    public function _checkAccess($action, $method){
        return true;
    }
    public function _allow($action, $method){
        
    }
    public function _deny($action, $method){
        
    }
    public function isAjax(){
        return request::isAjax();
    }
    public function getHttpHeader($name, $default = null){
        return request::getHttpHeader($name, $default);
    }
    public function getServerParameter($name, $default = null){
        return isset($_SERVER[$name])?$_SERVER[$name]:$default;
    }
    public function __construct(){
        $this->_baseUri = uri::fromString('/');
        $this->_relativeUri = uri::fromRequestUri();
        $this->_me = new ReflectionClass(get_class($this));
    }
    protected function _view($filename, $parameters = array(), $uri = null){
        if ($uri === null)
            $uri = $this->rel();
        $view = new view();
        //echo $filename;
        $view->setFilename($filename);
        $view->setUri($uri);
        $view->show($parameters);
        //include($filename);
    }
    public function view($filename, $parameters, $uri = null){
        //echo dirname($this->_me->getFileName());
        $this->_view(realpath(dirname($this->_me->getFileName()).'/'.$filename), $parameters, $uri);
    }
    public function moduleView($moduleName, $filename, $parameters, $uri = null){
        $this->_view(kanon::getBasePath().'/modules/'.$moduleName.'/views/'.$filename, $parameters, $uri);
    }
    public function registerActionController($action, $controller){
        $this->_actionControllers[$action] = $controller;
    }
    /**
     * Executing before run() deprecated
     */
    public function onConstruct(){

    }
    /**
     * Executing before run()
     */
    public function onRun(){

    }
    /**
     * Set parent controller
     * @param controllerPrototype $parentController
     */
    public function setParent($parentController){
        $this->_parent = $parentController;
    }
    /**
     * Get parent controller
     */
    public function getParent(){
        return $this->_parent;
    }
    /**
     * Get current domain name without www
     */
    public function getDomainName(){
        return uri::getDomainName();
    }
    /**
     * Source for <link rel="canonical" href="" />
     */
    public function getCanonicalUrl(){
        return 'http://'.$this->getDomainName().''.$this->rel("$this->_relativeUri"); // quotes required to not overwrite _relativeUri
    }
    /**
     * Get current url excluding query
     */
    public function getCurrentUrl(){
        return 'http://'.$_SERVER['SERVER_NAME'].''.reset(explode("?", $_SERVER['REQUEST_URI']));
    }
    public function setOptions($options = array()){
        $this->_options = $options;
    }
    /**
     * Get $_SERVER['REQUEST_METHOD']
     */
    public function getHttpMethod(){
        return $_SERVER['REQUEST_METHOD'];
    }
    public function setBaseUri($uriString, $autoRel = true){
        $this->_baseUri = uri::fromString($uriString);
        if ($autoRel){
            $this->_relativeUri = uri::fromRequestUri();
            $this->_relativeUri->subtractBase($this->_baseUri);
        }
    }
    public function setRelativeUriFromBase($uriString){
        $baseUri = uri::fromString($uriString);
        $this->_relativeUri = uri::fromRequestUri();
        $this->_relativeUri->subtractBase($baseUri);
    }
    /**
     * Last method to run if another methods not found
     * @param string $action
     */
    public function _action($action){
        if ($action == $this->_widgetAction){
            $this->preloadWidgets();
            var_dump($this->_relativeUri);
        }
        return response::notFound();
    }
    public function preloadWidgets(){
        // preload widgets here
    }
    /**
     * Get url relative to this controller (combine with controller's base uri)
     * @param string|uri $relativeUri
     * @param boolean $includeAction
     * @return uri
     */
    public function rel($relativeUri = '', $includeAction = false){
        $relativeUri = strval($relativeUri); //if (is_object($relativeUri))
        if (is_string($relativeUri))
            $relativeUri = uri::fromString($relativeUri);
        $a = array();
        if ($includeAction)
            $a[] = $this->_action;
        if (!is_object($relativeUri)){
            throw new Exception('$relativeUri not an object');
        }
        $relativeUri->setPath(array_merge($this->_baseUri->getPath(), $a, $relativeUri->getPath()));
        return $relativeUri;
    }
    /**
     * Returns SSI Include instruction <!--# include virtual="$uri" -->
     * @param string $uri
     */
    public function ssi($uri){
        return '<!--# include virtual="'.$uri.'" -->';
    }
    /**
     * Redirect with custom HTTP code
     * @deprecated
     * @param string $message
     */
    protected function _redirect($url = null, $httpCode = 303){
        //echo '<a href="'.$url.'">'.$url.'</a>';
        //exit;
        if (isset($_GET['ref'])){
            if ($url == $_GET['ref']){
                //echo '<pre>';
                //var_dump(debug_backtrace());
                //	die('Redirect loop');
            }
        }
        //$url = $url.'?ref='.urlencode($url);
        $title = 'Переадресация';
        if (!preg_match("#^[a-z]+:#ims", $url)){
            if (!preg_match("#^/#ims", $url)){
                $url = $this->rel($url, true);
            }
            $url = 'http://'.$this->getDomainName().$url;
        }
        $wait = 0;
        header("Location: ".$url, true, $httpCode);
        //header($_SERVER['SERVER_PROTOCOL']." 303 See Other");
        header("Content-type: text/html; charset=UTF-8");
        echo '<html><head>';
        echo '<title>'.$title.'</title>';
        echo '</head><body onload="doRedirect()" bgcolor="#ffffff" text="#000000" link="#0000cc" vlink="#551a8b" alink="#ff0000">';
        echo '<noscript>';
        echo '<meta http-equiv="refresh" content="'.$wait.'; url=&#39;'.htmlspecialchars($url).'&#39;">';
        echo '</noscript>';
        echo '<p><font face="Arial, sans-serif">Подождите...</font></p>';
        echo '<p><font face="Arial, sans-serif">Если переадресация не сработала, перейдите по <a href="'.$url.'">ссылке</a> вручную.</font></p>';
        echo '<script type="text/javascript" language="javascript">';
        echo 'function doRedirect() {';
        if (!$wait)
            echo 'location.replace("'.$url.'");';
        echo '}';
        echo '</script>';
        echo '</body></html>';
        exit;
    }
    /**
     * Exit with HTTP 403 error code
     * @param string $message
     */
    public function forbidden(){
        response::forbidden();
    }
    /**
     * Exit with HTTP 404 error code
     * @param string $message
     */
    public function notFound(){
        response::notFound();
    }
    /**
     * Redirect with HTTP 301 "Moved Permanently" code
     * @param string $message
     */
    public function movedPermanently($url){
        response::movedPermanently($url);
    }
    /**
     * Redirect with HTTP 303 "See Other" code
     * @param string $message
     */
    public function seeOther($url){
        response::seeOther($url);
    }
    /**
     * Redirect with HTTP 303 "See Other" code
     * This is a recommended method to redirect after POST
     * @param string $message
     */
    public function redirect($url){
        response::redirect($url);
    }
    /**
     * Redirect to previous page
     */
    function back(){
        response::back();
    }
    /**
     * Deprecated
     * @param string $message
     */
    protected function _show403($message = ''){
        response::forbidden();
    }
    /**
     * Deprecated
     * @param string $message
     */
    protected function _show404($message = ''){
        response::notFound();
    }
    protected $_ignoreParentTemplate = false;
    public function ignoreParentTemplate($ignore = true){
        $this->_ignoreParentTemplate = $ignore;
    }
    protected function _header(){
        //if (!$this->_ignoreParentTemplate) {
        $parent = $this->getParent();
        if ($this->_ignoreParentTemplate){
            $parent = $parent->getParent();
        }
        if ($parent){
            $parent->_header();
            //if ($this->getParent())
            echo "\r\n".'<div class="'.get_class($this).'_wrapper">';
        }
        //}
        $this->header();
        echo "\r\n".'<div class="'.get_class($this).'_content">';
    }
    protected function _footer(){
        echo "\r\n".'</div>';
        $this->footer();
        $parent = $this->getParent();
        if ($this->_ignoreParentTemplate){
            $parent = $parent->getParent();
        }
        if ($parent){
            //if ($this->getParent())
            echo "\r\n".'</div>';
            $parent->_footer();
        }
    }
    public function header(){
        
    }
    public function _initIndex(){
        
    }
    public function index(){
        
    }
    public function footer(){
        
    }
    /**
     * Get arguments for method $methodName from $predefinedArgs, $_GET, $_POST arrays, default value or setting them null.
     * @param string $methodName
     * @param array $predefinedArgs
     * @return array
     */
    protected function _getArgs($methodName, $predefinedArgs = array()){
        $method = $this->_me->getMethod($methodName);
        $parameters = $method->getParameters();
        $args = array();
        foreach ($parameters as $p){
            $name = $p->getName();
            if (isset($predefinedArgs[$name])){
                $value = $predefinedArgs[$name];
            }elseif (isset($_GET[$name])){
                $value = $_GET[$name];
            }elseif (isset($_POST[$name])){
                $value = $_POST[$name];
            }else{
                $value = $p->isDefaultValueAvailable()?$p->getDefaultValue():null;
            }
            $args[] = $value; //$name
        }
        return $args;
    }
    /**
     * Make $this->_childUri from $this->_baseUri + $actions
     * @param array $actions
     * @return controllerPrototype
     */
    protected function _makeChildUri($actions){
        $childUri = clone $this->_baseUri;
        $path = $childUri->getPath();
        foreach ($actions as $action){
            $path[] = $action;
        }
        $childUri->setPath($path);
        $this->_childUri = strval($childUri);
        return $this;
    }
    /**
     * Forward action to another controller
     * @param string $controllerClass
     * @param string $relativePath
     * @param array $options
     */
    public function forwardTo($controllerClass, $relativePath = '', $options = array(), $methodToRun = null){
        $controller = new $controllerClass();
        $controller->setParent($this);
        $controller->setBaseUri($this->rel($relativePath), false);
        $controller->setRelativeUriFromBase($this->_baseUri);
        $controller->setOptions($options);
        if (method_exists($controller, 'customRun')){
            $controller->customRun();
        }else{
            $controller->run($methodToRun);
            if ($methodToRun !== null){
                return; // continue execution of parent controller
            }
        }
        exit;
    }
    /**
     * Run another controller
     * @param string $controllerClass
     * @param array $options
     */
    public function runController($controllerClass, $options = array()){
        $controller = new $controllerClass();
        $controller->setParent($this);
        $controller->setBaseUri($this->_childUri);
        $controller->setOptions($options);
        if (method_exists($controller, 'customRun')){
            $controller->customRun();
        }else{
            $controller->run();
        }
        exit;
    }
    protected $_widgetAction = 'widget';
    public function getWidgetAction(){
        return $this->_widgetAction;
    }
    /**
     * Run widget
     * @param string $widgetClass
     * @param array $options
     */
    public function widget($widgetId, $widgetClass, $options = array()){
        $widget = new $widgetClass($widgetId, $this, $options);
        if (method_exists($widget, 'customRun')){
            $widget->customRun();
        }else{
            $widget->run();
        }
    }
    /**
     * Get route from docComments if possible
     * @param $uri
     * @param string $prefix
     * @return array|false
     */
    protected function _getRouteMethod($uri, $prefix = '!Route'){
        $this->_me = new ReflectionClass(get_class($this));
        $methods = $this->_me->getMethods();
        $maxIdentWeight = 0;
        $maxLength = 0;
        $result = false;
        //var_dump($methods);
        foreach ($methods as $method){
            //var_dump($method);
            //$method = $this->_me->getMethod($methodName);
            if ($doc = $method->getDocComment()){
                // 1. expand comment
                $doc = trim(preg_replace("#/\*\*(.*)\*/#ims", "\\1", $doc));
                // 2. search for !Route
                $la = explode("\n", $doc);
                $routes = array();
                foreach ($la as $line){
                    if (($pos = strpos($line, $prefix)) !== false){
                        $routes[] = substr($line, $pos + strlen($prefix) + 1);
                    }
                }
                foreach ($routes as $route){
                    $httpMethod = reset(explode(" ", $route));
                    $route = trim(substr($route, strlen($httpMethod)));
                    if ($httpMethod == $this->getHttpMethod() || strtoupper($httpMethod) == 'ANY'){
                        //var_dump($route);
                        $routePath = explode("/", $route);
                        $path = $uri->getPath();
                        $identical = false;
                        $actions = array();
                        $args = array();
                        $identWeight = 0;
                        $length = count($routePath);
                        if (count($path) >= count($routePath)){
                            $identical = true;
                            foreach ($routePath as $rdir){
                                $dir = array_shift($path);
                                $rdir = array_shift($routePath);
                                $actions[] = $dir;
                                if (substr($rdir, 0, 1) != '$'){
                                    if ($dir != $rdir){
                                        $identical = false;
                                    }else{
                                        $identWeight++;
                                    }
                                }else{
                                    $argName = substr($rdir, 1);
                                    $args[$argName] = $dir;
                                }
                            }
                        }
                        $use = false;
                        if ($identWeight > $maxIdentWeight){ // more identical directories
                            $use = true;
                        }else{
                            if ($length > $maxLength){ // more variables
                                $use = true;
                            }
                        }
                        if ($identical && $use){
                            //
                            $maxIdentWeight = max($identWeight, $maxIdentWeight);
                            $maxLength = max($length, $maxLength);
                            $result = array($actions, $method->getName(), $args);
                        }
                        //var_dump($identical);
                    }
                }
            }
        }
        return $result;
    }
    protected function _call($method, $action, $args = array()){
        if ($this->_checkAccess($action, $method)){
            return call_user_func_array(array($this, $method), $args);
        }
        return false;
    }
    /**
     * Run controller - select methods and run them
     */
    public function run($methodToRun = null){
        kanon::setFinalController($this);
        $methodFound = false;
        $class = get_class($this);
        if (strlen($this->_relativeUri) > 1){ // longer than "/"
            if ($this->getCurrentUrl() != $this->getCanonicalUrl()){
                //echo $this->getCurrentUrl().'<br >';
                //echo $this->getCanonicalUrl().'<br >';
                // TODO FIX INCORRECT REDIRECTS (resulted in loops)
                //$this->movedPermanently($this->getCanonicalUrl());
            }
        }
        if ($action = $this->_relativeUri->getBasePath()){
            $this->_action = $action;
            if (strpos($action, '.') !== false){
                $this->_type = end(explode('.', $action));
                $action = substr($action, 0, strlen($action) - strlen($this->_type) - 1); // cut .html, .js etc
            }
        }
        $this->onConstruct();
        if (($action == 'assets') && $this->_useAssets){
            $this->assets();
            return;
        }
        if ($methodToRun !== null){
            if (method_exists($this, $methodToRun)){
                $this->_call($methodToRun, $action, $this->_getArgs($methodToRun));
                //call_user_func_array(array($this, $methodToRun), $this->_getArgs($methodToRun));
            }
            return;
        }else{
            if (extension_loaded('eAccelerator')){
                throw new Exception('eAccelerator strips phpdoc blocks. Turn it off.');
            }else{
                if (list($actions, $methodName, $pathArgs) = $this->_getRouteMethod($this->_relativeUri, '!RouteInit')){
                    $this->_makeChildUri($actions);
                    $methodFound = true;
                    if (method_exists($this, $methodName)){
                        $this->_call($methodName, $action, $this->_getArgs($methodName, $pathArgs));
                        //call_user_func_array(array($this, $methodName), $this->_getArgs($methodName, $pathArgs));
                    }
                }
                if (list($actions, $methodName, $pathArgs) = $this->_getRouteMethod($this->_relativeUri, '!Route')){
                    $this->_makeChildUri($actions);
                    $methodFound = true;
                    if (method_exists($this, $methodName)){
                        if ($this->getHttpMethod() == 'GET')
                            $this->_header();
                        $this->_call($methodName, $action, $this->_getArgs($methodName, $pathArgs));
                        //call_user_func_array(array($this, $methodName), $this->_getArgs($methodName, $pathArgs));
                        if ($this->getHttpMethod() == 'GET')
                            $this->_footer();
                        return;
                    }
                }
            }
            if (!$methodFound){
                if ($action){
                    $uc = ucfirst($action);
                    $this->_makeChildUri(array($this->_action));
                    $initFunction = 'init'.$uc;

                    if ($controller = kanon::getActionController(get_class($this), $action)){
                        $this->runController($controller);
                        return;
                    }

                    if (method_exists($this, $initFunction)){
                        $methodFound = true;
                        call_user_func_array(array($this, $initFunction), $this->_getArgs($initFunction));
                    }
                    $actionFunction = 'action'.$uc;
                    if (method_exists($this, $actionFunction)){
                        $methodFound = true;
                        call_user_func_array(array($this, $actionFunction), $this->_getArgs($actionFunction));
                    }
                    $showFunction = 'show'.$uc;
                    if (method_exists($this, $showFunction)){
                        $methodFound = true;
                        $this->_header();
                        call_user_func_array(array($this, $showFunction), $this->_getArgs($showFunction));
                        $this->_footer();
                    }
                    if (!$methodFound){
                        return $this->_action($action);
                    }
                }else{
                    if (method_exists($this, 'customIndex')){
                        $this->customIndex();
                    }else{
                        //$this->_initIndex();
                        call_user_func_array(array($this, '_initIndex'), $this->_getArgs('_initIndex'));
                        $this->_header();
                        call_user_func_array(array($this, 'index'), $this->_getArgs('index'));
                        //$this->index();
                        $this->_footer();
                    }
                }
            }
        }
    }
}