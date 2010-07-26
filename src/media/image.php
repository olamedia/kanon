<?php
require_once dirname(__FILE__).'/../2d/rectangle.php';

class image{
	protected static $_driver;
	protected $_filename = null;
	protected $_info = null;
	protected $_keepAnimation = true;
	protected $_frameLimit = 24;
	public $meta = null;
	protected $_type = null;
	public function setType($type){
		$this->_type = $type;
	}
	protected static $_prefferedDrivers = array(
	'imagickDriver',
	'gdDriver',
	);
	public function __construct($filename){
		$this->_filename = $filename;
	}
	public function getFilename(){
		return $this->_filename;
	}
	/**
	 * @return imageDriver
	 */
	public static function getDriver(){
		if (null === self::$_driver){
			foreach (self::$_prefferedDrivers as $driverName){
				require_once dirname(__FILE__).'/drivers/'.$driverName.'.php';
				$driver = new $driverName();
				if ($driver->load()){
					self::$_driver = $driver;
					return self::$_driver;
				}
			}
			foreach (glob(dirname(__FILE__).'/drivers/*') as $f){
				if (is_file($f)){
					require_once $f;
					$driverName = basename($f);
					$a = explode('.', $driverName);
					array_pop($a);
					$driverName = implode('_', $a);
					$driver = new $driverName();
					if ($driver->load()){
						self::$_driver = $driver;
						return self::$_driver;
					}
				}
			}
		}
		return self::$_driver;
	}
	public function getInfo(){
		if ($this->_info == null){
			$this->_info = getimagesize($this->_filename);
		}
		return $this->_info;
	}
	public function getType(){
		if (null === $this->_type){
			if (is_file($this->_filename)){
				if ($info = $this->getInfo()){
					$this->_type = $info[2];
				}
			}
		}
		return $this->_type;
	}
	/**
	 * @return rectangle
	 */
	public function getRectangle(){
		if ($info = $this->getInfo()){
			$rect = new rectangle($info[0], $info[1]);
			return $rect;
		}
		return new rectangle(0, 0);
	}
	public function getWidth(){
		return $this->getRectangle()->getWidth();
	}
	public function getHeight(){
		return $this->getRectangle()->getHeight();
	}
	/**
	 * @return thumbnail
	 */
	public function getThumbnail($width, $height, $type = 'fit'){
		$allowed = array('stretch','fit','fitWidth','fitHeight','crop');
		if (in_array($type, $allowed)){
			return call_user_func_array(array($this, $type), array($width, $height));
		}
		return false;
	}
	/**
	 *
	 * @param string $targetFilename
	 * @param rectangle $rectangle
	 * @return thumbnail
	 */
	protected function _getThumbnail($targetFilename, $rectangle){
		return new thumbnail($targetFilename, $this, $rectangle);
	}
	public function getThumbnailPath($prefix){
		//if ($this->_targetFilename !== null) return $this->_targetFilename;
		return dirname($this->_filename).'/.thumb/'.$this->getThumbnailFilename($prefix);
	}
	public function getThumbnailFilename($prefix){
		return $prefix.'_'.basename($this->_filename);
	}
	public function stretch($width, $height){
		return $this->_getThumbnail($this->getThumbnailPath('tms'.$width.'x'.$height), new rectangle($width, $height));
	}
	public function fit($width, $height){
		return $this->_getThumbnail($this->getThumbnailPath('tmm'.$width.'x'.$height), $this->getRectangle()->fit($width, $height));
	}
	public function fitWidth($width, $height){
		return $this->_getThumbnail($this->getThumbnailPath('tmw'.$width), $this->getRectangle()->fitWidth($width));
	}
	public function fitHeight($width, $height){
		return $this->_getThumbnail($this->getThumbnailPath('tmh'.$height), $this->getRectangle()->fitHeight($height));
	}
	public function crop($width, $height){
		return $this->_getThumbnail($this->getThumbnailPath('tmc'.$width.'x'.$height), $this->getRectangle()->crop($width, $height));
	}
	protected $_modified = false;
	public function fromArea($sourceImage, $rectangle){
		$this->getDriver()->fromArea($this, $sourceImage, $rectangle);
		$this->_modified = true;
	}
	public function save(){
		if ($this->_modified){
			$this->getDriver()->save($this);
			$this->_modified = false;
		}
	}
	public function init(){
		return $this->getDriver()->init($this);
	}
	public function __destruct(){
		$this->getDriver()->destruct($this);
	}
}