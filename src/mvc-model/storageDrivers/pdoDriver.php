<?php
class pdoDriver extends storageDriver{
	protected function _makeConnection(){
		$this->_connection = new PDO($this->get('dsn'), $this->get('username'), $this->get('password'));
	}
	/**
	 * Execute an SQL statement and return the number of affected rows
	 * @param string $sql
	 */
	public function execute($sql){
		$this->getConnection()->exec($sql);
	}
	/**
	 * Executes an SQL statement, returning a result set
	 * @param string $sql
	 */
	public function query($sql){
		return $this->getConnection()->query($sql);
	}
	public function fetch(PDOStatement $resultSet){
		return $resultSet->fetch();
	}
	public function fetchColumn(PDOStatement $resultSet, $columnNumber = 0){
		return $resultSet->fetchColumn($columnNumber);
	}
	public function rowCount(PDOStatement $resultSet){
		return $resultSet->rowCount();
	}
	public function quote($string){
		return $this->getConnection()->quote($string);
	}
	public function lastInsertId(){
		$this->getConnection()->lastInsertId();
	}
}