<?php
class scaffoldModelCollectionController extends controller{
	/**
	 * @var modelCollection
	 */
	protected $_collection = null;
	protected $_itemsByPage = 50;
	public function onConstruct(){
		$this->_collection = $this->_options['collection'];
	}
	public function index($page){
		$this->_collection->setItemsByPage($this->_itemsByPage);
		$itemsCount = count($this->_collection->select());
		$pagesCount = ceil($itemsCount/$this->_itemsByPage);
		$this->viewPages($pagesCount, $page);
		echo '<table>';
		foreach ($this->_collection->select()->page($page) as $item){
			
		}
		echo '</table>';
		$this->viewPages($pagesCount, $page);
	}
}