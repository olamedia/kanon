<?php
require_once dirname(__FILE__).'/storageDrivers/mysqlDriver.php';
class modelStorage{
	private static $_foreignConnections = array();
	private static $_instances = array();
	/**
	 * @var storageDriver
	 */
	private $_storageDriver = null;
	protected $_uniqueId = null;
	public function getUniqueId(){
		if ($this->_uniqueId === null){
			$this->_uniqueId = kanon::getUniqueId();
		}
		return $this->_uniqueId;
	}
	public function saveModel($item){}
	public function insertModel($item){}
	public function updateModel($item){}
	public function deleteModel($item){}
	private function __construct(){

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
		$this->_registerForeignKeys($modelName);
		return $this;
	}
	protected function _registerForeignKeys($modelName){
		//echo '<div>+ '.$modelName.'</div>';
		$keys = &$this->getRegistry()->foreignKeys;
		$reverseKeys = &$this->getRegistry()->reverseKeys;
		$collection = modelCollection::getInstance($modelName);
		$fks = $collection->getForeignKeys();
		foreach ($fks as $propertyName => $a){
			list($foreignModel, $foreignPropertyName) = $a;
			//echo '+ '.$modelName.'.'.$propertyName.' => '.$foreignModel.'.'.$foreignPropertyName.':<br />';
			$keys[$foreignModel][$modelName] = array($foreignPropertyName, $propertyName);
			$keys[$modelName][$foreignModel] = array($propertyName, $foreignPropertyName);
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
	public static function getIndirectTablesJoins($sourceTable, $targetTable, $joinOptions){
		$keys = &storageRegistry::getInstance()->foreignKeys;
		$sourceClass = self::getTableModel($sourceTable);
		$targetClass = self::getTableModel($targetTable);
		$joins = array();
		$joinedTables = array();
		foreach ($keys[$sourceClass] as $foreignClass => $options){
			if ($foreignClass == $targetClass){
				if (!is_array($options)){
					$viaClass = $options;
					//echo 'Connectiong via '.$viaClass.'<br />';
					$viaTable = self::getModelTable($viaClass);
					$join1 = self::getIndirectTablesJoins($sourceTable, $viaTable, $joinOptions);
					$join2 = self::getIndirectTablesJoins($viaTable, $targetTable, $joinOptions);
					if ($join1 !== false){
						list($extraJoinedTables, $joinString) = $join1;
						$joins[] = $joinString;
						foreach ($extraJoinedTables as $uid => $b){
							$joinedTables[$uid] = true;
						}
					}
					if ($join2 !== false){
						list($extraJoinedTables, $joinString) = $join2;
						$joins[] = $joinString;
						foreach ($extraJoinedTables as $uid => $b){
							$joinedTables[$uid] = true;
						}
					}
					return array($joinedTables, implode("", $joins));
				}else{
					$joinType = $options[$targetTable->getUniqueId()]['type'];
					list($sourcePropertyName, $targetPropertyName) = $options;
					$joinString = " ".$joinType." JOIN {$targetTable->getTableName()} AS $targetTable ON ({$sourceTable->$sourcePropertyName} = {$targetTable->$targetPropertyName})";
					$joinedTables[$sourceTable->getUniqueId()] = true;
					$joinedTables[$targetTable->getUniqueId()] = true;
					//echo 'Connectiong via DIRECT<br />';
					if ($joinString !== false) return array($joinedTables, $joinString);
				}
			}
		}
		//echo 'Connectiong via FALSE<br />';
		return false;
	}
	/**
	 *
	 * @param string $dsn
	 * @param string $username
	 * @param string $password
	 * @return modelStorage
	 */
	public function connect($dsn, $username = 'root', $password = ''){
		$extension = reset(explode(":", $dsn));
		$driverName = $extension.'Driver';
		if (!extension_loaded($extension)){
			$driverName = 'pdoDriver';
			if (!extension_loaded('pdo')){
				return $this;
			}
		}
		$dsne = substr($dsn, strlen($extension)+1);
		$this->_storageDriver = new $driverName;
		$this->_storageDriver->setup('dsn',$dsn);
		$this->_storageDriver->setup('username',$username);
		$this->_storageDriver->setup('password',$password);
		$dsna = explode(";", $dsne);
		foreach ($dsna as $p){
			list($k,$v) = explode("=", $p);
			$this->_storageDriver->setup($k,$v);
		}
		return $this;
	}
}