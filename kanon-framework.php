<?php

/**
 * $Id$
 */
class nullObject{
	// nothing
}

/**
 * $Id$
 */
class registry implements ArrayAccess, IteratorAggregate{
	/**
	 * The variables array
	 * @access private
	 */
	private $_vars = array();
	/**
	 * Set variable
	 * @param string $index
	 * @param mixed $value
	 * @return void
	 */
	public function __set($key, $value){
		$this->_vars[$key] = $value;
	}
	/**
	 * Get variable
	 * @param mixed $index
	 * @return mixed
	 */
	public function __get($key){
		if (!isset($this->_vars[$key])){
			$this->_vars[$key] = new registry();
		}
		return $this->_vars[$key];
	}
	public function __isset($key){
		return isset($this->_vars[$key]);
	}
	public function offsetExists($offset){
		return array_key_exists($offset, $this->_vars);
	}
	public function offsetGet($offset){
		return $this->__get($offset);
	}
	public function offsetSet($offset, $value){
		$this->__set($offset, $value);
	}
	public function offsetUnset($offset){
		unset($this->_vars[$offset]);
	}
	public function getIterator(){
		return new ArrayIterator($this->_vars);
	}
}

/**
 * $Id$
 * Class representation of relative uri string
 * @author olamedia 
 */
class uri{
	/**
	 * Path section of URI
	 * @var array
	 */
	protected $_path = array();
	/**
	 * Query section of URI (after ?)
	 * @var array
	 */
	protected $_args = array();
	/**
	 * Get current domain name, excluding www. prefix
	 * @return string Domain name
	 */
	public static function getDomainName(){
		$da = explode(".", $_SERVER['SERVER_NAME']);
		reset($da);
		if ($da[0] == 'www'){
			array_shift($da);
		}
		return implode(".", $da);
	}
	/**
	 * Set path section of URI
	 * @param array $path
	 * @return uri Self
	 */
	public function setPath($path){
		$this->_path = $path;
		// realpath() for uri (stripping ".."):
		$k1 = false;
		$before = array();
		foreach ($this->_path as $k2 => $dir){
			if ($k1 !== false && ($this->_path[$k1] !== '..')){
				if ($dir == '..'){
					unset($this->_path[$k1]);
					unset($this->_path[$k2]);
					$k1 = $before[$k1];
					$k2 = false;
					unset($before[$k1]);
				}
			}
			if ($k2 !== false){
				$before[$k2] = $k1;
				$k1 = $k2;
			}
		}
		return $this;
	}
	/**
	 * Get path section of URI
	 * @return array
	 */
	public function getPath(){
		return $this->_path;
	}
	/**
	 * Get directory name from beginning of URI path
	 * @param integer $shift Position from beginning to get
	 * @return string
	 */
	public function getBasePath($shift = 0){
		reset($this->_path);
		for($i=0;$i<$shift;$i++) next($this->_path);
		return current($this->_path);
	}
	/**
	 * Set query section of URI
	 * @param array $args
	 * @return uri
	 */
	public function setArgs($args){
		$this->_args = $args;
		return $this;
	}
	/**
	 * Get query section of URI
	 * @return array
	 */
	public function getArgs(){
		return $this->_args;
	}
	/**
	 * Make uri object from relative path string
	 * @param string $uriString Relative url
	 * @return uri
	 */
	public static function fromString($uriString){
		$uri = new uri();
		$qpos = strpos($uriString, '?');
		$get = '';
		if ($qpos !== false){
			$get = substr($uriString, $qpos+1);
			$uriString = substr($uriString, 0, $qpos);
		}
		$geta = explode("&", $get);
		$args = array();
		foreach ($geta as $v){
			list($k, $v) = explode("=", $v);
			$args[$k] = $v;
		}
		// cut index.php
		$path = explode("/", $uriString);
		foreach ($path as $k => $v){
			if ($v == '') unset($path[$k]); else{
				$path[$k] = urldecode($v);
			}
		}
		foreach ($args as $k => $v){
			if ($v == '') unset($args[$k]);
		}
		$uri->setPath($path);
		$uri->setArgs($args);
		return $uri;
	}
	/**
	 * Make uri object from $_SERVER['REQUEST_URI']
	 * @return uri
	 */
	public static function fromRequestUri(){
		return uri::fromString($_SERVER['REQUEST_URI']);
	}
	/**
	 * Subtract $baseUri from left part of URI
	 * @param string|uri $baseUri
	 * @return uri
	 */
	public function subtractBase($baseUri){
		if (is_string($baseUri)) $baseUri = uri::fromString($baseUri);
		$basepath = $baseUri->getPath();
		$path = $this->_path;
		foreach ($basepath as $basedir){
			$dir = array_shift($path);
			if ($dir !== $basedir){
				throw new Exception('base dir not found');
			}
		}
		$this->_path = $path;
		return $this;
	}
	/**
	 * Return string representation of URI
	 * @return string
	 */
	public function __toString(){
		return '/'.implode('/',$this->_path);
	}
}

/**
 * $Id$
 * @author olamedia
 */
class fileStorage{
	protected static $_instances = array();
	protected $_name = null;
	protected $_parent = null;
	protected $_path = null;
	protected $_url = null;
	/**
	 * Constructor
	 * @param string $storageName
	 */
	protected function __construct($storageName){
		$this->_name = $storageName;
	}
	/**
	 * Get named file storage
	 * @param string $storageName
	 * @return fileStorage
	 */
	public static function getStorage($storageName = 'default'){
		if (!isset(self::$_instances[$storageName])) {
			self::$_instances[$storageName] = new static($storageName);
		}
		return self::$_instances[$storageName];
	}
	/**
	 * Get named file storage, relative to this storage
	 * @param string $storageName
	 * @param string $relativePath
	 * @return fileStorage
	 * @example $defaultStorage->getRelativeStorage('images', 'images/');
	 */
	public function getRelativeStorage($storageName, $relativePath = ''){
		static::getStorage($storageName)->setParent($this)->setRelativePath($relativePath);
		return $this;
	}
	/**
	 * Set parent storage
	 * @param fileStorage $parent
	 * @return fileStorage
	 */
	public function setParent($parent){
		$this->_parent = $parent;
		return $this;
	}
	/**
	 * Get parent storage
	 * @return fileStorage
	 */
	public function getParent(){
		return $this->_parent;
	}
	/**
	 * Set both path and url for storage, relative to parent storage
	 * @param string $relativePath
	 * @return fileStorage
	 */
	public function setRelativePath($relativePath = ''){
		$this->setPath($relativePath);
		$this->setUrl($relativePath);
		return $this;
	}
	/**
	 * Set path for storage
	 * @param string $path
	 * @return fileStorage
	 */
	public function setPath($path){
		if (!substr($path,0,1)!=='/'){
			$path = realpath($path);
			if ($path === false){
				throw new Exception('Path '.$path.' not exists');
			}
		}
		$this->_path = $this->_normalizePath($path);
		return $this;
	}
	/**
	 * Get expanded path from relative
	 * @return string|boolean
	 */
	public function getPath($relativePath = ''){
		//echo 'class::'.get_class($this).'('.$this->_name.')->getPath('.$relativePath.')start  ';
		$basename = basename($relativePath);
		$dirname = dirname($relativePath);
		if (in_array($basename, array('.', '..'))){
			// concatenate
			$dirname .= '/'.$basename;
			$basename = '';
		}
		$dirname = $this->_rel($dirname);
		//echo 'class::'.get_class($this).'('.$this->_name.')->getPath('.$relativePath.')return '.$dirname.'/'.$basename.' ';
		return $dirname.'/'.$basename;
	}
	public function getFilePath($relativeFilename){
		return $this->_relFile($relativeFilename);
	}
	/**
	 * Set url for storage
	 * @param string $url
	 * @return fileStorage
	 */
	public function setUrl($url){
		$this->_url = $this->_normalizePath($url);
		return $this;
	}
	/**
	 * Get url for file or directory 
	 * @param string $relativeUrl
	 * @return string
	 * @example $storage->getUrl('images/image.png');
	 */
	public function getUrl($relativeUrl = ''){
		$url = $this->_url.$relativeUrl;
		if (is_object($this->_parent)){
			return $this->_parent->getUrl($url);
		}
		return '/'.$url;
	}
	protected function _relFile($relativeFilename = ''){
		return $this->_rel(dirname($relativeFilename)).basename($relativeFilename);
	}
	protected function _fixPath($path){ 
       return dirname($path.'/.'); 
	}
	protected function _rel($relativePath = ''){
		//echo 'class::'.get_class($this).'('.$this->_name.')->_rel('.$relativePath.')start  ';
		$path = $this->_path.$relativePath;
		//echo 'class::'.get_class($this).'('.$this->_name.')->getPath('.$relativePath.')path '.$path.' ';
		if (is_object($this->_parent)){
			$path = $this->_parent->getPath($path);
		}else{
			$path = '/'.$path; // denormalize path
		}
		//echo 'class::'.get_class($this).'('.$this->_name.')->getPath('.$relativePath.')return <b>realpath('.$path.')='.realpath($path).'</b> ';
		$path = realpath($path);
		if ($path === false){
			// path not exists
			return false;
		}
		return $this->_fixPath($path.'/');
	}
	/**
	 * Upload file to storage 
	 * @param string $sourceFilename
	 * @param string $targetFilename
	 */
	public function upload($sourceFilename, $targetFilename){
		return copy($sourceFilename, $this->_relFile($targetFilename));
	}
	/**
	 * Download file from storage 
	 * @param string $sourceFilename
	 * @param string $targetFilename
	 */
	public function download($sourceFilename, $targetFilename){
		return copy($this->_relFile($sourceFilename), $targetFilename);
	}
	/**
	 * Write a string to a file
	 * @param string $sourceFilename
	 * @param string $targetFilename
	 */
	public function putContents($targetFilename, $data){
		return file_put_contents($this->_relFile($targetFilename), $data);
	}
	/**
	 * Reads entire file into a string
	 * @param string $sourceFilename
	 * @return string
	 */
	public function getContents($sourceFilename){
		return file_get_contents($this->_relFile($sourceFilename));
	}
	/**
	 * Normalize path for safe concatenation
	 * @param string $path
	 * @return string
	 */
	protected function _normalizePath($path){
		// 1. remove all slashes at both sides
		$path = ltrim(trim($path, "/"), "/");
		// 2. add right slash if strlen
		if (strlen($path)) $path = $path.'/';
		return $path;
	}
}
/*$defaultStorage = fileStorage::getStorage()
	->setPath(dirname(__FILE__).'/../')
	->setUrl('/');
$defaultStorage->getRelativeStorage('images', 'images/');
$defaultStorage->getRelativeStorage('css', 'css/');
$defaultStorage->getRelativeStorage('js', 'js/');
$storage = fileStorage::getStorage('css');
var_dump($storage);
echo '<hr />';
echo $storage->getUrl('images/img.png');
echo '<hr />';
echo $storage->getPath('images/img.png');
echo '<hr />';

echo '<hr />';*/

