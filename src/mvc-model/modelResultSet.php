<?php
#require_once dirname(__FILE__).'/modelQueryBuilder.php';
#require_once dirname(__FILE__).'/modelResultSetIterator.php';

class modelResultSet extends modelQueryBuilder implements IteratorAggregate, Countable{
	protected $_result = null;
	protected $_finished = false;
	protected $_list = array();
	protected $_useCache = false;
	public function destroy(){
		$this->_result = null;
		foreach ($this->_list as $m){
			if (is_subclass_of($m, 'model'))
				destroy($m);
		}
		$this->free();
		$this->_list = array();
	}
	public function free(){
		if ($this->_result!==null){
			$this->getStorage()->getDriver()->free($this->_result);
			$this->_result = null;
			$this->_finished = false;
		}
	}
	public function __destruct(){
		$this->destroy();
	}
	public function useCache($use = true){
		$this->_useCache = $use;
		return $this;
	}
	protected function &_makeModels(&$a){
		$models = array();
		foreach ($this->_selected as $sa){
			if ($sa instanceof modelAggregation){
				$models[] = $a[$sa->getAs()];
				/* }elseif ($sa instanceof modelAggregation){
				  $models[] = $a[$sa->getAs()]; */
			}elseif (is_array($sa)){
				list($table, $fields) = $sa;
				if ($table instanceof modelCollection){
					if (!($modelClass = $table->getModelClass())){
						$modelClass = 'model';
					}
					$model = new $modelClass();
					$model->markSaved();
					foreach ($fields as $field){
						//$model[$field->getName()]->setInitialValue($a[$field->getUniqueId()]);
						$model->setInitialFieldValue($field->getName(), $a[$field->getUniqueId()]);
					}
				}else{
					list($k, $v) = each($sa);
					$model = $a[$k];
				}
				$models[] = $model;
			}else{
				
			}
		}
		if (count($models)==1){
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
		if ($this->_result===null){
			if (modelCache::isEnabled()){
				$this->_result = modelCache::getResult($this);
			}
			if ($this->_result){
				return true;
			}else{
				if ($this->_result = $this->getStorage()->query($this->getSql())){
					if (modelCache::isEnabled()){ // rebuild as array
						$results = array();
						while ($result = $this->fetch()){
							$results[] = $result;
						}
						modelCache::cache($this, $results);
						$this->_result = $results;
						$this->_finished = false;
					}else{
						$this->_result = $results;
					}
					return true;
				}
			}
			return false;
		}
		return true;
	}
	public function reset(){
		$this->free();
	}
	/**
	 * Fetch a result row
	 * @return model
	 */
	public function fetch(){
		if ($this->_finished)
			return false;
		$this->execute();
		if ($this->_result){
			if (is_array($this->_result)){
				// cached values
				if (count($this->_result)){
					return array_shift($this->_result);
				}else{
					$this->_result = null;
				}
			}else{
				if ($a = $this->getStorage()->fetch($this->_result)){
					$models = $this->_makeModels($a);
					//if ($this->_useCache){
					//	$this->_list[] = $models;
					//}
					return $models;
				}
			}
		}
		$this->_finished = true;
		$this->free();
		return false;
	}
	protected function _fetchAll(){
		$this->useCache(true);
		while ($this->fetch()){

		}
	}
	public function getIterator(){
		//$this->useCache(true);
		//$this->_fetchAll();
		//return new ArrayIterator($this->_list);
		return new modelResultSetIterator($this);
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
	/* public function __destruct(){
	  unset($this->_result);
	  } */
}