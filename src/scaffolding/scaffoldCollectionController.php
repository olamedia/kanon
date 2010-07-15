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
			border-collapse: collapse;
		}
		.scaffold-list th{
			background: #EEEEEE;
			border: solid 1px #CCCCCC;
			padding: 3px;
			font-size: 11px;
		}
		.scaffold-list td{
			border: solid 1px #CCCCCC;
			padding: 3px;
			font-size: 11px;
		}
		.scaffold-list td.odd{
			background: #F1F1F1;
		}
		
		');
	}
	public function index($page){
		$this->_collection->setItemsByPage($this->_itemsByPage);
		$itemsCount = count($this->_collection->select());
		if ($itemsCount){
			$pagesCount = ceil($itemsCount/$this->_itemsByPage);
			$this->viewPages($pagesCount, $page);
			echo '<table class="scaffold-list">';
			$first = true;
			$odd = true;
			foreach ($this->_collection->select()->page($page) as $item){
				$odd = !$odd;
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
				echo '<tr'.($odd?' class="odd"':'').'>';
				foreach ($properties as $propertyName){
					echo '<td>';
					$property = $item->{$propertyName};
					echo $property->html();
					echo '</td>';
				}
				echo '</tr>';
			}
			echo '<tr>';
			echo '<th colspan="'.count($properties).'">';
			echo 'Найдено: '.$itemsCount;
			echo '</th>';
			echo '</tr>';
			echo '</table>';
			$this->viewPages($pagesCount, $page);
		}
	}
}