/**
 * $Id$
 */



class kanon{
	private static $_uniqueId = 0;
	/**
	 * Get named file storage
	 * @param string $storageName
	 * @return fileStorage
	 */
	public static function getUniqueId(){
		$id = self::$_uniqueId;
		$id = strval(base_convert($id, 10, 26));
		$shift = ord("a") - ord("0");
		for ($i = 0; $i < strlen($id); $i++){
			$c = $id{$i};
			if (ord($c) < ord("a")){
				$id{$i} = chr(ord($c)+$shift);
			}else{
				$id{$i} = chr(ord($c)+10);
			}
		}
		self::$_uniqueId++;
		return $id;
	}
	public static function getStorage($storageName = 'default'){
		return fileStorage::getStorage($storageName);
	}
	/**
	 * 
	 * @param string $storageName
	 * @return modelStorage
	 */
	public static function getModelStorage($storageName = 'default'){
		return modelStorage::getInstance($storageName);
	}
	public static function getCollection($modelName){
		return modelCollection::getCollection($modelName);
	}
	public static function getBaseUri(){
		$requestUri = $_SERVER['REQUEST_URI'];
		$scriptUri = $_SERVER['SCRIPT_NAME'];
		$max = min(strlen($requestUri), strlen($scriptUri));
		$cmp = 0;
		for ($l = 1; $l <= $max; $l++){
			if (substr_compare($requestUri, $scriptUri, 0, $l, true) === 0){
				$cmp = $l;
			}
		}
		return substr($requestUri, 0, $cmp);
	}
	public static function run($applicationClass){
		$app = application::getInstance($applicationClass);
		$trace = debug_backtrace();
		$file = $trace[0]['file'];
		$basePath = dirname($file);
		$app->setBasePath($basePath);
		$baseUrl = kanon::getBaseUri();
		$app->setBaseUri($baseUrl);
		$app->run();
	}
}

class modelAggregation{
	protected $_argument = null;
	protected $_function = 'SUM';
	protected $_as = '';
	public function __construct($argument, $function){
		$this->_argument = $argument;
		$this->_function = $function;
		$this->_as = kanon::getUniqueId('sql');
	}
	public function getArguments(){
		return array($this->_argument);
	}
	public function getAs(){
		return $this->_as;
	}
	public function __toString(){
		return $this->_function.'('.$this->_argument.') AS '.$this->_as;
	}
}

class modelExpression{
	protected $_left = null;
	protected $_operator = '=';
	protected $_right = null;
	public function __construct($left, $operator, $right){
		$this->_left = $left;
		$this->_operator = $operator;
		$this->_right = $right;
		if ($this->_right instanceof modelProperty){
			$this->_right = $this->_right->getValue();
		}
	}
	public function getArguments(){
		return array($this->_left, $this->_right);
	}
	public function getLeft(){
		return $this->_left;
	}
	public function getRight(){
		return $this->_right;
	}
	public function __toString(){
		$right = $this->getRight();
		if (strtoupper($this->_operator) == 'IN'){
			if (is_array($right)){
				$right = implode(",", $right);
			}
			$right = '('.$right.')';
		}
		return $this->getLeft().' '.$this->_operator.' '.$right;
	}
}



class modelField{
	protected $_collection = null;
	protected $_fieldName = null;
	protected $_uniqueId = null;
	public function getUniqueId(){
		if ($this->_uniqueId === null){
			$this->_uniqueId = kanon::getUniqueId();
		}
		return $this->_uniqueId;
	}
	/*public function getUniqueId(){
		return $this->_collection->getUniqueId().'__'.$this->_fieldName;
	}*/
	public function __construct($collection, $fieldname){
		$this->_collection = $collection;
		$this->_fieldName = $fieldname;
	}
	public function getName(){
		return $this->_fieldName;
	}
	public function getCollection(){
		return $this->_collection;
	}
	public function __toString(){
		return $this->_collection->getUniqueId().'.`'.$this->_fieldName.'`';
	}
	public function is($value){
		return new modelExpression($this, '=', $value);
	}
	public function min(){
		return new modelAggregation($this, 'MIN');
	}
	public function max(){
		return new modelAggregation($this, 'MAX');
	}
	public function sum(){
		return new modelAggregation($this, 'SUM');
	}
	public function avg(){
		return new modelAggregation($this, 'AVG');
	}
	public function lt($value){
		return new modelExpression($this, '<', $value);
	}
	public function gt($value){
		return new modelExpression($this, '>', $value);
	}
	public function in($value){
		return new modelExpression($this, 'IN', $value);
	}
	public function like($value){
		return new modelExpression($this, 'LIKE', $value);
	}
}

class modelQueryBuilder{
	/**
	 *
	 * @var modelCollection
	 */
	protected $_storageSource = null;
	protected $_joinedTables = array(); // all tables joined by user
	protected $_selectedTables = array(); // all tables in select
	protected $_selected = array();
	protected $_limitFrom = 0;
	protected $_limit = null;

	protected $_joinOptions = array();
	protected $_join = array();
	protected $_where = array();
	protected $_having = array();
	protected $_order = array();
	protected $_group = array();
	protected $_filters = array();
	public function addFilter($filter){
		$this->_filters[] = $filter;
		return $this;
	}
	/**
	 * @return modelStorage
	 */
	public function getStorage(){
		if ($this->_storageSource === null) return false;
		return  $this->_storageSource->getStorage();
	}
	public function e($unescapedString){
		return $this->getStorage()->quote($unescapedString);
	}
	/**
	 * @return modelQueryBuilder
	 */
	public function select(){
		$args = func_get_args();
		if (!count($args)) return $this;
		foreach ($args as $arg){
			if ($arg instanceof modelAggregation){
				$fields = $arg->getArguments();
				foreach ($fields as $field){
					$a["$field"] = $field;
				}
				$field = $arg;
				$this->_selected[] = $arg;
			}else{
				$table = null;
				$field = null;
				if ($arg instanceof modelCollection){
					$table = $arg;
				}
				if ($arg instanceof modelField){
					$table = $arg->getTable();
					$field = $arg;
				}

				if ($this->_storageSource === null) $this->_storageSource = $table;
				$a = array();
				if ($arg instanceof modelCollection){
					foreach ($table->getFieldNames() as $fieldName){
						$field = $table[$fieldName];
						$a["$field"] = $field;
					}
				}
				if ($arg instanceof modelField){
					$a["$field"] = $field;
				}
				$this->_selected[] = array($table, $a);
				$this->_selectedTables[$table->getUniqueId()] = $table;
				$this->_joinedTables[$table->getUniqueId()] = $table;
			}
		}
		//var_dump($this->_selected);
		return $this;
	}
	protected function _constructJoins(){
		$this->_join = array(); // reset joins
		$sourceTable = $this->_storageSource;
		$sourceTableUid = $sourceTable->getUniqueId();
		$joined = array();
		$joined[$sourceTable->getUniqueId()] = true;
		foreach ($this->_joinedTables as $tableUid => $table2){
			if ($sourceTableUid !== $tableUid){ //
				// Trying to join table
				$on = '';
				$joinType = 'INNER';
				if (isset($this->_joinOptions[$table2->getUniqueId()])){
					$options = $this->_joinOptions[$table2->getUniqueId()];
					$on = $options['on'];
					$joinType = $options['type'];
				}
				$joins = modelStorage::getIndirectTablesJoins($sourceTable, $table2, $this->_joinOptions);
				if ($joins !== false){
					foreach ($joins as $uid => $joinString){
						if (!isset($joined[$uid])){
							$this->_join[] = $joinString;
							$joined[$uid] = true;
						}
					}
				}
			}
		}
		//var_dump($this->_join);
	}
	/**
	 * @return modelQueryBuilder
	 */
	public function &join($table2, $on = '', $joinType = 'INNER'){
		$this->_joinOptions[$table2->getUniqueId()] = array(
			'on' => $on,
			'type' => $joinType
		);
		$this->_joinedTables[$table2->getUniqueId()] = $table2;
		return $this;
	}
	/**
	 * @return modelQueryBuilder
	 */
	public function &leftJoin($table2, $on = ''){
		return $this->join($table2, $on, 'LEFT');
	}
	/**
	 * @return modelQueryBuilder
	 */
	public function &limit(){
		$args = func_get_args();
		switch (count($args)){
			case 1:
				$this->_limit = $args[0];
				$this->_limitFrom = 0;
				break;
			case 2:
				$this->_limit = $args[1];
				$this->_limitFrom = $args[0];
				break;
			default:
				$this->_limit = null;
				$this->_limitFrom = 0;
		}
		return $this;
	}
	protected function _joinCondition($condition){
		if ($condition instanceof modelExpression){
			$left = $condition->getLeft();
			if ($left instanceof modelField){
				$this->join($left->getCollection());
			}
			$right = $condition->getLeft();
			if ($right instanceof modelField){
				$this->join($right->getCollection());
			}
		}
	}
	/**
	 * @return modelQueryBuilder
	 */
	public function &where(){
		$conditions = func_num_args()?func_get_args():array();
		foreach ($conditions as $condition){
			$this->_where[] = $condition;
			$this->_joinCondition($condition);
		}
		return $this;
	}
	/**
	 * @return modelQueryBuilder
	 */
	public function &having($condition){
		$this->_having[] = $condition;
		$this->_joinCondition($condition);
		return $this;
	}
	/**
	 * @return modelQueryBuilder
	 */
	public function &asc($field){
		$this->_order[] = $field.' ASC';
		if ($field instanceof modelField){
			$this->join($field->getTable());
		}
		return $this;
	}
	/**
	 * @return modelQueryBuilder
	 */
	public function &desc($field){
		$this->_order[] = $field.' DESC';
		if ($field instanceof modelField){
			$this->join($field->getTable());
		}
		return $this;
	}
	/**
	 * @return modelQueryBuilder
	 */
	public function &orderBy($orderString){
		$this->_order[] = $orderString;
		return $this;
	}
	/**
	 * @return modelQueryBuilder
	 */
	public function &groupBy($groupString){
		$this->_group[] = $groupString;
		return $this;
	}
	protected function getWhatSql(){
		$wa = array();
		foreach ($this->_selected as $sa){
			if (is_array($sa)){
				list($table, $fields) = $sa;
				foreach ($fields as $fid => $field){
					$wa[] = $field." as ".$field->getUniqueId();
				}
			}else{
				$wa[] = "$sa";
			}
		}
		return implode(", ", $wa);
	}
	protected function getJoinSql(){
		$this->_constructJoins();
		return implode("", $this->_join);
	}
	protected function getFromSql(){
		reset($this->_selected);
		$sa = current($this->_selected);
		list($table, $fields) = $sa;
		return " FROM ".$table->getTableName()." as ".$table;
	}
	protected function getOrderSql(){
		if (count($this->_order)){
			return " ORDER BY ".implode(", ", $this->_order);
		}
		return '';
	}
	protected function getWhereSql(){
		if (count($this->_where)){
			return " WHERE ".implode(" AND ", $this->_where);
		}
		return '';
	}
	protected function getHavingSql(){
		if (count($this->_having)){
			return " HAVING ".implode(" AND ", $this->_having);
		}
		return '';
	}
	protected function getGroupBySql(){
		if (count($this->_group)){
			return " GROUP BY ".implode(", ", $this->_group);
		}
		return '';
	}
	protected function getLimitSql(){
		if ($this->_limitFrom){
			if ($this->_limit){
				return " LIMIT $this->_limitFrom, $this->_limit";
			}else{
				return "";//,18446744073709551615;
			}
		}else{
			if ($this->_limit){
				return " LIMIT $this->_limit";
			}else{
				return "";//,18446744073709551615;
			}
		}
	}
	public function getSql(){
		$sql = "SELECT ".$this->getWhatSql()
		.$this->getFromSql()
		// join
		.$this->getJoinSql()
		.$this->getWhereSql()
		.$this->getGroupBySql()
		.$this->getHavingSql()
		.$this->getOrderSql()
		.$this->getLimitSql();
		//echo '<b>'.$sql.'</b><br />';
		return $sql;
	}
	public function getCountSql(){
		$sql = "SELECT COUNT(*)"
		.$this->getFromSql()
		// join
		.$this->getJoinSql()
		.$this->getWhereSql()
		.$this->getGroupBySql()
		.$this->getHavingSql()
		.$this->getOrderSql()
		.$this->getLimitSql();
		return $sql;
	}

}


