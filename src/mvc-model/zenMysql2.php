<?php
/*
 * TODO:
 * fromArray()
 * loadRelated() / submodels
 * Trees
 * select($table1, $table2, $field1, $field2, $string)
 * state(DRAFT/TDRAFT/TCLEAN/CLEAN)
 * $user->updated_at = new Doctrine_Expression('NOW()');
 * $user->refresh($refreshRelated = true);
 * $user->refreshRelated();
 * replace()
 * not()
 * PDO
 * find(pk)
 * $user->exists()
 *
 *
 *
 *
 *
 *
 *
 *
 */

class kanonItem extends kanonObject{

	public function fromArray($array){
		foreach ($array as $name => $value){
			$this->_propertyGet($name)->setInitialValue($value);
		}
	}
	public function count(){
		$this->_makeAllProperties();
		return count($this->_properties);
	}
}



class zenMysqlRow extends mysqlRow{

	public function __construct(){
		$this->_makeAllProperties();
		$this->_updateDefaults();
		$this->onConstruct();
	}
	public function onConstruct(){

	}
	protected function _updateDefaults(){
		$class = get_class($this);
		$table = zenMysql::getModelTable($class);
		if (!is_object($table)){
			throw new Exception('No table found for class '.$class);
		}
		foreach ($this->_classesMap as $propertyName => $className){
			if (isset($this->_fieldsMap[$propertyName])){
				$fieldName = $this->_fieldsMap[$propertyName];
				$default = $table->getDefaultFieldValue($fieldName);
				if ($default !== false){
					$this->{$propertyName}->setValue($default);
				}
			}
		}
	}
}

