<?php
require_once dirname(__FILE__).'/image.php';
require_once dirname(__FILE__).'/../common/kanon.php';
class thumbnailer{
	protected $_filename = null;
	protected $_basePath = null;
	protected $_baseUrl = null;
	protected $_rel = null;
	protected $_maxSize = 500;
	protected $_image = null;
	protected $_gcPath = null; // garbage collector path
	protected $_gcProb = 0.01; // garbage collector probability
	public function setMaxSize($maxSize = 500){
		$this->_maxSize = $maxSize;
	}
	public function __construct(){
		$requestUri = $_SERVER['REQUEST_URI'];
		$this->_filename = basename($requestUri);
		$trace = debug_backtrace();
		$file = $trace[0]['file'];
		$this->_basePath = dirname($file);
		$this->_baseUrl = kanon::getBaseUri();
		$this->_rel = dirname(substr($requestUri, strlen($this->_baseUrl)));
	}
	public function onShutdown(){
		if (!$this->_gcProb) return;
		mt_srand(microtime(true)*100000);
		if (mt_rand(0, 1/$this->_gcProb) != 1) return;
		$path = $this->_gcPath;
		foreach (glob($path.'/*') as $file){
			if (!is_file($file)) continue;
			if (substr(basename($file),0,2) != 'tm') continue; // minimize possible damage
			if (fileatime($file) < (time() - 60*60*24*14)){ // not accessed for 2 weeks
				//echo $file;
				unlink($file);
			}
		}
	}
	public function prepareShutdown($path){
		$this->_gcPath = $path;
		register_shutdown_function(array($this, 'onShutdown'));
		ignore_user_abort(true);
		set_time_limit(0);
		//$this->onShutdown();
		//exit;
	}
	public function notFound(){
		header($_SERVER['SERVER_PROTOCOL']." 404 Not Found");
		//$this->prepareShutdown();
	}
	public function getSourcePath($testPrefix = ''){
		$prefix = $this->getPrefix();
		$filename = substr($this->_filename, strlen($prefix)+1);
		//echo $filename.'?';
		$filePath = $this->_basePath.'/'.dirname($this->_rel).'/'.$testPrefix.$filename;
		//echo $filePath.'?!!! ';
		if (is_file($filePath)){
			return $filePath;
		}else{
			throw new Exception('source file '.$filePath.' not found in directory');
		}
		return false;
	}
	public function getPrefix(){
		if (strpos($this->_filename, '_') !== false){
			return reset(explode('_', $this->_filename));
		}
		return false;
	}
	public function makeThumbnail($filename){
		$this->_image = new image($filename);
		$prefix = $this->getPrefix();
		switch (substr($prefix,0,3)){
			case 'tmm':
				if (preg_match("#^tmm([0-9]+)x([0-9]+)$#ims", $prefix, $subs)){
					if ($subs[1] > $this->_maxSize || $subs[2] > $this->_maxSize) return false;
					return $this->_image->fit($subs[1], $subs[2]);
				}
				break;
			case 'tmw':
				if (preg_match("#^tmw([0-9]+)$#ims", $prefix, $subs)){
					if ($subs[1] > $this->_maxSize) return false;
					return $this->_image->fitWidth($subs[1]);
				}
				break;
			case 'tmh':
				if (preg_match("#^tmh([0-9]+)$#ims", $prefix, $subs)){
					if ($subs[1] > $this->_maxSize) return false;
					return $this->_image->fitHeight($subs[1]);
				}
				break;
			case 'tmc':
				if (preg_match("#^tmc([0-9]+)x([0-9]+)$#ims", $prefix, $subs)){
					if ($subs[1] > $this->_maxSize || $subs[2] > $this->_maxSize) return false;
					return $this->_image->crop($subs[1], $subs[2]);
				}
				break;
			case 'tms':
				if (preg_match("#^tmc([0-9]+)x([0-9]+)$#ims", $prefix, $subs)){
					if ($subs[1] > $this->_maxSize || $subs[2] > $this->_maxSize) return false;
					return $this->_image->stretch($subs[1], $subs[2]);
				}
				break;
		}
		return false;
	}
	public function run(){
		//echo 'Filename: '.$this->_filename.'<br />';
		// echo 'Base path: '.$this->_basePath.'<br />';
		// echo 'Base url: '.$this->_baseUrl.'<br />';
		// echo 'Relative: '.$this->_rel.'<br />';
		//var_dump($this);
		if (strpos($this->_filename, '_') !== false){
			if (basename($this->_rel) == '.thumb'){
				if (($filename = $this->getSourcePath()) || ($filename = $this->getSourcePath('l_'))){
					// Check path
					$path = $this->_basePath.'/'.$this->_rel;
					if (!is_dir($path)){
						mkdir($path, 0777, true);
					}
					if (is_dir($path)){
						if ($thumb = $this->makeThumbnail($filename)){
							// FOUND
							$this->prepareShutdown($path);
							kanon::redirect($_SERVER['REQUEST_URI']);
						}
					}else{
						throw new Exception('can\'t create directory');
					}
				}else{
					throw new Exception('source file not found in directory');
				}
			}
		}
		$this->notFound();
	}
}