//, Countable
class modelResultSet extends modelQueryBuilder implements IteratorAggregate, Countable{
	protected $_result = null;
	protected $_finished = false;
	protected $_list = array();
	protected function _makeModels($a){
		$models = array();
		foreach ($this->_selected as $sa){
			if ($sa instanceof modelAggregation){
				$models[] = $a[$sa->getAs()];
			}else{
				//reset($this->_selected);
				//$sa = current($this->_selected);
				list($table, $fields) = $sa;
				if (!($modelClass = $table->getModelClass())){
					$modelClass = 'model';
				}
				$model = new $modelClass();
				foreach ($fields as $field){
					//$k_fn = $field->getUniqueId();
					$model[$field->getName()] = $a[$field->getUniqueId()];
				}
				/*
				$tid = $table->getUniqueId();
				foreach ($a as $k => $v){
					if (($p = strpos($k, "__")) !== FALSE){
						$k_tid = substr($k, 0, $p);
						$k_fn = substr($k,$p+2);
						if ($tid == $k_tid){
							if (!is_object($model[$k_fn]) && is_object($model)){
								// throw new Exception("Property \"".htmlspecialchars($k_fn)."\" not exists in class \"".get_class($model).'"');
							}else{
								$model[$k_fn]->setInitialValue($v);
							}
						}
					}else{
						// aggregate?
					}
				}*/
				$models[] = $model;
			}
		}
		if (count($models) == 1){
			return $model;
		}
		return $models;
	}
	public function count(){
		return $this->getStorage()->fetchColumn(
		$this->getStorage()->query($this->getCountSql()),0
		);
	}
	public function execute(){
		if ($this->_result === null){
			if ($this->_result = $this->getStorage()->query($this->getSql())){
				return true;
			}
			return false;
		}
		return true;
	}
	/**
	 * Fetch a result row
	 * @return model
	 */
	public function fetch(){
		//echo ' fetch()';
		if ($this->_finished) return false;
		$this->execute();
		//var_dump($this->getStorage());
		//var_dump($this->_result);
		if ($this->_result){
			if ($a = $this->getStorage()->fetch($this->_result)){
				//var_dump($a);
				/*$this->_list[] = $a;
				 return $a;*/
				$models = $this->_makeModels($a);
				$this->_list[] = $models;
				return $models;
			}
		}
		$this->_finished = true;
		return false;
	}
	protected function _fetchAll(){
		//echo ' _fetchAll()';
		while ($this->fetch()){}
	}
	public function getIterator(){
		$this->_fetchAll();
		return new ArrayIterator($this->_list);
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

}



class modelCollection implements ArrayAccess{
	private static $_instances = array();
	protected $_modelName = null; // helper
	//protected $_helper = null; // model instance
	protected $_uniqueId = null;
	public function getModelClass(){
		return $this->_modelName;
	}
	public function offsetExists($offset){
		return in_array($offset, $this->getFieldNames());
	}
	public function __toString(){
		return $this->getUniqueId();
	}
	public function getTableName(){
		$tableName = storageRegistry::getInstance()->modelSettings[$this->_modelName]['table'];
		return is_string($tableName)?$tableName:false;
	}
	public function offsetGet($offset){
		return new modelField($this, $offset);
	}
	public function offsetSet($offset, $value){
		
	}
	public function offsetUnset($offset){
		
	}
	public function __get($name){
		$fields = $this->getHelper()->getFieldNames();
		if (isset($fields[$name])){
			return new modelField($this, $fields[$name]);
		}
		return null;
	}
	public function __set($name, $value){
		
	}
	public function select(){
		$args = func_get_args();
		array_unshift($args, $this);
		$result = new modelResultSet();
		call_user_func_array(array($result, 'select'), $args);
		return $result;
	}
	public function getFieldNames(){
		return $this->getHelper()->getFieldNames();
	}
	public function getForeignKeys(){
		return $this->getHelper()->getForeignKeys(); 
	}
	public function getStorage(){
		return $this->getHelper()->getStorage();
	}
	public function getConnection(){
		return $this->getStorage()->getConnection();
	}
	public function getUniqueId(){
		if ($this->_uniqueId === null){
			$this->_uniqueId = kanon::getUniqueId();
		}
		return $this->_uniqueId;
	}
	private function __construct($modelName){
		$this->_modelName = $modelName;
	}
	/**
	 * @return model
	 */
	public function getHelper(){
		return new $this->_modelName;
		if ($this->_helper === null){
			$this->_helper = new $this->_modelName;
		}
		return $this->_helper;
	}
	public static function getInstance($modelName){
		if (!isset(self::$_instances[$modelName])){
			self::$_instances[$modelName] = new self($modelName);
		}
		return self::$_instances[$modelName];
	}
}

abstract class control{
	protected $_prefix = null; // <input name="prefix_control_name" />
	protected $_name = null; // <input name="control_name" />
	protected $_key = null; // <input name="control_name[key]" />
	protected $_value = null;
	protected $_defaultValue = null;
	protected $_required = false;
	// basic decorations
	protected $_title = '';
	// control set adapter
	protected $_controlSet = null;
	protected $_item = null;
	protected $_jsOnChangeCallback = '';
	protected $_options = array();
	protected $_repeatable = false; // name="name[]"
	protected $_inputCssClass = 'text';
	protected $_labelCssClass = 'text';
	protected $_afterTitle = '';
	
	protected $_property = null;
	//protected $_dataSources = array('GET', 'POST'); // GET/POST/FILES
	public function setOptions($options = array()){
		foreach ($options as $k => $v) $this->_options[$k] = $v;
	}
	public function setInputCssClass($cssClass){
		$this->_inputCssClass = $cssClass;
	}
	public function getInputCssClass(){
		if (isset($this->_options['inputCssClass'])){
			return $this->_options['inputCssClass'];
		}
		return $this->_inputCssClass;
	}
	public function setLabelCssClass($cssClass){
		$this->_labelCssClass = $cssClass;
	}
	public function getLabelCssClass(){
		if (isset($this->_options['labelCssClass'])){
			return $this->_options['labelCssClass'];
		}
		return $this->_labelCssClass;
	}
	public function __construct($controlName, $manualOnConstruct = false){
		$this->_name = $controlName;
		if (!$manualOnConstruct) $this->onConstruct();
	}
	public function onConstruct(){
	}
	public function error($errorString){
		if ($this->_controlSet !== null){
			$this->_controlSet->error($errorString);
		}
	}
	public function setControlSet($controlSet){
		$this->_controlSet = $controlSet;
		$this->setItem($this->_controlSet->getItem());
	}
	/**
	 * @return AControlSet
	 */
	public function getControlSet(){
		return $this->_controlSet;
	}
	public function setItem($item){
		$this->_item = $item;
	}
	public function setNote($note){
		$this->_note = $note;
	}
	public function getItem(){
		return is_object($this->_item)?$this->_item:false;
	}
	public function getItemPrimaryKey(){
		if ($item = $this->getItem()){
			return $item->primaryKey();
		}
		return false;
	}
	public function setRepeatable($repeatable = true){
		$this->_repeatable = $repeatable;
	}
	public function getRepeatable(){
		return $this->_repeatable;
	}
	public function setRequired($required){
		$this->_required = $required;
	}
	public function _setValue($value){
		$this->_value = $value;
		$this->onChange();
	}
	public function onChange(){
	}
	public function setDefaultValue($value){
		$this->_defaultValue = $value;
	}
	public function _getValue(){
		return ($this->_value === null?$this->_defaultValue:$this->_value);
	}
	public function setKey($key){
		$this->_key = $key;
	}
	public function getPostKey(){
		return $this->_key;
	}
	public function setPrefix($prefix){
		$this->_prefix = $prefix;
	}
	public function setName($name){
		$this->_name = $name;
	}
	public function getPostName(){ // for $_POST / $_FILES
		return ($this->_prefix===null?'':$this->_prefix.'_').$this->_name;
	}
	public function getName(){ // for Html
		return $this->getPostName().($this->getRepeatable()?'[]':($this->getPostKey()===null?'':'['.$this->getPostKey().']'));
	}
	public function setTitle($title){
		$this->_title = $title;
	}
	public function getTitle(){
		return $this->_title;
	}
	public function getNote(){
		if ($this->_note == '') return '';
		return ' '.$this->_note.'';
		return '';
	}
	public function getId(){
		return $this->getRepeatable()?false:($this->getPostName().($this->getPostKey()===null?'':'_'.$this->getPostKey().''));
	}
	// init
	public function isValidValue(){
		if ($this->_required && ($this->getValue() == '')){
			$this->error('Не заполнено поле "'.$this->getTitle().'"');
			return false;
		}
		return true; // always valid
	}
	public function isUpdated(){
		return $this->_isUpdated;
	}
	public function isRequired(){
		return $this->_required;
	}
	public function getPostKeys(){
		$name = $this->getPostName();
		if (isset($_POST[$name])){
			if (is_array($_POST[$name])){
				$keys = array_keys($_POST[$name]);
				return $keys;
			}else{
				return array(null);
			}
		}
		if (isset($_FILES[$name])){
			if (is_array($_FILES[$name]['tmp_name'])){
				$keys = array_keys($_FILES[$name]['tmp_name']);
				foreach ($keys as $k => $key){
					if ($_FILES[$name]['error'][$key] != UPLOAD_ERR_OK){
						unset($keys[$k]);
					}
				}
				return $keys;
			}else{
				if ($_FILES[$name]['error'] == UPLOAD_ERR_OK){
					return array(null);
				}
			}
		}
		return array();
	}
	public function inPost($key = null){
		return in_array($key, $this->getPostKeys());
	}
	public function fill($key){
		$this->_key = $key;
		$this->fillFromPost();
	}
	public function fillFromPost(){
		$name = ($this->_prefix===null?'':$this->_prefix.'_').$this->_name;
		if ($this->_key === null){
			if (isset($_POST[$name])){
				$this->setValue($_POST[$name]);
				return;
			}
		}else{
			if (isset($_POST[$name]) && isset($_POST[$name][$this->_key])){
				$this->setValue($_POST[$name][$this->_key]);
				return;
			}
		}
		//$this->setValue('');
	}
	public function beforeSave(){
	}
	public function afterSave(){
	}
	// html
	public function getIdHtml(){
		return $this->getId()?' id="'.$this->getId().'"':'';
	}
	public function getRowHtml(){
		return '<tr><td valign="top" class="label">'.($this->getId()?'<label class="'.$this->getLabelCssClass().'" for="'.$this->getId().'">':'').
		((strlen($this->getTitle()) || $this->isRequired())?
		'<span'.($this->_required?' title="Обязательно к заполнению"':'').'>'.$this->getTitle().($this->_required?' <b style="color: #f00;">*</b>':'').$this->_afterTitle.'</span>':'')
		.'</td><td>'.$this->getHtml().$this->getNote().''.($this->getId()?'</label>':'').'</td></tr>';
	}
	public function getHtml(){
		return '<input type="text"'.$this->getIdHtml().' class="'.$this->getInputCssClass().'" name="'.$this->getName().'" value="'.htmlspecialchars($this->getValue()).'" />';
	}
	
	
	
	
	

