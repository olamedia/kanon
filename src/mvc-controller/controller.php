<?php

/**
 * $Id$
 */
#require_once dirname(__FILE__).'/controllerPrototype.php';
#require_once dirname(__FILE__).'/applicationRegistry.php';
class controller extends controllerPrototype{
    protected $_startTime = null;
    public function __construct(){
        $this->_startTime = microtime(true);
        parent::__construct();
    }
    protected static $_pageInstance = null;
    public static function getPageInstance(){
        if (self::$_pageInstance === null){
            self::$_pageInstance = new yPage();
        }
        return self::$_pageInstance;
    }
    /**
     * Gets yPage instance
     * @return yPage
     */
    public function getPage(){
        return self::getPageInstance();
    }
    public function getTable($tableName){ // Compatibility with zenMysql2 ORM
        $storage = kanon::getModelStorage();
        foreach ($storage->getRegistry()->modelSettings as $modelName=>$settings){
            if ($settings['table'] == $tableName){
                return kanon::getCollection($modelName);
            }
        }
        return false;
    }
    public function getRegistry(){
        return applicationRegistry::getInstance();
    }
    public function getApplication(){
        application::getInstance();
    }
    public function app(){
        return $this->getApplication();
    }
    public function registerMenuItem($title, $action){
        $this->getRegistry()->menu->{get_class($this)}[$title] = $action;
    }
    public function getMenu(){
        return $this->getRegistry()->menu->{get_class($this)};
    }
    /**
     * Set base path for /images/, /css/ etc
     * @param string $path
     */
    public function setBasePath($path){
        $this->getRegistry()->basePath = $path;
        return $this;
    }
    public function getBasePath($path = null){
        if ($path !== null){
            return realpath($this->getBasePath().$path).'/';
        }
        if ($this->getRegistry()->basePath === null){
            return realpath(dirname(__FILE__).$this->_relativeBasePath).'/';
        }else{
            return realpath($this->getRegistry()->basePath).'/';
        }
    }
    /**
     * Set html page <title>
     * @param string $title
     * @return controller
     */
    public function setTitle($title){
        $this->getPage()->setTitle($title);
        return $this;
    }
    public function getTitle(){
        return $this->getPage()->getTitle();
    }
    public function appendToBreadcrumb($links = array()){
        if (is_array($links)){
            if (count($links)){
                foreach ($links as $link){
                    $this->getRegistry()->breadcrumb[] = $link;
                }
            }
        }else{
            $this->getRegistry()->breadcrumb[] = $links;
        }
        return $this;
    }
    public function getBreadcrumb(){
        return $this->getRegistry()->breadcrumb->toArray();
    }
    public function viewBreadcrumb(){
        if (count($this->getBreadcrumb()) > 1){
            echo '<div class="nav app_breadcrumb">'.implode(" → ", $this->getBreadcrumb()).'</div>';
        }
    }
    public function getUser(){
        static $user;
        $user = isset($_SESSION['site_user'])?$_SESSION['site_user']:null;
        return null; //$user;
    }
    public function getUserId(){
        return 0; //is_object($this->getUser())?$this->getUser()->id->getValue():0;
    }
    public function requireCss($uri){
        $this->getPage()->requireCss($uri);
    }
    public function css($cssString){
        $this->getPage()->css($cssString);
    }
    public function robots($text){
        $this->getPage()->{$text}();
    }
    public function js($jsString){
        $this->getPage()->js($jsString);
    }
    public function requireJs($uri){//, $alias = 'default', $require = ''
        $this->getPage()->requireJs($uri);
    }
    public function head(){
        echo $this->getPage()->getHtmlStart();
    }
    public function getHeadContents(){
        return $this->getPage()->getHead();
    }
    protected function &getDatabase($name = null){
        if ($name === null){
            return $this->getRegistry()->defaultDatabase;
        }
        if (!is_array($this->getRegistry()->databases)){
            $this->getRegistry()->databases = array();
        }
        return isset($this->getRegistry()->databases[$name])?$this->getRegistry()->databases[$name]:null;
    }
    public function viewPages($pagesCount, $selectedPage){
        if ($pagesCount < 2)
            return;
        echo '<div class="pages">';
        $la = array();
        if ($selectedPage > 1){
            $la[] = '<a href="?page='.($selectedPage - 1).'" class="prev">предыдущая</a>';
        }
        $p = 0;
        while ($p <= $pagesCount){
            $p++;
            //echo $p.' ';
            if ($p > 0 && $p <= $pagesCount){
                //echo ' ok';
                if ($p == $selectedPage){
                    $la[] = '<b>'.$p.'</b>';
                }else{
                    $la[] = '<a href="?page='.$p.'">'.$p.'</a>';
                }
            }
        }
        if ($selectedPage < $pagesCount){
            $la[] = '<a href="?page='.($selectedPage + 1).'" class="next">следующая</a>';
        }
        echo implode(' ', $la);
        echo '</div>';
    }
}