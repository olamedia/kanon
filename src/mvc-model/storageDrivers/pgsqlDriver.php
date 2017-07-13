<?php

#require_once dirname(__FILE__).'/../storageDriver.php';

class pgsqlDriver extends storageDriver{
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
                return 'BOOLEAN'.$nn;
                break;
        }
    }
    protected function _repairCollection($errorInfo){
        var_dump($errorInfo);
    }
    public function quoteFieldName($fieldName){
        return pg_escape_identifier($this->getConnection(), $fieldName);
    }
    public function free($result){
        return pg_free_result($result);
    }
    public function internalQuery($sql){
        $result = pg_query($this->getConnection(), $sql);
        return $result;
    }
    public function quoteField($fieldName){
		return pg_escape_identifier($this->getConnection(), $fieldName);
        //return '`'.$string.'`';
    }
    protected function _makeConnection(){
		$cs = [];
		if ($host = $this->get('host')){
            $cs[] = 'host='.$host;
        }
		if ($port = $this->get('port')){
			$cs[] = 'port='.$port;
		}
		if ($dbname = $this->get('dbname')){
            $cs[] = 'dbname='.$dbname;
        }
		if ($username = $this->get('username')){
            $cs[] = 'user='.$username;
        }
		if ($password = $this->get('password')){
            $cs[] = 'password='.$password;
        }
		if ($charset = $this->get('charset')){
			$cs[] = 'options=\'--client_encoding='.$charset.'\'';
        }
        //if ($this->get('unix_socket')){
        try{
            $this->_connection = pg_connect(implode(' ', $cs));
        }catch(Exception $e){
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }
    /**
     * Execute an SQL statement and return the number of affected rows
     * @param string $sql
     */
    public function execute($sql){
        $result = $this->query($sql);
        return pg_affected_rows($result);//$this->getConnection());
    }
    /**
     * Executes an SQL statement, returning a result set
     * @param string $sql
     */
    public function query($sql){
        $result = $this->internalQuery($sql);
        if (!$result){
            $errorString = pg_last_error($this->getConnection());
            // Error: 1146 SQLSTATE: 42S02 (ER_NO_SUCH_TABLE)
            // Message: Table '%s.%s' doesn't exist
            //if ($errorNumber == 1146){
                //$this->_createCollection();
            //}
            //if ($errorNumber == 1054){
                // Mysql Error #1054 - Unknown column
                //$this->_updateCollection();
            //}
            //$errorNumber = mysql_errno($this->getConnection());
                throw new Exception(
                        'Pgsql Error '.htmlspecialchars($errorString).
                        ' SQL:'.htmlspecialchars($sql)
                );
        }
        return $result;
    }
    public function fetch($resultSet){
        return pg_fetch_assoc($resultSet);
    }
    public function fetchColumn($resultSet, $columnNumber = 0){
        return pg_fetch_result($resultSet, 0, $columnNumber);
    }
    public function rowCount($resultSet){
        return pg_num_rows($resultSet);
    }
    public function quote($string){
        return pg_escape_literal($this->getConnection(), $string);
    }
    public function lastInsertId($property){
		$id = pg_fetch_result(pg_query($this->getConnection(), 'SELECT CURRVAL(pg_get_serial_sequence('.pg_escape_identifier($this->getConnection(), $property->getModel()->getTableName()).','.pg_escape_identifier($this->getConnection(), $property->getFieldName()).'))'), 0, 0);
        return $id;
    }
	public function getInsertSql($model){
		$storage = $model->getStorage();
        $setSql = $this->getSetValuesSql($model);
        if ($setSql){
            return "INSERT INTO ".$this->quoteFieldName($model->getTableName())." ".$setSql;
        }
        return false;
    }
}
