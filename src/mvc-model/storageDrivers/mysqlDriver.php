<?php
require_once dirname(__FILE__).'/../storageDriver.php';
class mysqlDriver extends storageDriver{
	public function quoteFieldName($fieldName){
		return '`'.$fieldName.'`';
	}
	protected function _makeConnection(){
		if ($host = $this->get('host')){
			if (!$port = $this->get('port')){
				$port = 3307;
			}
			$host = $host.':'.$port;
		}else{
			if ($this->get('unix_socket')){
				$host = ':'.$this->get('unix_socket');
			}
		}
		if ($host){
			$this->_connection = mysql_connect($host, $this->get('username'), $this->get('password'));
		}
		if ($dbname = $this->get('dbname')){
			mysql_select_db($dbname, $this->_connection);
		}
		if ($charset = $this->get('charset')){
			mysql_query("SET NAMES ".$charset, $this->_connection);
		}
	}
	/**
	 * Execute an SQL statement and return the number of affected rows
	 * @param string $sql
	 */
	public function execute($sql){
		mysql_query($sql, $this->getConnection());
		return mysql_affected_rows($this->getConnection());
	}
	/**
	 * Executes an SQL statement, returning a result set
	 * @param string $sql
	 */
	public function query($sql){
		return mysql_query($sql, $this->getConnection());
	}
	public function fetch($resultSet){
		return mysql_fetch_assoc($resultSet);
	}
	public function fetchColumn($resultSet, $columnNumber = 0){
		return mysql_result($resultSet,0,$columnNumber);
	}
	public function rowCount($resultSet){
		return mysql_num_rows($resultSet);
	}
	public function quote($string){
		return mysql_real_escape_string($string, $this->getConnection());
	}
	public function lastInsertId(){
		return mysql_insert_id($this->getConnection());
	}
}