<?php

class modelResultSet extends modelQueryBuilder implements IteratorAggregate, Countable{
    protected $_result = null;
    protected $_finished = false;
    protected $_list = array();
    protected $_useCache = null; // null - default, true - use, false -no
    protected $_cacheLifetime = null; // in seconds
    protected $_rawMode = false;
    public function raw($raw = true){
        $this->_rawMode = $raw;
        return $this;
    }
    public function destroy(){
        $this->_result = null;
        foreach ($this->_list as $m){
            if (is_subclass_of($m, 'model'))
                destroy($m);
        }
        $this->free();
        $this->_list = array();
    }
    public function free(){
        if ($this->_result !== null){
            if (!is_array($this->_result)){
                $this->getStorage()->getDriver()->free($this->_result);
                $this->_result = null;
                $this->_finished = false;
            }
        }
    }
    public function __destruct(){
        $this->destroy();
    }
    /**
     * @return modelResultSet
     */
    public function noCache(){
        $this->_useCache = false;
        return $this;
    }
    /**
     * @return modelResultSet
     */
    public function cache($lifetime = null){
        $this->_useCache = true;
        if ($lifetime !== null){
            $this->_cacheLifetime = $lifetime;
        }
        return $this;
    }
    public function isCacheEnabled(){
        return $this->_useCache === null?modelCache::isEnabledByDefault():$this->_useCache;
    }
    public function getCacheLifetime(){
        return $this->_cacheLifetime;
    }
    public function useCache($use = true){ // deprecated
        $this->_useCache = $use;
        return $this;
    }
    protected function &_makeModels(&$a){
        if ($this->_rawMode){
            if (count($a) == 1){
                return array_shift($a);
            }
            return $a;
        }
        var_dump($a);
        $models = array();
        $made = array();
        foreach ($this->_selected as &$sa){
            if ($sa instanceof modelAggregation){
                $models[] = $a[$sa->getAs()];
                /* }elseif ($sa instanceof modelAggregation){
                  $models[] = $a[$sa->getAs()]; */
            }elseif (is_array($sa)){
                list($table, $fields) = $sa;
                if ($table instanceof modelCollection){
                    if (!($modelClass = $table->getModelClass())){
                        $modelClass = 'model';
                    }
                    if (!isset($made[$modelClass])){
                        $made[$modelClass] = true;
                        $model = new $modelClass();
                        $model->markSaved();
                        $model->preLoad();
                        foreach ($fields as $field){
                            //$model[$field->getName()]->setInitialValue($a[$field->getUniqueId()]);
                            $model->setInitialFieldValue($field->getName(), $a[$field->getUniqueId()]);
                        }
                        $model->postLoad();
                        $models[] = $model;
                    }
                }else{
                    list($k, $v) = each($sa);
                    $model = $a[$k];
                    $models[] = $model;
                }
            }else{
                
            }
        }
        if (count($models) == 1){
            return $model;
        }
        return $models;
    }
    public function count(){
        //echo $this->getCountSql();
        if (modelCache::isEnabled()){
            $count = modelCache::getResult($this, true);
            if ($count !== false){
                return $count;
            }
        }
        if (modelCache::prefetchOnCount()){
            $this->execute();
            if (is_array($this->_result)){
                $count = count($this->_result);
            }else{
                $count = $this->getStorage()->getDriver()->rowCount($this->_result);
            }
            $this->_result = null; // reset result
        }else{
            $result = $this->getStorage()->query($this->getCountSql());
            $count = 0;
            if ($result){
                while ($a = $this->getStorage()->fetch($result)){
                    $count += array_shift($a);
                }
            }
        }
        if (modelCache::isEnabled()){
            modelCache::cache($this, $count, true);
        }
        return $count;
    }
    public function execute(){
        if ($this->_result === null){
            if (modelCache::isEnabled()){
                $this->_result = modelCache::getResult($this);
            }
            if ($this->_result){
                return true;
            }else{
                $this->_result = $this->getStorage()->query($this->getSql());
                if ($this->_result){
                    if (modelCache::isEnabled()){ // rebuild as array
                        $results = array();
                        while ($result = $this->fetch()){
                            $results[] = $result;
                        }
                        modelCache::cache($this, $results);
                        $this->_result = $results;
                        $this->_finished = false;
                    }else{
                        //$this->_result = $results;
                    }
                    return true;
                }
            }
            return false;
        }
        return true;
    }
    public function reset(){
        $this->free();
    }
    /**
     * Fetch a result row
     * @return model
     */
    public function fetch(){
        if ($this->_finished)
            return false;
        $this->execute();
        var_dump($this->_result);
        if ($this->_result){
            if (is_array($this->_result)){
                // cached values
                if (count($this->_result)){
                    return array_shift($this->_result);
                }else{
                    $this->_result = null;
                }
            }else{
                $storage = $this->getStorage();
                echo 'storage:';
                var_dump($storage);
                $a = $storage->fetch($this->_result);
                if ($a){
                    var_dump($a);
                    $models = $this->_makeModels($a);
                    //if ($this->_useCache){
                    //	$this->_list[] = $models;
                    //}
                    return $models;
                }else{
                    echo '<h2>Failed</h2>';
                    var_dump(mysql_fetch_assoc(mysql_query($this->getSql(), $storage->getConnection())));
                    var_dump($a);
                    var_dump(mysql_errno($storage->getConnection()));
                    var_dump(mysql_error($storage->getConnection()));
                }
            }
        }
        $this->_finished = true;
        $this->free();
        return false;
    }
    protected function _fetchAll(){
        $this->useCache(true);
        while ($this->fetch()){
            
        }
    }
    public function getIterator(){
        //$this->useCache(true);
        //$this->_fetchAll();
        //return new ArrayIterator($this->_list);
        return new modelResultSetIterator($this);
    }
    public function delete(){ // Properly delete models
        foreach ($this as $result){
            if ($result instanceof model){
                $result->delete();
            }else{
                foreach ($result as $model){
                    $model->delete();
                }
            }
        }
    }
    public function toArray(){
        $a = array();
        foreach ($this as $result){
            $a[] = $result;
        }
        return $a;
    }
    /* public function __destruct(){
      unset($this->_result);
      } */
}