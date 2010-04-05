<?php
require_once dirname(__FILE__).'/modelQueryBuilder.php';
//, Countable
class modelResultSet extends modelQueryBuilder implements IteratorAggregate, Countable{
	protected $_result = null;
	protected $_finished = false;
	protected $_list = array();
	protected $_useCache = true;
	public function useCache($use = true){
		$this->_useCache = $use;
		return $this;
	}
	protected function _makeModels($a){
		$models = array();
		foreach ($this->_selected as $sa){
			if ($sa instanceof modelAggregation){
				$models[] = $a[$sa->getAs()];
			}else{
				list($table, $fields) = $sa;
				if (!($modelClass = $table->getModelClass())){
					$modelClass = 'model';
				}
				$model = new $modelClass();
				foreach ($fields as $field){
					$model[$field->getName()]->setInitialValue($a[$field->getUniqueId()]);
				}
				$models[] = $model;
			}
		}
		if (count($models) == 1){
			return $model;
		}
		return $models;
	}
	public function count(){
		//echo $this->getCountSql();
		return $this->getStorage()->fetchColumn(
		$this->getStorage()->query($this->getCountSql()),0
		);
	}
	public function execute(){
		//echo $this->getSql();
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
				if ($this->_useCache){
					$this->_list[] = $models;
				}
				return $models;
			}
		}
		$this->_finished = true;
		return false;
	}
	protected function _fetchAll(){
		$this->useCache(true);
		while ($this->fetch()){}
	}
	public function getIterator(){
		$this->useCache(true);
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
	/*public function __destruct(){
		unset($this->_result);
	}*/
}