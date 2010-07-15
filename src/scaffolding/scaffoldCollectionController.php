<?php
class scaffoldModelCollectionController extends controller{
	/**
	 * @var modelCollection
	 */
	protected $_collection = null;
	protected $_itemsByPage = 50;
	public function onConstruct(){
		$this->_collection = $this->_options['collection'];
		$this->css('
		table.scaffold-list{
			border: solid 1px #666;
		}
		.scaffold-list th{
			background: #eee;
			border: solid 1px #999;
			padding: 3px;
		}
		.scaffold-list td{
			border: solid 1px #999;
			padding: 3px;
		}
		');
	}
	public function index($page){
		$this->_collection->setItemsByPage($this->_itemsByPage);
		$itemsCount = count($this->_collection->select());
		$pagesCount = ceil($itemsCount/$this->_itemsByPage);
		$this->viewPages($pagesCount, $page);
		echo '<table class="scaffold-list">';
		$first = true;
		foreach ($this->_collection->select()->page($page) as $item){
			$properties = $item->getPropertyNames();
			if ($first){
				echo '<tr>';
				foreach ($properties as $propertyName){
					echo '<th>';
					echo $propertyName;
					echo '</th>';
				}
				echo '</tr>';
				$first = false;
			}
			echo '<tr>';
			foreach ($properties as $propertyName){
				echo '<td>';
				$property = $item->{$propertyName};
				echo $property->html();
				echo '</td>';
			}
			echo '</tr>';
		}
		echo '</table>';
		$this->viewPages($pagesCount, $page);
	}
}