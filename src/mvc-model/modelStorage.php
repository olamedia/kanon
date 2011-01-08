<?php

#require_once dirname(__FILE__).'/storageDrivers/mysqlDriver.php';
#require_once dirname(__FILE__).'/storageDrivers/pdoDriver.php';

class modelStorage{
    private static $_foreignConnections = array();
    private static $_instances = array();
    /**
     * @var storageDriver
     */
    private $_storageDriver = null;
    protected $_uniqueId = null;
    protected $_unregisteredForeignKeys = array();
    protected $_cacheEnabled = false;
    public function enableCache(){
        $this->_cacheEnabled = true;
    }
    public function disableCache(){
        $this->_cacheEnabled = false;
    }
    public function isCacheEnabled(){
        return $this->_cacheEnabled;
    }
    public function getUniqueId(){
        if ($this->_uniqueId === null){
            $this->_uniqueId = kanon::getUniqueId();
        }
        return $this->_uniqueId;
    }
    public function internalQuery($sql){
        return $this->_storageDriver->internalQuery($sql);
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
                    $seta[] = "`$fieldName` = ".$this->quote($property->getInternalValue());
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
                        $wherea[] = "`$fieldName` = ".$this->quote($initialValue);
                    }else{
                        if ($useAssignedValues){
                            $value = $property->getInternalValue();
                            if ($value !== null){
                                $wherea[] = "`$fieldName` = ".$this->quote($value);
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
            $property = $model[$fieldName];
            if ($property){
                $initialValue = $property->getInitialValue();
                if ($initialValue !== null){
                    $wherea[] = "`$fieldName` = ".$this->quote($initialValue);
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
        if ($debug)
            echo $sql;
        if ($this->execute($sql)){
            $model->makeValuesInitial();
            // Update AutoIncrement property
            $autoIncrement = $model->getAutoIncrement();
            if (isset($_COOKIE['debug'])){
                echo ' ai='.$autoIncrement.' ';
            }
            if ($autoIncrement !== null){
                $property = &$model->{$autoIncrement};
                if ($value = $this->lastInsertId()){
                    if (!is_object($property)){
                        throw new Exception('Autoincrement "'.print_r($autoIncrement, true).'" not defined in class "'.get_class($model).'"');
                    }
                    $property->setInitialValue($value);
                    //$property->setValue($value);
                    if (isset($_COOKIE['debug'])){
                        echo ' setValue='.$value.' getValue='.$model->{$autoIncrement}->getValue();
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
        if ($sql = $this->_getUpdateSql($model)){
            if (isset($_COOKIE['debug'])){
                echo $sql;
            }
            if ($this->execute($sql)){
                $model->makeValuesInitial();
                return true;
            }
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
    /**
     *
     * @param string $name
     * @return modelStorage
     */
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
    public function getModels(){
        $collections = array();
        foreach ($this->getRegistry()->modelSettings as $model=>$setting){
            $collections[] = $model;
        }
        return $collections;
    }
    public function registerForeignKeys(){
        if (self::$_foreignKeysCache !== null){
            if (is_file(self::$_foreignKeysCache)){
                $a = unserialize(file_get_contents(self::$_foreignKeysCache));
                $this->getRegistry()->foreignKeys = $a['keys'];
                $this->getRegistry()->reverseKeys = $a['reverseKeys'];
                return $this;
            }
        }
        foreach ($this->_unregisteredForeignKeys as $modelName){
            $this->_registerForeignKeys($modelName);
        }
        $this->_unregisteredForeignKeys = array();
        if (self::$_foreignKeysCache !== null){
            $a = array(
                'keys'=>$this->getRegistry()->foreignKeys,
                'reverseKeys'=>$this->getRegistry()->reverseKeys,
            );
            file_put_contents(self::$_foreignKeysCache, $a);
        }
        return $this;
    }
    protected static $_foreignKeysCache = null;
    public static function setForeignKeysCache($filename){
        self::$_foreignKeysCache = $filename;
    }
    protected function _registerForeignKeys($modelName){
        //echo '<div>+ '.$modelName.'</div>';
        $keys = &$this->getRegistry()->foreignKeys;
        $reverseKeys = &$this->getRegistry()->reverseKeys;

        $collection = modelCollection::getInstance($modelName);
        $fks = $collection->getForeignKeys();
        foreach ($fks as $propertyName=>$a){
            //var_dump($a);
            foreach ($a as $foreignModel=>$foreignPropertyName){
                //list($foreignModel, $foreignPropertyName) = $a;
                //echo '+ '.$modelName.'.'.$propertyName.' =>  '.$foreignModel.'.'.$foreignPropertyName.':<br />';//'.$a.'
                $keys[$foreignModel][$modelName] = array($foreignPropertyName, $propertyName);
                $keys[$modelName][$foreignModel] = array($propertyName, $foreignPropertyName);
            }
        }
        foreach ($keys as $model=>$connections){
            //echo '<div>Test '.$model.' ';
            foreach ($connections as $viaModel=>$options){
                //echo 'using '.$viaModel.' ';
                if ($keys->offsetExists($viaModel)){
                    //echo 'ok ';
                    foreach ($keys[$viaModel] as $foreignModel=>$options2){
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
        //echo '<pre>';
        //var_dump($keys);
        //var_dump($reverseKeys);
        //echo '</pre>';
    }
    public static function getTableModel($collection){
        return $collection->getModelClass();
    }
    public static function getIndirectTablesJoins($sourceTable, $targetTable, $joinTypes, $joinWhere){
        $keys = &storageRegistry::getInstance()->foreignKeys;
        $sourceClass = self::getTableModel($sourceTable);
        $targetClass = self::getTableModel($targetTable);
        $joins = array();
        $joinedTables = array();
        //echo 'Connecting from '.$sourceClass.' to '.$targetClass.'<br />';
        foreach ($keys[$sourceClass] as $foreignClass=>$options){
            if ($foreignClass == $targetClass){
                if (!is_array($options)){
                    $viaClass = $options;
                    //echo 'Connecting via '.$viaClass.'<br />';
                    $viaTable = modelCollection::getInstance($viaClass);
                    $subJoins = self::getIndirectTablesJoins($sourceTable, $viaTable, $joinTypes, $joinWhere);
                    if ($subJoins !== false){
                        foreach ($subJoins as $uid=>$joinString){
                            $joins[$uid] = $joinString;
                        }
                    }
                    $subJoins = self::getIndirectTablesJoins($viaTable, $targetTable, $joinTypes, $joinWhere);
                    if ($subJoins !== false){
                        foreach ($subJoins as $uid=>$joinString){
                            $joins[$uid] = $joinString;
                        }
                    }
                    return $joins;
                }else{
                    // FIXED JOIN TYPE & ON() SELECTION
                    $joinType = isset($joinTypes[$targetTable->getUniqueId()])?$joinTypes[$targetTable->getUniqueId()]:'INNER';
                    $joinOn = $sourceTable->getJoinOn($targetTable);
                    if ($joinOn === null){
                        $joinOn = $targetTable->getJoinOn($sourceTable);
                        if ($joinOn === null){
                            $joinOn = '';
                        }
                    }
                    list($sourcePropertyName, $targetPropertyName) = $options;
                    $joinString = " ".$joinType." JOIN {$targetTable->getTableName()} AS $targetTable ON ({$sourceTable->$sourcePropertyName} = {$targetTable->$targetPropertyName}";
                    if (isset($joinWhere[$targetTable->getUniqueId()])){ // apply filters
                        $joinString .= " AND ".implode(" AND ", $joinWhere[$targetTable->getUniqueId()]);
                    }
                    if (strlen($joinOn)){
                        $joinString .= " AND ".$joinOn;
                    }
                    $joinString .= ")";
                    //$joins[$sourceTable->getUniqueId()] = true;
                    $joins[$targetTable->getUniqueId()] = $joinString;
                    //echo 'Connecting via DIRECT<br />';
                    if ($joinString !== false)
                        return $joins; //array($joinedTables, $joinString);




                }
            }
        }
        //var_dump($keys[$sourceClass]);
        //throw new Exception('Connecting via FALSE');
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
        if ($dsn instanceof modelStorage){
            $this->_storageDriver = & $dsn->getDriver();
            return $this;
        }
        $extension = reset(explode(":", $dsn));
        $driverName = null;
        if ($extension == 'sqlite'){ // prefer pdo (sqlite2,sqlite3)
            if (extension_loaded('pdo')){
                $driverName = 'pdoDriver';
            }
        }
        if ($driverName === null){
            $driverName = $extension.'Driver';
            if (!extension_loaded($extension)){ // prefer native, but fallback to pdo
                $driverName = 'pdoDriver';
                if (!extension_loaded('pdo')){
                    return $this;
                }
            }
        }
        $dsne = substr($dsn, strlen($extension) + 1);
        $this->_storageDriver = new $driverName;
        $this->_storageDriver->setStorage($this);
        $this->_storageDriver->setDatabaseType($extension);
        $this->_storageDriver->setup('dsn', $dsn);
        $this->_storageDriver->setup('username', $username);
        $this->_storageDriver->setup('password', $password);
        $this->_storageDriver->setup('charset', $charset);
        $dsna = explode(";", $dsne);
        foreach ($dsna as $p){
            list($k, $v) = explode("=", $p);
            $this->_storageDriver->setup($k, $v);
        }
        return $this;
    }
}