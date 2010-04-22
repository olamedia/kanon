<?php
require_once dirname(__FILE__).'/../storageDriver.php';
class mysqlDriver extends storageDriver{
	public function getDataTypeSql($type, $size, $unsigned, $notNull){
		$nn = $notNull?' NOT NULL':'';
		$u = $unsigned?' UNSIGNED':'';
		switch ($type){
			case modelProperty::TYPE_VARCHAR:
				return 'VARCHAR('.$size.')'.$nn;
				break;
			case modelProperty::TYPE_INTEGER:
				if ($size>10){
					return 'BIGINT('.$size.')'.$u.$nn; // BIGINT is an extension to the SQL
				}elseif($size<=3){
					return 'TINYINT('.$size.')'.$u.$nn; // TINYINT is an extension to the SQL
				}else{
					return 'INT('.$size.')'.$u.$nn;
				}
				break;
			case modelProperty::TYPE_FLOAT:
				return 'FLOAT'.$u.$nn;
				break;
			case modelProperty::TYPE_DOUBLE:
				return 'DOUBLE'.$u.$nn;
				break;
			case modelProperty::TYPE_BOOLEAN:
				return 'TINYINT(1)'.$u.$nn;
				break;
		}
	}
	protected function _repairCollection($errorInfo){
		var_dump($errorInfo);
	}
	public function quoteFieldName($fieldName){
		return '`'.$fieldName.'`';
	}
	public function free($result){
		mysql_free_result($result);
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
		if (isset($_COOKIE['debug'])){
			echo $sql;
		}
		mysql_query($sql, $this->getConnection());
		return mysql_affected_rows($this->getConnection());
	}
	/**
	 * Executes an SQL statement, returning a result set
	 * @param string $sql
	 */
	public function query($sql){
		if (isset($_COOKIE['debug'])){
			echo $sql."<br />";
		}
		$result = mysql_query($sql, $this->getConnection());
		if (!$result){
			foreach (debug_backtrace() as $d){
				var_dump($d['file'].':'.$d['line']);
			}
			throw new Exception(mysql_error().' SQL:'.htmlspecialchars($sql));
		}
		return $result;
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
		return "'".mysql_real_escape_string($string, $this->getConnection())."'";
	}
	public function lastInsertId(){
		$id = mysql_insert_id($this->getConnection());
		if (isset($_COOKIE['debug'])){
			echo ' mysql_insert_id '.$id.' ';
		}
		return $id;
	}
}