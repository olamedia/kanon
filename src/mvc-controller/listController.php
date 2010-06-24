<?php
class listController extends controller{
	protected $_model = null;
	protected $_title = 'title';
	protected $_subController = null;
	public function _action($action){
		//$app = application::getInstance();
		$modelId = intval($action);
		$class = get_class($this);
		$cl = strlen($class);
		$lc = 'ListController';
		$l = strlen($lc);
		if ($this->_model === null){
			if (substr($class,$cl-$l,$l)==$lc){
				$this->_model = substr($class,0,$cl-$l);
			}
			if (($this->_model === null) || (!class_exists($this->_model))){
				throw new Exception('setup $_model in '.get_class($this));
			}
		}
		$model = $this->_model;
		if ($this->_subController === null){
			$this->_subController = $model.'Controller';
			if (!class_exists($this->_subController)){
				throw new Exception('setup $_subController in '.get_class($this));
			}
		}
		$subController = $this->_subController;
		$items = modelCollection::getInstance($model);
		$item = $items->select()->where("$items->id = '$modelId'")->fetch();
		if ($item){
			$this->onValidItem($modelId, $item);
			$this->runController($subController, array($model => $item));
		}
	}
	public function onValidItem($modelId, $item, $rel = ''){
		$parentItem = $item;
		$bc = array();
		while ($parentItem){
			$bc[] = '<a href="'.$this->rel($rel.$parentItem->id).'">'.$parentItem->{$this->_title}->html().'</a>';
			$parentItem = $parentItem->getParent();
		}
		$this->appendToBreadcrumb(array_reverse($bc));
	}
}