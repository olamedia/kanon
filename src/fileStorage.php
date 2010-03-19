<?php
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
		$this->_path = $this->_normalizePath($path);
		return $this;
	}
	/**
	 * Get path for storage
	 * @return string
	 */
	public function getPath($relativePath = ''){
		$basename = basename($relativePath);
		$dirname = dirname($relativePath);
		if (in_array($basename, array('.', '..'))){
			// concatenate
			$dirname .= '/'.$basename;
			$basename = '';
		}
		$dirname = $this->_rel($dirname);
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
		$path = $this->_path.$relativePath;
		if (is_object($this->_parent)){
			$path = $this->_parent->getPath($path);
		}else{
			$path = '/'.$path; // denormalize path
		}
		return $this->_fixPath(realpath($path).'/');
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