<?php
class zenWidgets{
	protected static $_widgetNames = array();
	public static function registerWidget($widgetClassName, $widgetName){
		self::$_widgetNames[$widgetClassName] = $widgetName;
	}
	public static function getWidgetNames(){
		return self::$_widgetNames;
	}
}
class zenWidget{
	protected $_controller = null;
	protected $_placement = null;
	protected $_contentId = null;
	protected $_options = array();
	public function __construct($controller){
		$this->_controller = $controller;
	}
	public function setOptions($options = array()){
		$this->_options = $options;
	}
	public function setContentId($contentId){
		$this->_contentId = $contentId;
	}
	public function setPlacement($placement){
		$this->_placement = $placement;
		$this->_contentId = $placement->contentId->getValue();
	}
	public function html(){
		echo 'WIDGET';
	}
} 
// module
class newsListWidget extends zenWidget{
	public function html(){
		echo 'NEWS';
	}
}
