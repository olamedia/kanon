<?php
#require_once dirname(__FILE__).'/../common/kanon.php';
#require_once dirname(__FILE__).'/storageRegistry.php';
abstract class storageDriver{
	protected $_uniqueId = null;
	protected $_databaseType = '';
	protected $_connection = null;
	protected $_storage = null; // back reference to storage
	protected $_autoRepair = true;
	protected $_serviceMode = true;
	protected static $_cache = array();
	public function enableServiceMode(){
		$this->_serviceMode = true;
		return $this;
	}
	public function disableServiceMode(){
		$this->_serviceMode = false;
		return $this;
	}
	public function enableAutoRepair(){
		$this->_autoRepair = true;
		return $this;
	}
	public function disableAutoRepair(){
		$this->_autoRepair = false;
		return $this;
	}
	public function setStorage($storage){
		$this->_storage = $storage;
		return $this;
	}
	abstract public function internalQuery($sql);
	public function quoteField($string){
		return '"'.$string.'"'; // ANSI
	}
	/**
	 * Update collection if some fields missing
	 */
	protected function _updateCollection(){
		$updated = false;
		$models = $this->getStorage()->getModels();
		foreach ($models as $model){
			$collection = modelCollection::getInstance($model);
			/** @var $collection modelCollection */
			if ($collection->exists()){
				if ($realFields = $this->getFieldNames($collection)){
					$collectionFields = $collection->getFieldNames();
					foreach ($collectionFields as $fieldName){
						if (!in_array($fieldName, $realFields)){
							$helper = $collection->getHelper();
							$fieldSql = $helper[$fieldName]->getCreateSql($this);
							// ALTER TABLE  `sdf` ADD  `sdfads` INT NOT NULL AFTER  `id`
							$tableName = $collection->getTableName();
							// AFTER  `id`
							// FIRST
							$sql = "ALTER TABLE {$tableName} ADD ".$fieldSql;
							if ($collection->internalQuery($sql)){
								$updated = true;
							}
						}
					}
				}
			}else{
				$this->_createCollection();
			}
		}
		return $updated;
	}
	protected function getFieldNames($collection){
		$tableName = $collection->getTableName();
		$fields = array();
		if ($q = $collection->internalQuery("DESCRIBE {$tableName}")){
			while ($a = $this->fetch($q)){
				$fields[] = $a['Field'];
			}
		}
		return $fields;
	}
	/**
	 * Create collections if not exists
	 */
	protected function _createCollection(){
		//if ($this->_autoRepair){
		/*throw new Exception(
		 'Trying to _createCollection()'
			);*/
		$created = false;
		//echo 'Create collection()'."\r\n";
		$models = $this->getStorage()->getModels();
		foreach ($models as $model){
			$collection = modelCollection::getInstance($model);
			/** @var modelCollection $collection */
			if (!$collection->exists()){
				//echo $model.' collection  not exists'."\r\n";
				//$this->disableAutoRepair();
				//$this->disableServiceMode();
				//echo $collection->getCreateSql();
				/*throw new Exception(
						'Trying to create table '.$collection->getTableName().' SQL:'.$collection->getCreateSql()
				);*/
				if ($collection->internalQuery($collection->getCreateSql())){
					$created = true;
				}
				//$this->enableServiceMode();
				//$this->enableAutoRepair();
			}else{
				//echo $model.' collection  exists'."\r\n";
			}
		}
		/*throw new Exception(
		 'Trying to _createCollection() - end'
		 );*/
		 return $created;
			//}
			//return false;
	}
	/**
	 * @return modelStorage
	 */
	public function getStorage(){
		return $this->_storage;
	}
	public function setDatabaseType($type){
		$this->_databaseType = $type;
		return $this;
	}
	abstract protected function _repairCollection($errorInfo);
	public function getConnection(){
		if ($this->_connection === null){
			$this->_makeConnection();
		}
		return $this->_connection;
	}
	abstract protected function _makeConnection();
	public function getUniqueId(){
		if ($this->_uniqueId === null){
			$this->_uniqueId = kanon::getUniqueId();
		}
		return $this->_uniqueId;
	}
	abstract public function getDataTypeSql($type, $size, $unsigned, $notNull);
	abstract public function free($result);
	abstract public function execute($sql);
	abstract public function query($sql);
	abstract public function lastInsertId();
	abstract public function fetch($resultSet);
	abstract public function fetchColumn($resultSet, $columnNumber = 0);
	abstract public function rowCount($resultSet);
	abstract public function quote($string);
	public function get($name){
		$driverOptions = $this->getRegistry()->driverOptions;
		return isset($driverOptions[$this->getUniqueId()][$name])?$driverOptions[$this->getUniqueId()][$name]:false;
	}
	public function setup($name, $value){
		//echo 'setup('.$name.', '.$value.') ';
		$driverOptions = $this->getRegistry()->driverOptions;
		$driverOptions[$this->getUniqueId()][$name] = $value;
		$this->getRegistry()->driverOptions = $driverOptions;
	}
	public function getRegistry(){
		return storageRegistry::getInstance();
	}
}