<?php
require_once dirname(__FILE__).'/storageDrivers/mysqlDriver.php';
require_once dirname(__FILE__).'/storageDrivers/pdoDriver.php';
class modelStorage{
	private static $_foreignConnections = array();
	private static $_instances = array();
	/**
	 * @var storageDriver
	 */
	private $_storageDriver = null;
	protected $_uniqueId = null;
	protected $_unregisteredForeignKeys = array();
	public function getUniqueId(){
		if ($this->_uniqueId === null){
			$this->_uniqueId = kanon::getUniqueId();
		}
		return $this->_uniqueId;
	}
	protected function _getWhatSql($item){
		return '*';
	}
	/**
	 *
	 * @param model $model
	 */
	protected function _getSetSql($model){
		$seta = array();
		$fields = $model->getFieldNames();
		foreach ($fields as $fieldName){
			$property = $model[$fieldName];
			if ($property){
				if ($property->hasChangedValue()){
					$seta[] = "`$fieldName` = '".$this->quote($property->getInternalValue())."'";
				}
			}
		}
		if (count($seta)){
			return " SET ".implode(",", $seta);
		}
		return false;
	}
	/**
	 *
	 * @param model $model
	 */
	protected function _getWherePrimaryKeySql($model, $useAssignedValues = false){
		$wherea = array();
		$pk = $model->getPrimaryKey();
		if (count($pk)){
			foreach ($pk as $propertyName){
				$property = $model->{$propertyName};
				$fieldName = $property->getFieldName();
				if (is_object($property)){
					$initialValue = $property->getInitialValue();
					if ($initialValue !== null){
						$wherea[] = "`$fieldName` = '".$this->quote($initialValue)."'";
					}else{
						if ($useAssignedValues){
							$value = $property->getValue();
							if ($value !== null){
								$wherea[] = "`$fieldName` = '".$this->quote($value)."'";
							}
						}
					}
				}
			}
			if (count($pk) == count($wherea)){
				return " WHERE ".implode(" AND ", $wherea);
			}
		}
		return false;
	}
	/**
	 *
	 * @param model $model
	 */
	protected function _getWhereSql($model, $useAssignedValues = false){
		if (($whereSql = $this->_getWherePrimaryKeySql($model, $useAssignedValues)) !== false){
			return $whereSql;
		}
		// can't use PK
		$wherea = array();
		$fields = $model->getFieldNames();
		foreach ($fields as $fieldName){
			$property = $item[$fieldName];
			if ($property){
				$initialValue = $property->getInitialValue();
				if ($initialValue !== null){
					$wherea[] = "`$fieldName` = '".$this->quote($initialValue)."'";
				}
			}
		}
		if (count($wherea)){
			return " WHERE ".implode(" AND ", $wherea);
		}
		return false;
	}
	/**
	 *
	 * @param model $model
	 */
	protected function _getInsertSql($model){
		$setSql = $this->_getSetSql($model);
		if ($setSql){
			return "INSERT INTO `{$model->getTableName()}`".$setSql;
		}
		return false;
	}
	/**
	 *
	 * @param model $model
	 */
	protected function _getUpdateSql($model){
		$setSql = $this->_getSetSql($model);
		if ($setSql){
			return "UPDATE `{$model->getTableName()}`".$setSql.$this->_getWhereSql($model)." LIMIT 1";
		}
		return false;
	}
	/**
	 *
	 * @param model $model
	 */
	protected function _getDeleteSql($model){
		return "DELETE FROM `{$model->getTableName()}`".$this->_getWhereSql($model, true)." LIMIT 1";
	}
	/**
	 *
	 * @param model $model
	 */
	public function saveModel($model, $debug = false){
		if ($this->_getWhereSql($model)){
			//echo 'update '.get_class($model).' '.$this->_getWhereSql($model);
			//exit;
			$result = $model->update($debug);
		}else{
			//echo 'insert '.get_class($model).' '.$this->_getWhereSql($model);
			//exit;
			$result = $model->insert($debug);
		}
		return $result;
	}
	public function free($result){
		$this->_storageDriver->free($result);
	}
	/**
	 *
	 * @param model $model
	 */
	public function insertModel($model, $debug = false){
		if (isset($_COOKIE['debug'])){
			echo ' insertModel ';
		}
		$sql = $this->_getInsertSql($model);
		if ($debug) echo $sql;
		if ($this->query($sql)){
			$model->makeValuesInitial();
			// Update AutoIncrement property
			$autoIncrement = $model->getAutoIncrement();
			if (isset($_COOKIE['debug'])){
				echo ' ai='.$autoIncrement.' ';
			}
			if ($autoIncrement !== null){
				$property = $model->{$autoIncrement};
				if ($value = $this->lastInsertId()){
					if (!is_object($property)){
						throw new Exception('Autoincrement "'.print_r($autoIncrement, true).'" not defined in class "'.get_class($model).'"');
					}
					$property->setInitialValue($value);
					//$property->setValue($value);
					if (isset($_COOKIE['debug'])){
						echo ' setValue='.$value.' ';
					}
				}else{
					throw new Exception('Autoincrement error');
				}
			}
			return true;
		}
		return false;
	}
	/**
	 *
	 * @param model $model
	 */
	public function updateModel($model, $debug = false){
		$sql = $this->_getUpdateSql($model);
		if ($debug) echo $sql;
		if ($this->query($sql)){
			$model->makeValuesInitial();
			return true;
		}
		return false;
	}
	/**
	 *
	 * @param model $model
	 */
	public function deleteModel($model){
		$sql = $this->_getDeleteSql($model);
		$this->query($sql);
	}
	private function __construct(){

	}
	/**
	 * @return storageDriver
	 */
	public function getDriver(){
		return $this->_storageDriver;
	}
	public function getConnection(){
		return $this->_storageDriver->getConnection();
	}
	public static function getInstance($name = 'default'){
		if (!isset(self::$_instances[$name])){
			$instance = new self;
			self::$_instances[$name] = $instance;
			storageRegistry::getInstance()->storages[$instance->getUniqueId()] = $instance;
		}
		return self::$_instances[$name];
	}
	public function execute($sql){
		return $this->_storageDriver->execute($sql);
	}
	public function query($sql){
		return $this->_storageDriver->query($sql);
	}
	public function lastInsertId(){
		return $this->_storageDriver->lastInsertId();
	}
	public function fetch($resultSet){
		return $this->_storageDriver->fetch($resultSet);
	}
	public function fetchColumn($resultSet, $columnNumber = 0){
		return $this->_storageDriver->fetchColumn($resultSet, $columnNumber = 0);
	}
	public function rowCount($resultSet){
		return $this->_storageDriver->rowCount($resultSet);
	}
	public function quote($string){
		return $this->_storageDriver->quote($string);
	}
	public function getRegistry(){
		return storageRegistry::getInstance();
	}
	public function registerCollection($modelName, $tableName){
		$this->getRegistry()->modelSettings[$modelName]['table'] = $tableName;
		$this->getRegistry()->modelSettings[$modelName]['storage'] = $this->getUniqueId();
		$this->_unregisteredForeignKeys[] = $modelName;
		return $this;
	}
	public function registerForeignKeys(){
		foreach ($this->_unregisteredForeignKeys as $modelName){
			$this->_registerForeignKeys($modelName);
		}
		$this->_unregisteredForeignKeys = array();
		return $this;
	}
	protected function _registerForeignKeys($modelName){
		//echo '<div>+ '.$modelName.'</div>';
		$keys = &$this->getRegistry()->foreignKeys;
		$reverseKeys = &$this->getRegistry()->reverseKeys;
		$collection = modelCollection::getInstance($modelName);
		$fks = $collection->getForeignKeys();
		foreach ($fks as $propertyName => $a){
			//var_dump($a);
			foreach ($a as $foreignModel => $foreignPropertyName){
				//list($foreignModel, $foreignPropertyName) = $a;
				//echo '+ '.$modelName.'.'.$propertyName.' => '.$a.' '.$foreignModel.'.'.$foreignPropertyName.':<br />';
				$keys[$foreignModel][$modelName] = array($foreignPropertyName, $propertyName);
				$keys[$modelName][$foreignModel] = array($propertyName, $foreignPropertyName);
			}
		}
		foreach ($keys as $model => $connections){
			//echo '<div>Test '.$model.' ';
			foreach ($connections as $viaModel => $options){
				//echo 'using '.$viaModel.' ';
				if ($keys->offsetExists($viaModel)){
					//echo 'ok ';
					foreach ($keys[$viaModel] as $foreignModel => $options2){
						if ($foreignModel !== $model){
							if (!isset($keys[$model][$foreignModel])){
								//echo $model.'=>'.$foreignModel.' via '.$viaModel.'.<br />';
								//echo $indirectForeignClass2.'<br />';
								$keys[$model][$foreignModel] = $viaModel;
								$reverseKeys[$foreignModel][$model] = $viaModel;
							}
						}
					}
				}
			}
			//echo '</div>';
		}
	}
	public static function getTableModel($collection){
		return $collection->getModelClass();
	}
	public static function getIndirectTablesJoins($sourceTable, $targetTable, $joinType, $joinOn = ''){
		$keys = &storageRegistry::getInstance()->foreignKeys;
		$sourceClass = self::getTableModel($sourceTable);
		$targetClass = self::getTableModel($targetTable);
		$joins = array();
		$joinedTables = array();
		//echo 'Connecting from '.$sourceClass.' to '.$targetClass.'<br />';
		foreach ($keys[$sourceClass] as $foreignClass => $options){
			if ($foreignClass == $targetClass){
				if (!is_array($options)){
					$viaClass = $options;
					//echo 'Connecting via '.$viaClass.'<br />';
					$viaTable = modelCollection::getInstance($viaClass);
					$subJoins = self::getIndirectTablesJoins($sourceTable, $viaTable, $joinOptions);
					if ($subJoins !== false){
						foreach ($subJoins as $uid => $joinString){
							$joins[$uid] = $joinString;
						}
					}
					$subJoins = self::getIndirectTablesJoins($viaTable, $targetTable, $joinOptions);
					if ($subJoins !== false){
						foreach ($subJoins as $uid => $joinString){
							$joins[$uid] = $joinString;
						}
					}
					return $joins;
				}else{
					//$joinType = isset($joinOptions[$targetTable->getUniqueId()]['type'])?$joinOptions[$targetTable->getUniqueId()]['type']:'INNER';
					list($sourcePropertyName, $targetPropertyName) = $options;
					$joinString = " ".$joinType." JOIN {$targetTable->getTableName()} AS $targetTable ON ({$sourceTable->$sourcePropertyName} = {$targetTable->$targetPropertyName}";
					if (strlen($joinOn)){
						$joinString .= " AND ".$joinOn;
					}
					$joinString .= ")";
					//$joins[$sourceTable->getUniqueId()] = true;
					$joins[$targetTable->getUniqueId()] = $joinString;
					//echo 'Connecting via DIRECT<br />';
					if ($joinString !== false) return $joins;//array($joinedTables, $joinString);
				}
			}
		}
		var_dump($keys[$sourceClass]);
		throw new Exception('Connecting via FALSE');
		return false;
	}
	/**
	 *
	 * @param string $dsn
	 * @param string $username
	 * @param string $password
	 * @return modelStorage
	 */
	public function connect($dsn, $username = 'root', $password = '', $charset = 'UTF8'){
		$extension = reset(explode(":", $dsn));
		/*if (extension_loaded('pdo')){
			$extension = 'pdo';
			}*/
		$driverName = $extension.'Driver';
		if (!extension_loaded($extension)){
			$driverName = 'pdoDriver';
			if (!extension_loaded('pdo')){
				return $this;
			}
		}
		$dsne = substr($dsn, strlen($extension)+1);
		$this->_storageDriver = new $driverName;
		$this->_storageDriver->setDatabaseType($extension);
		$this->_storageDriver->setup('dsn',$dsn);
		$this->_storageDriver->setup('username',$username);
		$this->_storageDriver->setup('password',$password);
		$this->_storageDriver->setup('charset',$charset);
		$dsna = explode(";", $dsne);
		foreach ($dsna as $p){
			list($k,$v) = explode("=", $p);
			$this->_storageDriver->setup($k,$v);
		}
		return $this;
	}
}