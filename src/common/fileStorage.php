<?php
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