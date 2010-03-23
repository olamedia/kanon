<?php
require_once dirname(__FILE__).'/storageDrivers/mysqlDriver.php';
class modelStorage{
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
		return $this;
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