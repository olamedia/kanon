<?php
require_once dirname(__FILE__).'/../common/kanon.php';
require_once dirname(__FILE__).'/storageRegistry.php';
abstract class storageDriver{
	protected $_uniqueId = null;
	protected $_databaseType = '';
	protected $_connection = null;
	public function setDatabaseType($type){
		$this->_databaseType = $type;
		return $this;
	}
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