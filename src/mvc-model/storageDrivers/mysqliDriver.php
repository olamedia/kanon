<?php

class mysqliDriver extends storageDriver{
    public function getDataTypeSql($type, $size, $unsigned, $notNull){
        $nn = $notNull?' NOT NULL':'';
        $u = $unsigned?' UNSIGNED':'';
        switch ($type){
            case modelProperty::TYPE_TEXT:
                return 'TEXT'.$nn; //('.$size.')
                break;
            case modelProperty::TYPE_VARCHAR:
                return 'VARCHAR('.$size.')'.$nn;
                break;
            case modelProperty::TYPE_INTEGER:
                if ($size > 10){
                    return 'BIGINT('.$size.')'.$u.$nn; // BIGINT is an extension to the SQL
                }elseif ($size <= 3){
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
        return ($result->free());
    }
    public function internalQuery($sql){
        //if (isset($_COOKIE['debug'])){
        //    echo $sql."<br />";
        //}
        $time = microtime(true);

        $result = $this->getConnection()->query($sql);

        profiler::getInstance()->addSql($sql, $time);
        return $result;
    }
    public function quoteField($string){
        return '`'.$string.'`';
    }
    protected function _makeConnection(){
		$host = $this->get('host');
		$port = $this->get('port');
		if (!$port){
			$port = 3307;
		}
		$dbname = $this->get('dbname');
		$this->_connection = mysqli_init();
		try{
	        if ($host){
				$this->_connection->real_connect($host, $this->get('username'), $this->get('password'), $dbname, $port);
				//$this->_connection->
	        }else{
	            //if ($this->get('unix_socket')){
	            //    $host = ':'.$this->get('unix_socket');
	            //}
	        }
		}catch(Exception $e){
			throw new Exception($e->getMessage(), $e->getCode());
		}
        if ($charset = $this->get('charset')){
            $this->_connection->set_charset($charset);
        }
    }
    /**
     * Execute an SQL statement and return the number of affected rows
     * @param string $sql
     */
    public function execute($sql){
        $result = $this->query($sql);
		if (true === $result || false === $result){
			return $result;
		}
		return $result->affected_rows;
    }
    /**
     * Executes an SQL statement, returning a result set
     * @param string $sql
     */
    public function query($sql){
        $result = $this->internalQuery($sql);
        if (!$result){
            $errorNumber = $this->getConnection()->errno;
            // Error: 1146 SQLSTATE: 42S02 (ER_NO_SUCH_TABLE)
            // Message: Table '%s.%s' doesn't exist
            if ($errorNumber == 1146){
                $this->_createCollection();
            }
            if ($errorNumber == 1054){
                // Mysql Error #1054 - Unknown column
                $this->_updateCollection();
            }
            if (!$result){
                throw new Exception(
                        'Mysql Error #'.$errorNumber.
                        ' - '.($this->getConnection()->error).
                        ' SQL:'.htmlspecialchars($sql)
                );
            }
        }
        return $result;
    }
    public function fetch($resultSet){
        return $resultSet->fetch_assoc();
    }
    public function fetchColumn($resultSet, $columnNumber = 0){
		$resultSet->data_seek(0); // row 0
		$resultSet->field_seek($columnNumber);
		return $resultSet->fetch_field();
        //return mysql_result($resultSet, 0, $columnNumber);
    }
    public function rowCount($resultSet){
        return $resultSet->num_rows;
    }
    public function quote($string){
        return "'".($this->getConnection()->real_escape_string($string))."'";
    }
    public function lastInsertId($property){
        $id = $this->getConnection()->insert_id;
        return $id;
    }
}
