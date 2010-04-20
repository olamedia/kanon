<?php
require_once dirname(__FILE__).'/modelQueryBuilder.php';
class modelResultSet extends modelQueryBuilder implements IteratorAggregate, Countable{
	protected $_result = null;
	protected $_finished = false;
	protected $_list = array();
	protected $_useCache = true;
	public function destroy(){
		foreach ($this->_list as $m){
			if (is_subclass_of($m, 'model')) $m->destroy();
		}
		unset($this->_list);
		unset($this);
	}
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
		$result = $this->getStorage()->query($this->getCountSql());
		$count = 0;
		if ($result){
			while ($a = $this->getStorage()->fetch($result)){
				$count += array_shift($a);
			} 
		}
		return $count;
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
		if ($this->_finished) return false;
		$this->execute();
		if ($this->_result){
			if ($a = $this->getStorage()->fetch($this->_result)){
				$models = $this->_makeModels($a);
				if ($this->_useCache){
					$this->_list[] = $models;
				}
				return $models;
			}
		}
		$this->_finished = true;
		$this->getStorage()->free($this->_result);
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