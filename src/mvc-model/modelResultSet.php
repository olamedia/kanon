<?php
require_once dirname(__FILE__).'/modelQueryBuilder.php';
//, Countable
class modelResultSet extends modelQueryBuilder implements IteratorAggregate{
	public function fetch(){
		
	}
	public function getIterator(){
		
	}
	public function execute(){
		
	}
	public function delete(){ // Properly delete models
		foreach ($this as $model){
			$model->delete();
		}
	}

}