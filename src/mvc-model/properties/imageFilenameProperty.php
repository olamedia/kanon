<?php

class imageFilenameProperty extends stringProperty{
	protected $_path = null;
	protected $_uri = null;
	protected $_tmWidth = 0;
	protected $_tmHeight = 0;
	public function setPath($path){
		$this->_path = $path;
		return $this;
	}
	public function getPath(){
		if ($this->_path!==null){
			return $this->_path;
		}
		return kanon::getBasePath().'/'.(isset($this->_options['path'])?$this->_options['path']:'');
	}
	public function setUri($uri){
		$this->_uri = $uri;
		return $this;
	}
	public function getUri(){
		if ($this->_uri!==null){
			return $this->_uri;
		}
		$baseUrl = 'http://'.request::getServerName().'';
		return $baseUrl.$this->_options['url'];
		return kanon::getBaseUri().'/'.$this->_options['url'];
	}
	public function source(){
		return $this->getUri().'/'.$this->getValue();
	}
	public function getValue(){
		$value = parent::getValue();
		/*if ($value!==''){
			if (!is_file($this->getPath().$value)){
				$value = '';
			}
		}*/
		return $value;
	}
	public function tm($size, $method = 'fit', $x = null){
		$width = $height = $size;
		if ($x!==null){
			$height = $method;
			$method = $x;
		}
		$path = $this->getPath();
		if (!is_file($this->getPath().$this->getValue())){
			throw new Exception('file not found: '.$this->getPath().$this->getValue());
			return false;
		}
		$prefix = 'tmm';
		switch ($method){
			case 'fit':
				$prefix = 'tmm';
				break;
			case 'crop':
				$prefix = 'tmc';
				break;
			case 'stretch':
				$prefix = 'tms';
				break;
		}

		$tm = $prefix.$width.'x'.$height.'_'.$this->getValue();
		if (is_file($path.'.thumb/'.$tm)){
			$info = getimagesize($path.'.thumb/'.$tm);
			$this->_tmWidth = $info[0];
			$this->_tmHeight = $info[1];
		}else{
			//return false;
		}
		return $this->getUri().'.thumb/'.$tm;
	}
	public function preUpdate(){
		$path = $this->getPath().'.thumb/';
		foreach (glob($path.'tm*_'.$this->getValue()) as $filename){
			unlink($filename);
		}
	}
	public function html($size = 100, $method="fit", $x = null){
		return '<img src="'.$this->tm($size, $method,$x).'"'.($this->_tmHeight?' height="'.$this->_tmHeight.'"':'').($this->_tmWidth?' width="'.$this->_tmWidth.'"':'').' />';
	}
	// http://www.appelsiini.net/projects/lazyload
	public function htmlLazyLoad($size = 100, $method="fit"){
		return '<img src="/css/images/1x1.gif" original="'.$this->tm($size, $method).'"'.($this->_tmHeight?' height="'.$this->_tmHeight.'"':'').($this->_tmWidth?' width="'.$this->_tmWidth.'"':'').' class="preloader" />';
	}
	public function htmlSourceLazyLoad(){
		$info = getimagesize($this->getPath().$this->getValue());
		$w = $info[0];
		$h = $info[1];
		return '<img src="/css/images/1x1.gif" original="'.$this->source().'"'.($h?' height="'.$h.'"':'').($w?' width="'.$w.'"':'').' class="preloader" />';
	}
}