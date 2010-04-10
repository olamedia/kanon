<?php
require_once dirname(__FILE__).'/../storageDriver.php';
class pdoDriver extends storageDriver{
	public function getDataTypeSql($type, $size, $unsigned, $notNull){
		$nn = $notNull?' NOT NULL':'';
		$u = $unsigned?' UNSIGNED':'';
		switch ($type){
			case modelProperty::TYPE_VARCHAR:
				switch ($this->_databaseType){
					case 'sqlite':
						return 'VARCHAR';
					default:
						return 'VARCHAR('.$size.')';
				}
			case modelProperty::TYPE_INTEGER:
				// $size - display width of integer
				switch ($this->_databaseType){
					case 'sqlite':
						return 'INTEGER'.$nn;
					default:
						if ($size>10){
							return 'BIGINT('.$size.')'.$u.$nn; // BIGINT is an extension to the SQL
						}elseif($size<=3){
							return 'TINYINT('.$size.')'.$u.$nn; // TINYINT is an extension to the SQL
						}else{
							return 'INT('.$size.')'.$u.$nn;
						}
				}
			case modelProperty::TYPE_FLOAT:
				switch ($this->_databaseType){
					case 'sqlite':
						return 'FLOAT'.$nn;
					default:
						return 'FLOAT'.$u.$nn;
				}
			case modelProperty::TYPE_DOUBLE:
				switch ($this->_databaseType){
					case 'sqlite':
						return 'DOUBLE'.$nn;
					default:
						return 'DOUBLE'.$u.$nn;
				}
			case modelProperty::TYPE_BOOLEAN:
				switch ($this->_databaseType){
					case 'sqlite':
						return 'INTEGER'.$nn;
					default:
						return 'TINYINT(1)'.$u.$nn;
				}
		}
	}
	protected function _makeConnection(){
		$this->_connection = new PDO($this->get('dsn'), $this->get('username'), $this->get('password'));
		$this->_connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}
	public function free($result){
		unset($result);
	}
	/**
	 * Execute an SQL statement and return the number of affected rows
	 * @param string $sql
	 */
	public function execute($sql){
		try{
			$result = $this->getConnection()->exec($sql);
		}catch(PDOException $e){
			$result = false;
			$this->_repairCollection($e);
		}
		return $result;
	}
	protected function _createCollection(){
		$models = $this->getStorage()->getModels();
		foreach ($models as $model){
			$collection = $model::getCollection();
			if (!$collection->exists()){
				echo $model.' collection  not exists'."\r\n";
			}else{
				echo $model.' collection  exists'."\r\n";
			}
		}
	}
	/**
	 *
	 * @param PDOException $errorInfo
	 */
	protected function _repairCollection($errorInfo){
		$errorCode = $errorInfo->getCode();
		var_dump($errorCode);
		switch ($errorCode[0]){
			case 'HY000': // General error
				switch ($errorCode[1]){
					case '1': // sqlite: no such table
						$this->_createCollection();
						break;
				}
				break;
		}
	}
	/**
	 * Executes an SQL statement, returning a result set
	 * @param string $sql
	 */
	public function query($sql){
		try{
			$result = $this->getConnection()->query($sql);
		}catch(PDOException $e){
			$result = false;
			if ($this->_autoRepair){
				$this->_repairCollection($e); // CREATE/ALTER
			}
		}
		return $result;
	}
	public function fetch($resultSet){
		return $resultSet->fetch();
	}
	public function fetchColumn($resultSet, $columnNumber = 0){
		if (is_object($resultSet)){
			return $resultSet->fetchColumn($columnNumber);
		}
		return false;
	}
	public function rowCount($resultSet){
		return $resultSet->rowCount();
	}
	public function quote($string){
		return $this->getConnection()->quote($string);
	}
	public function lastInsertId(){
		$this->getConnection()->lastInsertId();
	}
}