class zenMysqlTable extends mysqlTable implements ArrayAccess, Countable, IteratorAggregate{
	private $_serverId = null;
	private $_databaseName = null;
	private $_tableName = null;
	private $_fCache = array();
	private $_fList = array();
	private $_isFullList = false;
	private $_uid = null;
	private $_defaults = array();
	public function &setDefaultFieldValue($field, $value){
		$this->_defaults[$field] = $value;
		return $this;
	}
	public function &getDefaultFieldValue($field){
		if (isset($this->_defaults[$field])){
			return $this->_defaults[$field];
		}
		return false;
	}
	public function &resetFilters(){
		$this->_filters = array();
		return $this;
	}
	protected function _q($sql){
		return mysql_query($sql, $this->getLink());
	}
	public function q($sql){
		return mysql_query($sql, $this->getLink());
	}
	public function e($unescapedString){
		return mysql_real_escape_string($unescapedString, $this->getLink());
	}
	public function getUid(){
		if ($this->_uid === null){
			$this->_uid = zenMysql::getTableUid();
		}
		return $this->_uid;
	}
	protected function _fetchFieldsList(){
		$sql = "SHOW COLUMNS FROM `".$this->e($this->_tableName)."`";
		if ($q = $this->_q($sql)){
			while ($a = mysql_fetch_row($q)){
				$fieldName = $a[0];
				$this->_fList[$fieldName] = $this->getFieldByName($fieldName);
			}
			$this->_isFullList = true;
		}
	}
	public function __construct($database, $tableName){
		$this->_serverId = $database->getServerId();
		$this->_databaseName = $database->getDatabaseName();
		$this->_tableName = $tableName;
	}
	public function getFieldByName($fieldName){
		if (!isset($this->_fCache[$fieldName])){
			$this->_fCache[$fieldName] = new zenMysqlField($this, $fieldName);
		}
		return $this->_fCache[$fieldName];
	}
	public function __toString(){
		return $this->getUid();
		return $this->_tableName;
	}
	public function setModel($modelClass){
		zenMysql::setTableModel($this, $modelClass);
		//zenMysql::configureModel($modelClass)->setTable($this);
	}
	public function getServerId(){
		return $this->_serverId;
	}
	public function getDatabaseName(){
		return $this->_databaseName;
	}
	public function getTableName(){
		return $this->_tableName;
	}
	public function &getLink(){
		return zenMysql::getDatabaseLink($this->_serverId, $this->_databaseName)->getLink();
	}
	/* ArrayAccess interface */
	public function offsetSet($fieldName, $value) {
		$this->_fCache[$fieldName] = $value;
	}
	public function offsetExists($fieldName) {
		if (!isset($this->_fCache[$fieldName]) && !$this->_isFullList){
			$this->_fetchFieldsList();
		}
		return isset($this->_fCache[$fieldName]);
	}
	public function offsetUnset($fieldName) {
		unset($this->_fCache[$fieldName]);
	}
	public function offsetGet($fieldName) {
		return $this->getFieldByName($fieldName);
	}
	/**
	 * Countable interface
	 * @return integer
	 */
	public function count() {
		if (!$this->_isFullList){
			$this->_fetchFieldsList();
		}
		return count($this->_fList);
	}
	public function getIterator(){
		if (!$this->_isFullList){
			$this->_fetchFieldsList();
		}
		return new ArrayIterator($this->_fList);
	}
	// __get && __set && __isset && __unset
	public function __get($fieldName){
		return $this->getFieldByName($fieldName);
	}
	/* SQL */
	public function select(){
		$args = func_num_args()?func_get_args():array();
		array_unshift($args, $this);
		$qb = new zenMysqlResult();
		call_user_func_array(array($qb, 'select'), $args);
		foreach ($this->_filters as $filter){
			$qb->where($filter);
		}
		return $qb;
	}
}
class zenMysqlDatabase implements IZenMysqlPrototype, ArrayAccess, Countable, IteratorAggregate{
	private $_serverId = null;
	private $_databaseName = null;
	private $_tCache = array();
	private $_tList = array();
	private $_isFullList = false;
	public function __construct($server, $databaseName){
		$this->_serverId = $server->getServerId();
		$this->_databaseName = $databaseName;
	}
	public function __toString(){
		return $this->_databaseName;
	}
	protected function _q($sql){
		return mysql_query($sql, $this->getLink());
	}
	public function q($sql){
		return mysql_query($sql, $this->getLink());
	}
	public function e($unescapedString){
		return mysql_real_escape_string($unescapedString, $this->getLink());
	}
	public function getServerId(){
		return $this->_serverId;
	}
	public function getDatabaseName(){
		return $this->_databaseName;
	}
	public function &setAlias($alias){
		zenMysql::setDatabaseAlias($this, $alias);
		return $this;
	}
	public function &setDefault(){
		return $this->setAlias('__default');
	}
	public function getTableByName($tableName){
		if (!isset($this->_tCache[$tableName])){
			$this->_tCache[$tableName] = new zenMysqlTable($this, $tableName);
		}
		return $this->_tCache[$tableName];
	}
	public function &getLink(){
		return zenMysql::getDatabaseLink($this->_serverId, $this->_databaseName)->getLink();
	}
	protected function _fetchTablesList(){
		$sql = "SHOW TABLES";
		if ($q = $this->_q($sql)){
			while ($a = mysql_fetch_row($q)){
				$tableName = $a[0];
				$this->_tList[$tableName] = $this->getTableByName($tableName);
			}
			$this->_isFullList = true;
		}
	}
	/* ArrayAccess interface */
	public function offsetSet($tableName, $value) {
		$this->_tCache[$tableName] = $value;
	}
	public function offsetExists($tableName) {
		if (!isset($this->_tCache[$tableName]) && !$this->_isFullList){
			$this->_fetchTablesList();
		}
		return isset($this->_tCache[$tableName]);
	}
	public function offsetUnset($tableName) {
		unset($this->_tCache[$tableName]);
	}
	public function offsetGet($tableName) {
		return $this->getTableByName($tableName);
	}
	/**
	 * Countable interface
	 * @return integer
	 */
	public function count() {
		if (!$this->_isFullList){
			$this->_fetchTablesList();
		}
		return count($this->_tList);
	}
	public function getIterator(){
		if (!$this->_isFullList){
			$this->_fetchTablesList();
		}
		return new ArrayIterator($this->_tList);
	}
}


class zenMysqlItemStorage extends itemStorage{
	protected $_table = null;
	public function query($sql){
		if (!strlen($sql)) return false;
		//echo $sql;
		//die();
		$q = mysql_query($sql, $this->_table->getLink());
		if (!$q){
			$app = application::getInstance();
			if ($app->getUserId() == 1){
				throw new Exception(mysql_error($this->_table->getLink())."\n".$sql);
			}
		}
		return $q;
		return true;
	}


}




