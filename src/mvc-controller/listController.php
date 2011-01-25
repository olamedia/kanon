<?php

class listController extends controller{
    protected $_model = null;
    protected $_id = 'id';
    protected $_title = 'title';
    protected $_subController = null;
    protected function _getTitle($item){
        if (method_exists($item, 'getTitle')){
            return $item->getTitle();
        }
        return $item->{$this->_title}->html();
    }
    protected function _getHref($item, $rel){
        if (method_exists($item, 'getHref')){
            return $item->getHref();
        }
        return $this->rel($rel.$item->id);
    }
    public function _action($action){
        //$app = application::getInstance();
        $modelId = intval($action);
        $class = get_class($this);
        $cl = strlen($class);
        $lc = 'ListController';
        $l = strlen($lc);
        if ($this->_model === null){
            if (substr($class, $cl - $l, $l) == $lc){
                $this->_model = substr($class, 0, $cl - $l);
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
        $item = $items->select()->where("$items->{$this->_id} = '$modelId'")->fetch();
        if ($item){
            $this->onValidItem($modelId, $item);
            $this->runController($subController, array($model=>$item));
        }else{
            $this->notFound();
        }
    }
    public function onValidItem($modelId, $item, $rel = ''){
        $this->setTitle($this->_getTitle($item));
        $parentItem = $item;
        $bc = array();
        while ($parentItem){
            $bc[] = '<a href="'.$this->_getHref($parentItem, $rel).'">'.$this->_getTitle($parentItem).'</a>';
            $parentItem = $parentItem->getParent();
        }
        $this->appendToBreadcrumb(array_reverse($bc));
    }
}