	public function setProperty($property){
		if (!is_object($property)){
			throw new Exception($property);
		}
		$this->_property = $property;
		if ($this->_value !== null){
			$this->_property->setValue($this->_value);
		}
		if ($property->getControl() === null){
			$property->setControl($this);
		}
	}
	public function getProperty(){
		return $this->_property;
	}
	public function setValue($value){
		$property = $this->getProperty();
		if ($property !== null && is_object($property)){
			$property->setValue($value);
		}else{
			$this->_setValue($value);
		}
		$this->onChange();
	}
	/**
	 * Prepare for DB
	 */
	public function importPropertyValue($propertyValue){
		$this->setValue($value);
	}
	public function exportValueToProperty(){
		return $this->getValue();
	}
	
	public function getValue(){
		$property = $this->getProperty();
		if ($property !== null && is_object($property)){
			$value = $property->getValue(false);
			if ($value !== null){
				return $value;
			}else{
				// default value of input is preffered
				if ($this->_defaultValue !== null){
					return $this->_defaultValue;
				}
				$propertyDefault = $property->getDefaultValue();
				if ($propertyDefault !== null){
					return $propertyDefault;
				}
				
			}
		}
		return $this->_getValue();
	}
	public function preSave(){}
	public function preInsert(){}
	public function preUpdate(){}
	public function preDelete(){}
	public function postSave(){}
	public function postInsert(){}
	public function postUpdate(){}
	public function postDelete(){}
}


abstract class controlSet{
	protected $_controls;
	protected $_classesMap = array(); // controlName => class
	protected $_titles = array(); // controlName => title
	protected $_required = array(); // controlName => required
	protected $_propertiesMap = array(); // controlName => propertyName
	protected $_options = array(); // control options
	protected $_errors;
	protected $_prefix = null;
	protected $_item = null;
	protected $_itemTemplate = null;
	protected $_hiddenControls = array();
	protected $_repeat = false;
	protected $_isUpdated = false;
	//===================================================================== getters && setters / options
	public function setOptions($options = array()){
		foreach ($options as $k => $v) $this->_options[$k] = $v;
	}
	public function hideControl($controlName){
		$this->_hiddenControls[$controlName] = true;
	}
	public function showControl($controlName){
		unset($this->_hiddenControls[$controlName]);
	}
	public function setRepeat($repeat = true){
		$this->_repeat = $repeat;
	}
	public function getRepeat(){
		return $this->_repeat;
	}
	public function isUpdated(){
		return $this->_isUpdated;
	}	
	public function setItemUpdated($updated = true){
		$this->_isUpdated = true;
	}	
	public function setItem($item){
		$this->_item = $item;
	}
	public function getItem(){
		return $this->_item;
	}
	public function setClasses($classes){
		$this->_classesMap = $classes;
	}
	public function setClass($controlName, $className){
		$this->_classesMap[$controlName] = $className;
	}
	public function setProperties($properties){
		$this->_propertiesMap = $properties;
	}
	public function setTitles($titles){
		foreach ($titles as $controlName => $title){
			$this->getControl($controlName)->setTitle($title);
		}
	}
	/**
	 * @return AControl
	 */
	public function getControl($controlName){
		if (!isset($this->_controls[$controlName])){
			if (!isset($this->_classesMap[$controlName])){
				return null;
			}
			$class = $this->_classesMap[$controlName];
			/** @var AControl */
			$control = new $class($controlName, true);
			$control->setControlSet($this);
			$control->setPrefix($this->_prefix);
			if (isset($this->_options[$controlName])) $control->setOptions($this->_options[$controlName]);
			$this->_controls[$controlName] = $control;
			if (isset($this->_propertiesMap[$controlName])){
				$propertyName = $this->_propertiesMap[$controlName];
				if ($this->_item !== null){
					$control->setProperty($this->_item->{$propertyName});
				}
			}
			if (isset($this->_titles[$controlName])){
				$title = $this->_titles[$controlName];
				$control->setTitle($title);
			}
			if (isset($this->_notes[$controlName])){
				$note = $this->_notes[$controlName];
				$control->setNote($note);
			}
			if (isset($this->_required[$controlName])){
				$required = $this->_required[$controlName];
				$control->setRequired($required);
			}
			$control->setRepeatable($this->getRepeat()?true:false);
			$control->onConstruct();
		}
		return $this->_controls[$controlName];
	}
	public function resetControls(){
		$this->_controls = array();
	}
	public function save(){
		if ($this->getItem() !== null){
			$result = $this->getItem()->save();
			//var_dump($result);
			return $result;
		}
	}
	public function error($errorString){
		$this->_errors[] = $errorString;
	}
	public function getErrors(){
		return $this->_errors;
	}
	public function setKey($key){
		foreach ($this->_classesMap as $controlName => $class){
			$control = $this->getControl($controlName);
			$control->setKey($key);
		}
	}
	//===================================================================== processing POST
	/**
	 * Get keys array for POST and FILES
	 */
	public function getPostKeys(){
		$keys = array();
		foreach ($this->_classesMap as $controlName => $class){
			if (!isset($this->_hiddenControls[$controlName])){
				$controlKeys = $this->getControl($controlName)->getPostKeys();
				if (count($controlKeys)){
					$keys = array_unique(array_merge($keys, $controlKeys));
				}
			}
		}
		//echo 'Keys:<br />';
		//var_dump($keys);
		if (!count($keys)) return false;
		
		return $keys;
	}
	public function inPost($key = null){
		foreach ($this->_classesMap as $controlName => $class){
			if (!isset($this->_hiddenControls[$controlName])){
				if (($foundKey = $this->getControl($controlName)->inPost($key)) !== false){
					return $foundKey;
				}
			}
		}
		return false;
	}
	public function fillFromPost($key = null){
		foreach ($this->_classesMap as $controlName => $class){
			if (!isset($this->_hiddenControls[$controlName])){
				$control = $this->getControl($controlName);
				$control->setKey($key);
				$control->fillFromPost();
			}
		}
	}
	public function isValidValues(){
		foreach ($this->_classesMap as $controlName => $class){
			if (!isset($this->_hiddenControls[$controlName])){
				if (!$this->getControl($controlName)->isValidValue()) {
					return false;
				}
			}
		}
		return true;
	}
	public function beforeSave(){
		foreach ($this->_classesMap as $controlName => $class){
			if (!isset($this->_hiddenControls[$controlName])){
				$control = $this->getControl($controlName);
				$control->beforeSave();
			}
		}
	}
	public function afterSave(){
		foreach ($this->_classesMap as $controlName => $class){
			if (!isset($this->_hiddenControls[$controlName])){
				$control = $this->getControl($controlName);
				$control->afterSave();
			}
		}
	}
	public function checkPost($key = null){
		$this->fillFromPost($key);
		if ($this->isValidValues()){
			$this->beforeSave();
			if ($this->save()){
				$this->afterSave();
				$this->setItemUpdated(true);
			}
		}
	}
	public function setItemTemplate($itemTemplate){
		$this->_itemTemplate = $itemTemplate;
	}
	public function getItemTemplate(){
		return clone $this->_itemTemplate;
	}
	public function process(){
		//echo 'Process<br />';
		$this->processPost();
	}
	public function processPost(){
		if ($keys = $this->getPostKeys()){
			if (is_array($keys) && count($keys)){
				foreach ($keys as $key){
					if (is_object($this->_itemTemplate)){
						$this->resetControls();
						$this->setItem($this->getItemTemplate());
					}
					$this->checkPost($key);
				}
			}
		}
	}