class filenameProperty extends stringProperty{
	protected $_path = 'undefined';
	protected $_uri = '';
	protected $_tmWidth = 0;
	protected $_tmHeight = 0;
	public function getPath(){
		$app = application::getInstance();
		return $app->getBasePath($this->_options['path']);
	}
	public function getUri(){
		return $this->_options['url'];
	}
	public function sourcePath(){
		return $this->getPath().$this->getValue();
	}
	public function sourceUrl(){
		return $this->getUri().$this->getValue();
	}
	public function source(){
		return $this->sourceUrl();
	}
	public function unlink(){
		unlink($this->sourcePath());
	}
	public function preDelete(){
		$this->unlink();
	}
}
class imageFilenameProperty extends filenameProperty{
	public function tm($size, $method = 'fit'){
		if (!is_file($this->getPath().$this->getValue())){
			return false;
		}
		$img = new tImage();
		$img->path = $this->getPath();
		$img->thumbnailsFolder = '.thumb';
		$tm = $img->tm($this->getValue(), $size, $method);
		if (is_file($img->path.$img->thumbnailsFolder.'/'.$tm)){
			$info = getimagesize($img->path.$img->thumbnailsFolder.'/'.$tm);
			$this->_tmWidth = $info[0];
			$this->_tmHeight = $info[1];
		}else{
			return false;
		}
		return $this->getUri().$img->thumbnailsFolder.'/'.$tm;
	}
	public function html($size = 100, $method="fit"){
		return '<img src="'.$this->tm($size, $method).'" height="'.$this->_tmHeight.'" width="'.$this->_tmWidth.'" />';
	}
	public function unlink(){
		$img = new tImage();
		$img->path = $this->getPath();
		$img->thumbnailsFolder = '.thumb';
		$img->unlink($this->getValue());
	}
	public function unlinkThumbs(){
		$img = new tImage();
		$img->path = $this->getPath();
		$img->thumbnailsFolder = '.thumb';
		$img->unlink($this->getValue(), true);
	}
}
class flashFilenameProperty extends filenameProperty{
	public function html($width = 'auto', $height = 'auto'){
		if ($this->getValue() == '') return '';
		list($w,$h) = getimagesize($this->sourcePath());
		if ($width == 'auto') $width = $w;
		if ($height == 'auto') $height = $h;
		return '<object width="'.$width.'" height="'.$height.'">'.
		'<param name="movie" value="'.$this->source().'"></param>'.
		'<param name="allowFullScreen" value="true"></param>'.
		'<param name="allowscriptaccess" value="always"></param>'.
		'<embed src="'.$this->source().'" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="'.$width.'" height="'.$height.'"></embed>'.
		'</object>';
	}
}
class mediaFilenameProperty extends imageFilenameProperty{
	protected function _getSize(){
		if ($this->getValue() == '') return false;
		list($fw,$fh) = getimagesize($this->sourcePath());
		$w = $fw; $h = $fh;
		$item = $this->getItem();
		//echo get_class($item);
		if (isset($this->_options['widthKey'])){
			$wk = $this->_options['widthKey'];
			$w = $item->$wk->getValue();
		}
		$w = $w?$w:$fw;
		if (isset($this->_options['heightKey'])){
			$hk = $this->_options['heightKey'];
			$h = $item->$hk->getValue();
		}
		$h = $h?$h:$fh;
		//echo ' w:'.$w;
		//echo ' h:'.$h;
		return array($w,$h);
	}
	protected function _flashHtml($width = 'auto', $height = 'auto'){
		if ($this->getValue() == '') return '';
		list($w,$h) = $this->_getSize();//getimagesize($this->sourcePath());
		if ($width == 'auto') $width = $w;
		if ($height == 'auto') $height = $h;
		return '<object width="'.$width.'" height="'.$height.'">'.
		'<param name="movie" value="'.$this->source().'"></param>'.
		'<param name="allowFullScreen" value="true"></param>'.
		'<param name="allowscriptaccess" value="always"></param>'.
		'<embed src="'.$this->source().'" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="'.$width.'" height="'.$height.'"></embed>'.
		'</object>';
	}
	public function _imageHtml($size = 100, $method="fit"){
		if ($size == 'auto'){
			$tm = $this->source();
			list($w,$h) = getimagesize($this->sourcePath());
			$in = ' height="'.$h.'" width="'.$w.'"';
		}else{
			$tm = $this->tm($size, $method);
			$in = ' height="'.$this->_tmHeight.'" width="'.$this->_tmWidth.'"';
		}
		return '<img src="'.$tm.'"'.$in.' />';
	}
	public function _imageSourceHtml($size = 100, $method="fit"){
		$tm = $this->source();
		list($w,$h) = getimagesize($this->sourcePath());
		$in = ' height="'.$h.'" width="'.$w.'"';
		return '<img src="'.$tm.'"'.$in.' />';
	}
	public function html($width = 'auto', $height = 'auto'){
		$ext = end(explode(".", $this->getValue()));
		if ($ext == 'swf'){
			return $this->_flashhtml();//$width, $height
		}else{
			$size = 'auto';
			if ($width != 'auto' && $height != 'auto'){
				$size = max($width, $height);
			}else{
				if ($width != 'auto') $size = $width;
				if ($height != 'auto') $size = $height;
			}
			return $this->_imageSourceHtml($size);
		}
	}
}