<?php
require_once dirname(__FILE__).'/modelQueryBuilder.php';
//, Countable
class modelResultSet extends modelQueryBuilder implements IteratorAggregate, Countable{
	protected $_result = null;
	protected $_finished = false;
	protected $_list = array();
	protected function _makeModels($a){
		$models = array();
		foreach ($this->_selected as $sa){
			if ($sa instanceof modelAggregation){
				$models[] = $a[$sa->getAs()];
			}else{
				//reset($this->_selected);
				//$sa = current($this->_selected);
				list($table, $fields) = $sa;
				if (!($modelClass = $table->getModelClass())){
					$modelClass = 'model';
				}
				$model = new $modelClass();
				foreach ($fields as $field){
					//$k_fn = $field->getUniqueId();
					$model[$field->getName()]->setInitialValue($a[$field->getUniqueId()]);
				}
				/*
				$tid = $table->getUniqueId();
				foreach ($a as $k => $v){
					if (($p = strpos($k, "__")) !== FALSE){
						$k_tid = substr($k, 0, $p);
						$k_fn = substr($k,$p+2);
						if ($tid == $k_tid){
							if (!is_object($model[$k_fn]) && is_object($model)){
								// throw new Exception("Property \"".htmlspecialchars($k_fn)."\" not exists in class \"".get_class($model).'"');
							}else{
								$model[$k_fn]->setInitialValue($v);
							}
						}
					}else{
						// aggregate?
					}
				}*/
				$models[] = $model;
			}
		}
		if (count($models) == 1){
			return $model;
		}
		return $models;
	}
	public function count(){
		echo $this->getCountSql();
		return $this->getStorage()->fetchColumn(
		$this->getStorage()->query($this->getCountSql()),0
		);
	}
	public function execute(){
		if ($this->_result === null){
			if ($this->_result = $this->getStorage()->query($this->getSql())){
				return true;
			}
			return false;
		}
		return true;
	}
	/**
	 * Fetch a result row
	 * @return model
	 */
	public function fetch(){
		//echo ' fetch()';
		if ($this->_finished) return false;
		$this->execute();
		//var_dump($this->getStorage());
		//var_dump($this->_result);
		if ($this->_result){
			if ($a = $this->getStorage()->fetch($this->_result)){
				//var_dump($a);
				/*$this->_list[] = $a;
				 return $a;*/
				$models = $this->_makeModels($a);
				$this->_list[] = $models;
				return $models;
			}
		}
		$this->_finished = true;
		return false;
	}
	protected function _fetchAll(){
		//echo ' _fetchAll()';
		while ($this->fetch()){}
	}
	public function getIterator(){
		$this->_fetchAll();
		return new ArrayIterator($this->_list);
	}
	public function delete(){ // Properly delete models
		foreach ($this as $result){
			if ($result instanceof model){
				$result->delete();
			}else{
				foreach ($result as $model){
					$model->delete();
				}
			}
		}
	}

}