	//===================================================================== output HTML
	public function getTableRowsHtml($key = null){
		$h = '';
		$this->setKey($key);
		foreach ($this->_classesMap as $controlName => $class){
			if (!isset($this->_hiddenControls[$controlName])){
			$control = $this->getControl($controlName);
			$h .= $control->getRowHtml();
			}
		}
		return $h;
	}
	public function getTableHtml($key = null){
		$h = '';
		$h .= '<table>';
		$rh = $this->getTableRowsHtml($key);
		$repeat = 1;
		if ($this->getRepeat()) $repeat = $this->getRepeat();
		$h .= str_repeat($rh, $repeat);
		$h .= '</table>';
		return $h;
	}
	public function getHtml($key = null){
		return $this->getTableHtml($key);
	}
	public function getFormHtml($key = null){
		return 
		(count($this->getErrors())?'<div class="errors"><ul><li>'.implode("</li><li>", $this->getErrors()).'</li></ul></div>':'').
		'<form method="post" enctype="multipart/form-data" action="">'.$this->getHtml($key).'<input type="submit" value="Сохранить" /></form>';
	}
}


class selectControl extends control{
	protected $_controlType = 'select'; // select, checkbox, radio
	protected $_multiple = false;
	protected $_options = array();
	protected $_invalidOptions = array();
	public function setControlType($controlType){
		$this->_controlType = $controlType;
	}
	public function setMultiple($multiple){
		$this->_multiple = $multiple;
	}
	public function setOptions($options){
		$this->_options = $options;
	}
	public function importPropertyValue($value){
		switch ($this->_controlType){
			case 'checkbox':
				if (strlen($value)){
					$values = explode('//', substr($value, 1, strlen($value)-2));
				}else{
					$values = array();
				}
				$this->setValue($values);
				break;
			case 'radio':
			case 'select':
			default:
				$this->setValue($value);
				break;
		}
	}
	public function exportValueToProperty(){
		switch ($this->_controlType){
			case 'checkbox':
				$values = $this->getValue();
				return '/'.implode('//', $values).'/';
				break;
			case 'radio':
			case 'select':
			default:
				return $this->getValue();
				break;
		}
	}
	public function isValidValue(){
		$values = array();
		if ($this->_controlType == 'checkbox'){
			$values = $this->getValue();
		}else{
			$values[] = $this->getValue();
		}
		//var_dump($values);
		foreach ($values as $value){
			if (in_array($value, $this->_invalidOptions)){
				$this->error('Неверно заполнено поле "'.$this->getTitle().'"');
				return false;
			}
		}
		foreach ($values as $value){
			if (!isset($this->_options[$value])){
				$this->error('Неверно заполнено поле "'.$this->getTitle().'"');
				return false;
			}
		}
		return true;
	}
	public function getHtml(){
		//'.$this->getValue().'
		$selectedId = $this->getValue();
		$options = array();
		$onchangehtml = '';
		if ($this->_jsOnChangeCallback !== ''){
			$onchangehtml .= ' onChange="'.$this->_jsOnChangeCallback.'"';
		}
		foreach ($this->_options as $optionId => $optionTitle){
			switch ($this->_controlType){
				case 'checkbox':
					if (!is_array($selectedId)) $selectedId = array();
					$options[] = '<input class="checkbox" name="'.$this->getName().'[]" type="checkbox" id="'.$this->getId().'_'.$optionId.'" value="'.$optionId.'"'.(in_array($optionId,$selectedId)?' checked="checked"':'').'><label for="'.$this->getId().'_'.$optionId.'">'.htmlspecialchars($optionTitle).'</label>';
					break;
				case 'radio':
					$options[] = '<input name="'.$this->getName().'" type="radio" id="'.$this->getId().'_'.$optionId.'" value="'.$optionId.'"'.($selectedId==$optionId?' checked="checked"':'').'><label for="'.$this->getId().'_'.$optionId.'">'.htmlspecialchars($optionTitle).'</label>';
					break;
				case 'select':
				default:
					$options[] = '<option value="'.$optionId.'"'.($selectedId==$optionId?' selected="selected"':'').'>'.htmlspecialchars($optionTitle).'</option>';
					break;
			}
		}
		switch ($this->_controlType){
			case 'checkbox':
			case 'radio':
				return '<div id="'.$this->getId().'_wrap"><div>'.implode('</div><div>',$options).'</div></div>';
				break;
			case 'select':
			default:
				return '<div id="'.$this->getId().'_wrap" class="select"><select'.$onchangehtml.' id="'.$this->getId().'" name="'.$this->getName().'">'.implode('',$options).'</select></div>';
				break;
		}

		//'.($this->_onChangeCallback ==''?'':' onChange="'.$this->_onChangeCallback.'"').'
		//return '<select></select>';
	}
}


class fileInput extends control{
	protected $_files = array();
	protected $_filesPrefix = '';
	protected function _getPath(){
		return $this->_options['upload_path'];
	}
	protected function _files(){
		$files = array();
		foreach ($_FILES as $k => $f){
			$files[$k] = array();
			foreach ($f as $fk => $a){
				if (is_array($a)){
					$files[$k] = $this->_arrayAddLastKey($files[$k], $a, $fk);
				}else{
					$files[$k][$fk] = $a;
				}
			}
		}
		return $files;
	}
	protected function _arrayAddLastKey($target, $a, $lastKey){ // tmp_name => ds =>fd
		foreach ($a as $k => $v){
			if (is_array($v)){
				$target[$k] = $this->_arrayAddLastKey($target[$k], $v, $lastKey);
			}else{
				$target[$k][$lastKey] = $v;
			}
		}
		return $target;
	}
	protected function _saveFile($tmpName, $name){
		$ext = '.dat';
		if ($dotpos = strrpos($name, ".")){
			$ext = substr($name, $dotpos);
		}
		$fileName = false;
		if (!is_file($tmpName)) return false;
		$path = realpath($this->_getPath());
		if ($pk = $this->getItemPrimaryKey()){
			$fileName = $path.'/'.$this->_filesPrefix.$pk.$ext;
		}else{
			return false;
		}
		if ($fileName == ''){
			$fileName = $this->_tempnam($path, $this->_filesPrefix, $ext);
		}
		if ($fileName){
			if (copy($tmpName, $fileName)){
				
				return basename($fileName);
			}
		}
		return false;
	}
	public function beforeSave(){
	}
	public function afterSave(){
		$files = $this->_files();
		$name = $this->getPostName();
		if (!isset($files[$name])) {
			return;
		}
		$key = $this->getPostKey();
		if ($key === null){
			$file = $files[$name];
		}else{
			if (!isset($files[$name][$key])) return;
			$file = $files[$name][$key];
		}
		if (isset($file['tmp_name'])){
			if ($fileName = $this->_saveFile($file['tmp_name'], $file['name'])){
				$this->setValue($fileName);
				if ($property = $this->getProperty()){
					if ($property instanceof imageFilenameProperty){
						$property->unlinkThumbs();
					}
				}
				if ($item = $this->getItem()){
					$item->save();
				}
			}
		}
	}
	public function fillFromPost(){
	}
	public function getHtml(){
		$previewHtml = '';
		if (isset($this->_options['show_preview']) && ($this->_options['show_preview'] == true)){
			if ($property = $this->getProperty()){
				if ($property instanceof imageFilenameProperty){
					$previewHtml = $property->html(100);
				}else{
					$previewHtml = $property->html();
				}
			}
		}
		return '<div>'.$previewHtml.'</div><input class="file" type="file"'.$this->getIdHtml().' name="'.$this->getName().'" value="'.$this->getValue().'" />';
	}
}


class textInput extends control{
}


class textarea extends textInput{
	public function getHtml(){
		$style = array();
		if (isset($this->_options['width'])){
			$style[] = 'width: '.$this->_options['width'];
		}
		if (isset($this->_options['height'])){
			$style[] = 'height: '.$this->_options['height'];
		}
		$ss = count($style)?' style="'.implode(";", $style).'"':'';
		return '<textarea id="'.$this->getId().'" class="'.$this->getInputCssClass().'" name="'.$this->getName().'"'.$ss.'>'.$this->getValue().'</textarea>';
	}
}


class passwordInput extends textInput{
	public function getHtml(){
		return '<input type="password" autocomplete="off"'.$this->getIdHtml().' class="'.$this->getInputCssClass().'" name="'.$this->getName().'" value="'.$this->getValue().'" />';
	}
}


class htmlTextarea extends textarea{
	protected $_inputCssClass = 'htmlarea';
	public function getHtml(){
		$html = parent::getHtml();
		$config = '';
		if (isset($this->_options['filemanager_url'])){
			$url = $this->_options['filemanager_url'];
			$config  = '<script type="text/javascript">var '.$this->getId().'_filemanager = "'.$url.'";</script>';
		}
		return $html.$config;
	}
}


class checkboxInput extends control{ // 0/1 values
	protected $_inputCssClass = 'checkbox';
	public function getHtml(){
		return '<div style="margin: 5px 0;"><input type="hidden" name="'.$this->getName().'" value="0" />'.
		'<input type="checkbox"'.$this->getIdHtml().' class="'.$this->getInputCssClass().'" name="'.$this->getName().'" value="1"'.($this->getValue()?' checked="checked"':'').' /></div>';
	}
}

/**
 * $Id$
 */

class controllerPrototype{
	protected $_me = null; // ReflectionClass
	protected $_parent = null;
	protected $_baseUri = null;
	protected $_relativeUri = null;
	protected $_childUri = '';
	protected $_action = '';
	protected $_options = array();
	public function __construct(){
		$this->_baseUri = uri::fromString('/');
		$this->_relativeUri = uri::fromRequestUri();
		$this->_me = new ReflectionClass(get_class($this));
	}
	/**
	 * Executing before run()
	 */
	public function onConstruct(){

	}
	/**
	 * Set parent controller
	 * @param controllerPrototype $parentController
	 */
	public function setParent($parentController){
		$this->_parent = $parentController;
	}
	/**
	 * Get parent controller
	 */
	public function getParent(){
		return $this->_parent;
	}
	/**
	 * Get current domain name without www
	 */
	public function getDomainName(){
		return uri::getDomainName();
	}
	/**
	 * Source for <link rel="canonical" href="" />
	 */
	public function getCanonicalUrl(){
		return 'http://'.$this->getDomainName().''.$this->rel("$this->_relativeUri"); // quotes required to not overwrite _relativeUri
	}
	/**
	 * Get current url excluding query
	 */
	public function getCurrentUrl(){
		return 'http://'.$_SERVER['SERVER_NAME'].''.reset(explode("?",$_SERVER['REQUEST_URI']));
	}
	public function setOptions($options = array()){
		$this->_options = $options;
	}
	/**
	 * Get $_SERVER['REQUEST_METHOD']
	 */
	public function getHttpMethod(){
		return $_SERVER['REQUEST_METHOD'];
	}
	public function setBaseUri($uriString, $autoRel = true){
		$this->_baseUri = uri::fromString($uriString);
		if ($autoRel){
			$this->_relativeUri = uri::fromRequestUri();
			$this->_relativeUri->subtractBase($this->_baseUri);
		}
	}

