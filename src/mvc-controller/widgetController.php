<?php

/**
 * Description of widgetController
 *
 * @author olamedia
 */
class widgetController extends controller{
    protected $_widgetUri = null;
    protected $_widgetId = null;
    protected $_block = null;
    public function setBlock($block){
        $this->_block = $block;
    }
    public function actionRemove(){
        if ($this->_block !== null){
            $this->_block->delete();
            $this->back();
        }
    }
    public function initSettings(){
        if ($this->_block !== null){
            if (request::getMethod() === 'POST'){
                if (isset($_POST['padding'])){
                    $this->_block->setOption('padding', intval($_POST['padding']));
                }
                $this->back();
            }
        }
    }
    public function showSettings(){
        if ($this->_block !== null){
            echo '<form method="post" action="'.$this->arel().'" success="'.$this->rel('').'">';
            echo '<label for="s-padding">Внутренний отступ</label>';
            echo '<input id="s-padding" type="text" name="padding" style="width: 15px;" value="'.$this->_block->getOption('padding', 0).'" />';
            echo '<div><input type="submit" value="Сохранить" /></div>';
            echo '</form>';
        }
    }
    /**
     *
     * @param integer $widgetId Constant id for widget settings over site
     * @param controller $parentController
     * @param array $options
     */
    public function __construct($widgetId, $parentController, $options = array()){
        $this->_baseUri = uri::fromString('/');
        $this->_relativeUri = uri::fromRequestUri();
        $this->_me = new ReflectionClass(get_class($this));
        $this->setBaseUri($parentController->_childUri);
        $this->setOptions($options);
        $this->setWidgetUri($parentController->rel($parentController->getWidgetAction().'/'.$widgetId));
        $this->setWidgetId($widgetId);
        parent::__construct();
    }
    protected $_widgetMode = false;
    public function setWidgetMode($widgetMode = true){
        $this->_widgetMode = $widgetMode;
        if ($widgetMode){
            $this->setBaseUri($this->_widgetUri);
        }
    }
    public function setWidgetId($widgetId){
        $this->_widgetId = $widgetId;
    }
    public function getWidgetId(){
        return $this->_widgetId;
    }
    public function setWidgetUri($uri){
        $this->_widgetUri = $uri;
    }
    public function wrel($relativeUri = ''){
        $relativeUri = strval($relativeUri);
        if (is_string($relativeUri))
            $relativeUri = uri::fromString($relativeUri);
        if (!is_object($relativeUri)){
            throw new Exception('$relativeUri not an object');
        }
        $relativeUri->setPath(array_merge($this->_widgetUri->getPath(), $relativeUri->getPath()));
        return $relativeUri;
    }
    public function toolbar(){
        echo '<div class="toolbar">';
        echo '<a rel="dialog" href="'.$this->wrel('edit').'">edit</a>';
        echo '<a rel="dialog" href="'.$this->wrel('delete').'">delete</a>';
        echo '</div>';
    }
    public function statusbar(){
        echo '<div class="statusbar"></div>';
    }
    protected static $_adminMode = false;
    public static function setAdminMode($adminMode = true){
        self::$_adminMode = $adminMode;
    }
    public function header(){
        echo '<div class="widget '.get_class($this).'" id="'.$this->_widgetId.'">';
        if (self::$_adminMode)
            $this->toolbar();
    }
    public function index(){
        echo 'WIDGET';
    }
    public function footer(){
        if (self::$_adminMode)
            $this->statusbar();
        echo '</div>';
    }
    public function __toString(){
        $this->_header();
        $this->index();
        $this->_footer();
    }
}
