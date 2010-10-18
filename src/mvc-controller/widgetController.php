<?php

/**
 * Description of widgetController
 *
 * @author olamedia
 */
class widgetController extends controller{
	protected $_widgetUri = null;
	protected $_widgetId = null;
        /**
         *
         * @param integer $widgetId Constant id for widget settings over site
         * @param controller $parentController
         * @param array $options
         */
	public function __construct($widgetId, $parentController,$options = array()){
		$this->_baseUri = uri::fromString('/');
		$this->_relativeUri = uri::fromRequestUri();
		$this->_me = new ReflectionClass(get_class($this));
		$this->setBaseUri($parentController->_childUri);
		$this->setOptions($options);
		$this->setWidgetUri($parentController->rel($parentController->getWidgetAction().'/'.$widgetId));
		$this->setWidgetId($widgetId);
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