	public function setRelativeUriFromBase($uriString){
		$baseUri = uri::fromString($uriString);
		$this->_relativeUri = uri::fromRequestUri();
		$this->_relativeUri->subtractBase($baseUri);
	}
	/**
	 * Last method to run if another methods not found
	 * @param string $action
	 */
	protected function _action($action){
		return $this->notFound('Directory "'.$action.'" not found in '.get_class($this).'');
	}
	/**
	 * Get url relative to this controller (combine with controller's base uri)
	 * @param string|uri $relativeUri
	 * @param boolean $includeAction
	 * @return uri
	 */
	public function rel($relativeUri = '', $includeAction = false){
		if (is_string($relativeUri)) $relativeUri = uri::fromString($relativeUri);
		$a = array();
		if ($includeAction) $a[] = $this->_action;
		$relativeUri->setPath(array_merge($this->_baseUri->getPath(),$a,$relativeUri->getPath()));
		return $relativeUri;
	}
	/**
	 * Redirect with custom HTTP code
	 * @param string $message
	 */
	protected function _redirect($url = null, $httpCode = 303){
		//echo '<a href="'.$url.'">'.$url.'</a>';
		//exit;
		$title = 'Переадресация';
		if (!preg_match("#^[a-z]+:#ims",$url)){
			if (!preg_match("#^/#ims",$url)){
				$url = $this->rel($url, true);
			}
			$url = 'http://'.$this->getDomainName().$url;
		}
		$wait = 0;
		header("Location: ".$url, true, $httpCode);
		//header($_SERVER['SERVER_PROTOCOL']." 303 See Other");
		header("Content-type: text/html; charset=UTF-8");
		echo '<html><head>';
		echo '<title>'.$title.'</title>';
		echo '</head><body onload="doRedirect()" bgcolor="#ffffff" text="#000000" link="#0000cc" vlink="#551a8b" alink="#ff0000">';
		echo '<noscript>';
		echo '<meta http-equiv="refresh" content="'.$wait.'; url=&#39;'.htmlspecialchars($url).'&#39;">';
		echo '</noscript>';
		echo '<p><font face="Arial, sans-serif">Подождите...</font></p>';
		echo '<p><font face="Arial, sans-serif">Если переадресация не сработала, перейдите по <a href="'.$url.'">ссылке</a> вручную.</font></p>';
		echo '<script type="text/javascript" language="javascript">';
		echo 'function doRedirect() {';
		if (!$wait)	echo 'location.replace("'.$url.'");';
		echo '}';
		echo '</script>';
		echo '</body></html>';
		exit;
	}
	/**
	 * Exit with HTTP 403 error code
	 * @param string $message
	 */
	public function forbidden($message = ''){
		header($_SERVER['SERVER_PROTOCOL']." 403 Forbidden");
		echo $message;
		exit;
	}
	/**
	 * Exit with HTTP 404 error code
	 * @param string $message
	 */
	public function notFound($message = ''){
		header($_SERVER['SERVER_PROTOCOL']." 404 Not found");
		echo '<title>Страница не найдена</title>';
		echo '<body>'.$message.'</body>';
		// Google helper:
		/*echo '<script type="text/javascript">
		 var GOOG_FIXURL_LANG = "ru";
		 var GOOG_FIXURL_SITE = "http://'.$this->getDomainName().'";
		 </script>
		 <script type="text/javascript" src="http://linkhelp.clients.google.com/tbproxy/lh/wm/fixurl.js">
		 </script>';*/
		echo str_repeat('&nbsp; ', 100); // required to display custom error message (IE, Chrome)
		exit;
	}
	/**
	 * Redirect with HTTP 301 "Moved Permanently" code
	 * @param string $message
	 */
	public function movedPermanently($url){
		$this->_redirect($url, 301);
	}
	/**
	 * Redirect with HTTP 303 "See Other" code
	 * @param string $message
	 */
	public function seeOther($url){
		$this->_redirect($url, 303);
	}
	/**
	 * Redirect with HTTP 303 "See Other" code
	 * This is a recommended method to redirect after POST
	 * @param string $message
	 */
	public function redirect($url){
		$this->_redirect($url, 303);
	}
	/**
	 * Redirect to previous page
	 */
	function back(){
		$this->redirect($_SERVER['HTTP_REFERER']);
	}
	/**
	 * Deprecated
	 * @param string $message
	 */
	protected function _show403($message = ''){
		$this->forbidden($message);
	}
	/**
	 * Deprecated
	 * @param string $message
	 */
	protected function _show404($message = ''){
		$this->notFound($message);
	}
	protected function _header(){
		if ($c = $this->getParent()){
			if ($c->getParent()) echo "\r\n".'<div class="'.get_class($c).'_wrapper">';
			$c->_header();
		}
		$this->header();
		echo "\r\n".'<div class="'.get_class($this).'_content">';
	}
	protected function _footer(){
		echo "\r\n".'</div>';
		$this->footer();
		if ($c = $this->getParent()){
			$c->_footer();
			if ($c->getParent()) echo "\r\n".'</div>';
		}
	}
	public function header(){
	}
	public function _initIndex(){
	}
	public function index(){
	}
	public function footer(){
	}
	/**
	 * Get arguments for method $methodName from $predefinedArgs, $_GET, $_POST arrays, default value or setting them null.
	 * @param string $methodName
	 * @param array $predefinedArgs
	 * @return array
	 */
	protected function _getArgs($methodName, $predefinedArgs = array()){
		$method = $this->_me->getMethod($methodName);
		$parameters = $method->getParameters();
		$args = array();
		foreach ($parameters as $p){
			$name = $p->getName();
			if (isset($predefinedArgs[$name])){
				$value = $predefinedArgs[$name];
			}elseif (isset($_GET[$name])){
				$value = $_GET[$name];
			}elseif (isset($_POST[$name])){
				$value = $_POST[$name];
			}else{
				$value = $p->isDefaultValueAvailable()?$p->getDefaultValue():null;
			}
			$args[] = $value;//$name
		}
		return $args;
	}
	/**
	 * Make $this->_childUri from $this->_baseUri + $actions
	 * @param array $actions
	 * @return controllerPrototype
	 */
	protected function _makeChildUri($actions){
		$childUri = clone $this->_baseUri;
		$path = $childUri->getPath();
		foreach ($actions as $action){
			$path[] = $action;
		}
		$childUri->setPath($path);
		$this->_childUri = strval($childUri);
		return $this;
	}
	/**
	 * Forward action to another controller
	 * @param string $controllerClass
	 * @param string $relativePath
	 * @param array $options
	 */
	public function forwardTo($controllerClass, $relativePath = '', $options = array()){
		$controller = new $controllerClass();
		$controller->setParent($this);
		$controller->setBaseUri($this->rel($relativePath), false);
		$controller->setRelativeUriFromBase($this->_baseUri);
		$controller->setOptions($options);
		if (method_exists($controller, 'customRun')){
			$controller->customRun();
		}else{
			$controller->run();
		}
		exit;
	}
	/**
	 * Run another controller
	 * @param string $controllerClass
	 * @param array $options
	 */
	public function runController($controllerClass, $options = array()){
		$controller = new $controllerClass();
		$controller->setParent($this);
		$controller->setBaseUri($this->_childUri);
		$controller->setOptions($options);
		if (method_exists($controller, 'customRun')){
			$controller->customRun();
		}else{
			$controller->run();
		}
		exit;
	}
	/**
	 * Get route from docComments if possible
	 * @param $uri
	 * @param string $prefix
	 * @return array|false
	 */
	protected function _getRouteMethod($uri, $prefix = '!Route'){
		$this->_me = new ReflectionClass(get_class($this));
		$methods = $this->_me->getMethods();
		$maxIdentWeight = 0;
		$maxLength = 0;
		$result = false;
		//var_dump($methods);
		foreach ($methods as $method){
			//var_dump($method);
			//$method = $this->_me->getMethod($methodName);
			if ($doc = $method->getDocComment()){
				// 1. expand comment
				$doc = trim(preg_replace("#/\*\*(.*)\*/#ims", "\\1", $doc));
				// 2. search for !Route
				$la = explode("\n", $doc);
				$routes = array();
				foreach ($la as $line){
					if (($pos = strpos($line, $prefix)) !== false){
						$routes[] = substr($line, $pos + strlen($prefix) + 1);
					}
				}
				foreach ($routes as $route){
					$httpMethod = reset(explode(" ", $route));
					$route = trim(substr($route, strlen($httpMethod)));
					if ($httpMethod == $this->getHttpMethod() || strtoupper($httpMethod) == 'ANY'){
						//var_dump($route);
						$routePath = explode("/", $route);
						$path = $uri->getPath();
						$identical = false;
						$actions = array();
						$args = array();
						$identWeight = 0;
						$length = count($routePath);
						if (count($path) >= count($routePath)){
							$identical = true;
							foreach ($routePath as $rdir){
								$dir = array_shift($path);
								$rdir = array_shift($routePath);
								$actions[] = $dir;
								if (substr($rdir,0,1) != '$'){
									if ($dir != $rdir){
										$identical = false;
									}else{
										$identWeight++;
									}
								}else{
									$argName = substr($rdir,1);
									$args[$argName] = $dir;
								}
							}
						}
						$use = false;
						if ($identWeight > $maxIdentWeight){ // more identical directories
							$use = true;
						}else{
							if ($length > $maxLength){ // more variables
								$use = true;
							}
						}
						if ($identical && $use){
							//
							$maxIdentWeight = max($identWeight,$maxIdentWeight);
							$maxLength = max($length,$maxLength);
							$result = array($actions, $method->getName(), $args);
						}
						//var_dump($identical);
					}
				}
			}
		}
		return $result;
	}
	/**
	 * Run controller - select methods and run them
	 */
	public function run(){
		$methodFound = false;
		$class = get_class($this);
		if (strlen($this->_relativeUri) > 1){ // longer than "/"
			if ($this->getCurrentUrl() != $this->getCanonicalUrl()){
				$this->movedPermanently($this->getCanonicalUrl());
			}
		}
		if ($action = $this->_relativeUri->getBasePath()){
			$this->_action = $action;
		}
		$this->onConstruct();
		if (list($actions, $methodName, $pathArgs) = $this->_getRouteMethod($this->_relativeUri, '!RouteInit')){
			$this->_makeChildUri($actions);
			if (method_exists($this, $methodName)){
				call_user_func_array(array($this, $methodName), $this->_getArgs($methodName, $pathArgs));
			}
		}
		if (list($actions, $methodName, $pathArgs) = $this->_getRouteMethod($this->_relativeUri, '!Route')){
			$this->_makeChildUri($actions);
			if (method_exists($this, $methodName)){
				$methodFound = true;
				$args = $this->_getArgs($methodName, $pathArgs);
				if ($this->getHttpMethod() == 'GET') $this->_header();
				call_user_func_array(array($this, $methodName), $args);
				if ($this->getHttpMethod() == 'GET') $this->_footer();
				return;
			}
		}

		if ($this->_action){
			$uc = ucfirst($this->_action);
			$this->_makeChildUri(array($action));
			$initFunction = 'init'.$uc;
			if (method_exists($this, $initFunction)){
				$methodFound = true;
				call_user_func_array(array($this, $initFunction), $this->_getArgs($initFunction));
			}
			$actionFunction = 'action'.$uc;
			if (method_exists($this, $actionFunction)){
				$methodFound = true;
				call_user_func_array(array($this, $actionFunction), $this->_getArgs($actionFunction));
			}
			$showFunction = 'show'.$uc;
			if (method_exists($this, $showFunction)){
				$methodFound = true;
				$this->_header();
				call_user_func_array(array($this, $showFunction), $this->_getArgs($showFunction));
				$this->_footer();
			}
			if (!$methodFound){
				return $this->_action($action);
			}
		}else{
			if (method_exists($this, 'customIndex')){
				$this->customIndex();
			}else{
				$this->_initIndex();
				$this->_header();
				$this->index();
				$this->_footer();
			}
		}
	}
}

/**
 * $Id$
 */


class controller extends controllerPrototype{
	protected $_startTime = null;
	public function __construct(){
		$this->_startTime = microtime(true);
		parent::__construct();
	}
	public function getRegistry(){
		return applicationRegistry::getInstance();
	}
	public function getApplication(){
		application::getInstance();
	}
	public function app(){
		return $this->getApplication();
	}
	/**
	 * Set base path for /images/, /css/ etc
	 * @param string $path
	 */
	public function setBasePath($path){
		$this->getRegistry()->basePath = $path;
		return $this;
	}
	public function getBasePath($path = null){
		if ($path !== null){
			return realpath($this->getBasePath().$path).'/';
		}
		if ($this->getRegistry()->basePath === null){
			return realpath(dirname(__FILE__).$this->_relativeBasePath).'/';
		}else{
			return realpath($this->getRegistry()->basePath).'/';
		}
	}
	/**
	 * Set html page <title>
	 * @param string $title
	 * @return controller
	 */
	public function setTitle($title){
		$this->getRegistry()->title = $title;
		return $this;
	}
	public function getTitle(){
		return $this->getRegistry()->title;
	}
	public function appendToBreadcrumb($links = array()){
		if (count($links)){
			if (!is_array($this->getRegistry()->breadcrumb)){
				$this->getRegistry()->breadcrumb = array();
			}
			foreach ($links as $link){
				$this->getRegistry()->breadcrumb[] = $link;
			}
		}
		return $this;
	}
	public function getBreadcrumb(){
		if (!is_array($this->getRegistry()->breadcrumb)){
			$this->getRegistry()->breadcrumb = array();
		}
		return $this->getRegistry()->breadcrumb;
	}
	public function viewBreadcrumb(){
		echo implode(" → ", $this->getBreadcrumb());
	}
	public function getUser(){
		return isset($_SESSION['site_user'])?$_SESSION['site_user']:null;
	}
	public function getUserId(){
		return is_object($this->getUser())?$this->getUser()->id->getValue():0;
	}
	public function requireCss($uri){
		if (!is_array($this->getRegistry()->cssIncludes)){
			$this->getRegistry()->cssIncludes = array();
		}
		$this->getRegistry()->cssIncludes[] = $uri;
	}
	public function css($cssString){
		$this->getRegistry()->plainCss .= $cssString;
	}
	public function requireJs($uri){
		if (!is_array($this->getRegistry()->javascriptIncludes)){
			$this->getRegistry()->javascriptIncludes = array();
		}
		$this->getRegistry()->javascriptIncludes[] = $uri;
	}
	public function getHeadContents(){
		$h = '<!DOCTYPE html>'; // html5
		$h .= '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
		$h .= '<title>'.$this->getTitle().'</title>';
		if (is_array($this->getRegistry()->cssIncludes)){
			foreach ($this->getRegistry()->cssIncludes as $url){
				$h .= '<link rel="stylesheet" type="text/css" href="'.$url.'" />';
			}
		}
		if (isset($this->getRegistry()->plainCss)){
			$h .= '<style type="text/css">';
			$h .= $this->getRegistry()->plainCss;
			$h .= '</style>';
		}
		if (is_array($this->getRegistry()->javascriptIncludes)){
			foreach ($this->getRegistry()->javascriptIncludes as $url){
				$h .= '<script type="text/javascript" src="'.$url.'"></script>';
			}
		}
		$h .= '<link rel="shortcut icon" href="/favicon.ico" />';
		return $h;
	}
	protected function &getDatabase($name = null){
		if ($name === null){
			return $this->getRegistry()->defaultDatabase;
		}
		if (!is_array($this->getRegistry()->databases)){
			$this->getRegistry()->databases = array();
		}
		return isset($this->getRegistry()->databases[$name])?$this->getRegistry()->databases[$name]:null;
	}

}

/**
 * $Id$
 */

class application extends frontController{
	private static $_selfInstance = null;
	private static $_instance = null;
	/*public static function __construct(){
		parent::__construct();


	}*/
	public static function getInstance($controllerClassName = null){
		/*if (self::$_selfInstance === null){
			self::$_selfInstance = new self();
				
			}*/
		header($_SERVER['SERVER_PROTOCOL']." 200 OK");
		header("Content-Type: text/html; charset=utf-8");
		@set_magic_quotes_runtime(false);
		frontController::startSession('.'.uri::getDomainName());
		if (get_magic_quotes_gpc()){
			frontController::_stripSlashesDeep($_GET);
			frontController::_stripSlashesDeep($_POST);
		}
		if (self::$_instance === null && $controllerClassName !== null){
			self::$_instance = new $controllerClassName();
		}
		return self::$_instance;
	}

}
function app(){
	return application::getInstance();
}

/**
 * $Id$
 */

class serviceController extends controller{
	/**
	 * Service-specific (REST for example) responce for unknown request
	 * 
	 */
	public function notFound($message =''){
		header($_SERVER['SERVER_PROTOCOL']." 500 Internal Server Error");
		header("Content-type: text/plain; charset=UTF-8");
		echo "Unknown request";
		exit;
	}
}

/**
 * $Id$
 */

class applicationRegistry extends registry{
	private static $_instance = null;
	private function __construct(){
		
	}
	public static function getInstance(){
		if (self::$_instance === null){
			self::$_instance = new self();
		}
		return self::$_instance;
	}
}

/**
 * $Id$
 */

class frontController extends controller{
	public static function startSession($domain, $expire = 360000) {
		session_set_cookie_params($expire, '/', $domain);
		@session_start();
		// Reset the expiration time upon page load
		if (isset($_COOKIE[session_name()])){
			setcookie(session_name(), $_COOKIE[session_name()], time() + $expire, "/", $domain);
		}
	}
	public static function _stripSlashesDeep(&$value){
		$value = is_array($value) ?
		array_map(array(self,'_stripSlashesDeep'), $value) :
		stripslashes($value);
		return $value;
	}

}
/*
 * $app = application::getInstance('/');
 * $app->run('/');
 */

//  implements IControllableProperty

class modelProperty{
	protected $_name = null;
	protected $_defaultValue = null;
	protected $_initialValue = null;
	protected $_value = null;
	protected $_options = array();
	protected $_model = null;
	/**
	 * @var IPropertyControl
	 */
	protected $_control = null;
	public function setOptions($options = array()){
		foreach ($options as $k => $v) $this->_options[$k] = $v;
	}
	public function __construct($propertyName){
		$this->_name = $propertyName;
		$this->onConstruct();
	}
	public function onConstruct(){
		
	}
	public function is($value){
		return new modelExpression($this, '=', $value);
	}
	public function lt($value){
		return new modelExpression($this, '<', $value);
	}
	public function gt($value){
		return new modelExpression($this, '>', $value);
	}
	public function in($value){
		return new modelExpression($this, 'IN', $value);
	}
	public function like($value){
		return new modelExpression($this, 'LIKE', $value);
	}
	public function setControl($controlClassName){
		$this->_control = new $controlClassName($this->_name);
		if ($this->_control->getProperty() === null){
			$this->_control->setProperty($this);
		}
	}
	public function setModel($model){
		$this->_model = $model;
	}
	/**
	 * @return model
	 */
	public function getModel(){
		return $this->_model;
	}
	public function getStorage(){
		return $this->getModel()->getStorage();
	}
	public function getControl(){
		return $this->_control;
	}
	public function getDefaultValue(){
		return $this->_defaultValue;
	}
	public function getInitialValue(){
		return $this->_initialValue;
	}
	public function getInternalValue($allowDefault = true){ // for sql SET
		return $this->_getInternalValue($allowDefault);
	}
	public function getValue($allowDefault = true){
		return $this->_getInternalValue($allowDefault);
	}
	protected function _getInternalValue($allowDefault = true){ // Template for both public and database variants
		if ($this->_value === null){
			if ($this->_initialValue === null){
				if ($allowDefault){
					return $this->_defaultValue;
				}
			}
			return $this->_initialValue;
		}
		return $this->_value;
	}
	public function setValue($value){
		$this->_value = $value;
	}
	public function setInitialValue($value){
		$this->_initialValue = $value;
	}
	public function isChangedValue(){
		return (($this->_value !== null) && ($this->_value != $this->_initialValue));
	}
	public function hasChangedValue(){
		return (($this->_value !== null) && ($this->_value != $this->_initialValue));
	}
	public function __toString(){
		return strval($this->getValue());
	}
	public function e(){
		return $this->getStorage()->quote($this->getValue());
	}
	public function html(){
		return htmlspecialchars($this->getValue());
	}
	// Storable value
	public function isValidValue($toSave = false){
		return true;
	}
	public function preSave(){
		if (!$this->isValidValue()){
			$this->setValue(null);
		}
	}
	public function preInsert(){}
	public function preUpdate(){}
	public function preDelete(){}
	public function postSave(){}
	public function postInsert(){}
	public function postUpdate(){}
	public function postDelete(){}
}

class modelIterator implements Iterator{
	private $_model = null;
	private $_classes = array();
	private $_iterator = null;
	public function __construct($model, $classes){
		$this->_model = $model;
		$this->_classes = $classes;
		$this->_iterator = new ArrayIterator($this->_classes); 
	}
	function rewind() {
        $this->_iterator->rewind();
    }
    function current() {
       $propertyName = $this->_iterator->key();
       return $this->_model->$propertyName;
    }
    function key() {
        return $this->_iterator->key();
    }
    function next() {
        $this->_iterator->next();
    }
    function valid() {
        return $this->_iterator->valid();;
    }
}


class stringProperty extends modelProperty{
}



class model implements ArrayAccess, IteratorAggregate{
	protected $_properties = array(); // array of modelProperty
	protected $_classes = array(); // propertyName => className
	protected $_fields = array(); // propertyName => fieldName
	protected $_primaryKey = array(); // propertyNames
	protected $_autoIncrement = null; // propertyName
	protected $_foreignKeys = array(); // property => array(foreignClass, foreignProperty)
	protected $_options = array(); // propertyName => options
	//protected $_storage = null;
	//protected $_storageClass = 'modelStorage';
	public function __sleep(){
		return array('_properties');//'_classesMap', '_fieldsMap', '_primaryKey', '_autoIncrement',
	}
	public function __wakeup(){}
	public static function getCollection(){
		if (!function_exists('get_called_class')){
			
			// PHP 5 >= 5.2.4
		}
		return modelCollection::getInstance(get_called_class()); // PHP 5 >= 5.3.0
	}
	public function getIterator(){
		return new modelIterator($this, $this->_classes);
	}
	public function getPrimaryKey(){
		return $this->_primaryKey;
	}
	public function getAutoIncrement(){
		return $this->_autoIncrement;
	}
	public function getFieldNames(){
		return $this->_fields;
	}
	public function getPropertyNames(){
		return array_keys($this->_classes);
	}
	public function getForeignKeys(){
		return $this->_foreignKeys;
	}
	public function toArray(){
		$a = array();
		foreach ($this->_properties as $name => $property){
			$a[$name] = $property->getValue();
		}
		return $a;
	}
	public function setOptions($options = array()){
		foreach ($options as $k => $v) $this->_options[$k] = $v;
		return $this;
	}
	protected function _getProperty($name){
		if (!isset($this->_properties[$name])){
			$class = 'stringProperty';
			if (isset($this->_classes[$name])){
				if (class_exists($this->_classes[$name])){
					$class = $this->_classes[$name];
				}
			}
			$this->_properties[$name] = new $class($name);
			$this->_properties[$name]->setModel($this);
			if (is_array($this->_options[$name])){
				$this->_properties[$name]->setOptions($this->_options[$name]);
			}
		}
		return $this->_properties[$name];
	}
	protected function _makeValuesInitial(){
		foreach ($this->_properties as $property){
			if ($property->isChangedValue()){
				$property->setInitialValue($property->getValue());
				$property->setValue(null);
			}
		}
	}
	public function __get($name){
		return $this->_getProperty($name);
	}
	public function __set($name, $value){
		$this->_getProperty($name)->setValue($value);
	}
	// ArrayAccess
	public function offsetExists($offset){
		return in_array($this->_fields($offset));
	}
	public function offsetUnset($offset){
		// can't be unset
	}
	public function offsetGet($offset){
		if (($propertyName = array_search($offset, $this->_fields)) !== false){
			return $this->_getProperty($propertyName);
		}
		return new nullObject;
	}
	public function offsetSet($offset, $value){
		if (($propertyName = array_search($offset, $this->_fields)) !== false){
			$this->_getProperty($propertyName)->setValue($value);
		}
		return $this;
	}
	/**
	 * @return modelStorage
	 */
	public function getStorage(){
		$storageId = storageRegistry::getInstance()->modelSettings[get_class($this)]['storage'];
		$storage = storageRegistry::getInstance()->storages[$storageId];
		return $storage;
	}
	/**
	 * @return string
	 */
	public function getTableName(){
		return storageRegistry::getInstance()->modelSettings[get_class($this)]['table'];
	}
	public function save(){
		$this->preSave();
		foreach ($this as $property){
			$property->preSave();
			$control = $property->getControl();
			if ($control !== null){
				$control->preSave();
			}
		}
		$result = $this->getStorage()->saveModel($this);
		$this->postSave();
		foreach ($this as $property){
			$property->postSave();
			$control = $property->getControl();
			if ($control !== null){
				$control->postSave();
			}
		}
		return $result;
	}
	public function insert(){
		$this->preInsert();
		foreach ($this as $property){
			$property->preInsert();
		}
		$result = $this->getStorage()->insertModel($this);
		$this->postInsert();
		foreach ($this as $property){
			$property->postInsert();
		}
		return $result;
	}
	public function update(){
		$this->preUpdate();
		foreach ($this as $property){
			$property->preUpdate();
		}
		$result = $this->getStorage()->updateModel($this);
		$this->postUpdate();
		foreach ($this as $property){
			$property->postUpdate();
		}
		return $result;
	}
	public function delete(){
		$this->preDelete();
		foreach ($this as $property){
			$property->preDelete();
		}
		$result = $this->getStorage()->deleteModel($this);
		$this->postDelete();
		foreach ($this as $property){
			$property->postDelete();
		}
		return $result;
	}
	public function preSave(){}
	public function preInsert(){}
	public function preUpdate(){}
	public function preDelete(){}
	public function postSave(){}
	public function postInsert(){}
	public function postUpdate(){}
	public function postDelete(){}
}

class modelStatement{
	
}

/**
 * $Id$
 */

class storageRegistry extends registry{
	private static $_instance = null;
	private function __construct(){
		
	}
	public static function getInstance(){
		if (self::$_instance === null){
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	public static function dump(){
		print_r(self::$_instance);
	}
}



abstract class storageDriver{
	protected $_uniqueId = null;
	protected $_connection = null;
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


class mysqlDriver extends storageDriver{
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


class modelStorage{
	private static $_foreignConnections = array();
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
					$seta[] = "`$fieldName` = '".$this->quote($property->getInternalValue())."'";
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
				$property = $item->{$propertyName};
				if ($property){
					$initialValue = $property->getInitialValue();
					if ($initialValue !== null){
						$wherea[] = "`$fieldName` = '".$this->quote($initialValue)."'";
					}else{
						if ($useAssignedValues){
							$value = $property->getValue();
							if ($value !== null){
								$wherea[] = "`$fieldName` = '".$this->quote($value)."'";
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
			$property = $item[$fieldName];
			if ($property){
				$initialValue = $property->getInitialValue();
				if ($initialValue !== null){
					$wherea[] = "`$fieldName` = '".$this->quote($initialValue)."'";
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
	public function saveModel($model){
		if ($this->_getWhereSql($model)){
			$result = $model->update();
		}else{
			$result = $model->insert();
		}
		return $result;
	}
	/**
	 *
	 * @param model $model
	 */
	public function insertModel($model){
		$sql = $this->_getInsertSql($model);
		if ($this->query($sql)){
			$model->makeValuesInitial();
			// Update AutoIncrement property
			$autoIncrement = $model->getAutoIncrement();
			if ($autoIncrement !== null){
				$property = $item->{$autoIncrement};
				if ($value = $this->lastInsertId()){
					if (!is_object($property)){
						throw new Exception('Autoincrement "'.print_r($autoIncrement, true).'" not defined in class "'.get_class($model).'"');
					}
					$property->setInitialValue($value);
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
	public function updateModel($model){
		$sql = $this->_getUpdateSql($model);
		if ($this->query($sql)){
			$model->makeValuesInitial();
			return true;
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
		$this->_registerForeignKeys($modelName);
		return $this;
	}
	protected function _registerForeignKeys($modelName){
		//echo '<div>+ '.$modelName.'</div>';
		$keys = &$this->getRegistry()->foreignKeys;
		$reverseKeys = &$this->getRegistry()->reverseKeys;
		$collection = modelCollection::getInstance($modelName);
		$fks = $collection->getForeignKeys();
		foreach ($fks as $propertyName => $a){
			list($foreignModel, $foreignPropertyName) = $a;
			//echo '+ '.$modelName.'.'.$propertyName.' => '.$foreignModel.'.'.$foreignPropertyName.':<br />';
			$keys[$foreignModel][$modelName] = array($foreignPropertyName, $propertyName);
			$keys[$modelName][$foreignModel] = array($propertyName, $foreignPropertyName);
		}
		foreach ($keys as $model => $connections){
			//echo '<div>Test '.$model.' ';
			foreach ($connections as $viaModel => $options){
				//echo 'using '.$viaModel.' ';
				if ($keys->offsetExists($viaModel)){
					//echo 'ok ';
					foreach ($keys[$viaModel] as $foreignModel => $options2){
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
	}
	public static function getTableModel($collection){
		return $collection->getModelClass();
	}
	public static function getIndirectTablesJoins($sourceTable, $targetTable, $joinOptions){
		$keys = &storageRegistry::getInstance()->foreignKeys;
		$sourceClass = self::getTableModel($sourceTable);
		$targetClass = self::getTableModel($targetTable);
		$joins = array();
		$joinedTables = array();
		//echo 'Connecting from '.$sourceClass.' to '.$targetClass.'<br />';
		foreach ($keys[$sourceClass] as $foreignClass => $options){
			if ($foreignClass == $targetClass){
				if (!is_array($options)){
					$viaClass = $options;
					//echo 'Connecting via '.$viaClass.'<br />';
					$viaTable = modelCollection::getInstance($viaClass);
					$subJoins = self::getIndirectTablesJoins($sourceTable, $viaTable, $joinOptions);
					if ($subJoins !== false){
						foreach ($subJoins as $uid => $joinString){
							$joins[$uid] = $joinString;
						}
					}
					$subJoins = self::getIndirectTablesJoins($viaTable, $targetTable, $joinOptions);
					if ($subJoins !== false){
						foreach ($subJoins as $uid => $joinString){
							$joins[$uid] = $joinString;
						}
					}
					return $joins;
				}else{
					$joinType = isset($joinOptions[$targetTable->getUniqueId()]['type'])?$joinOptions[$targetTable->getUniqueId()]['type']:'INNER';
					list($sourcePropertyName, $targetPropertyName) = $options;
					$joinString = " ".$joinType." JOIN {$targetTable->getTableName()} AS $targetTable ON ({$sourceTable->$sourcePropertyName} = {$targetTable->$targetPropertyName})";
					//$joins[$sourceTable->getUniqueId()] = true;
					$joins[$targetTable->getUniqueId()] = $joinString;
					//echo 'Connecting via DIRECT<br />';
					if ($joinString !== false) return $joins;//array($joinedTables, $joinString);
				}
			}
		}
		echo 'Connecting via FALSE<br />';
		return false;
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


class integerProperty extends modelProperty{
	public function getCreateTablePropertySql(){
		return "`".$this->_fieldName."` bigint(20) unsigned NOT NULL";
	}
}


class timestampProperty extends integerProperty{
	/**
	 * @return string Human presentation
	 */
	public function format($format = "d.m.Y H:i:s"){
		return date($format, $this->value());
	}
	public function chanFormat(){//Вск 06 Дек 2009
		$ts = $this->getValue();
		$wa = array('Вск','Пнд','Втр','Срд','Чтв','Птн','Сбт');
		$ma = array(null,'Янв','Фев','Мар','Апр','Май','Июн','Июл','Авг','Сен','Окт','Ноя','Дек');
		return $wa[date("w", $ts)].' '.date("d", $ts).' '.$ma[date("m", $ts)].' '.date("Y H:i:s", $ts);
	}
}


class modificationTimestampProperty extends timestampProperty{
	public function preSave(){
		$this->setValue(time());
	}
}


class creationTimestampProperty extends timestampProperty{
	public function preInsert(){
		$this->setValue(time());
	}
}

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
	public function fetch($resultSet){
		return $resultSet->fetch();
	}
	public function fetchColumn($resultSet, $columnNumber = 0){
		return $resultSet->fetchColumn($columnNumber);
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
