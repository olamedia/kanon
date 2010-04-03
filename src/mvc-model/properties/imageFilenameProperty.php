<?php
class imageFilenameProperty extends stringProperty{
	protected $_path = 'undefined';
	protected $_uri = '';
	protected $_tmWidth = 0;
	protected $_tmHeight = 0;
	public function setPath($path){
		$this->_path = $path;
	}
	public function setUri($uri){
		$this->_uri = $uri;
	}
	public function source(){
		return $this->_uri.'/'.$this->getValue();
	}
	public function tm($size, $method = 'fit'){
		if (!is_file($this->_path.$this->getValue())){
			//echo $this->_path;
			return false;
		}
		$img = new tImage();
		$img->path = $this->_path;
		$tm = $img->tm($this->getValue(), $size, $method);
		if (is_file($img->path.'.thumb/'.$tm)){
			$info = getimagesize($img->path.'.thumb/'.$tm);
			$this->_tmWidth = $info[0];
			$this->_tmHeight = $info[1];
		}else{
			//return false;
		}
		return $this->_uri.'.thumb/'.$tm;
	}
	public function html($size = 100, $method="fit"){
		return '<img src="'.$this->tm($size, $method).'"'.($this->_tmHeight?' height="'.$this->_tmHeight.'"':'').($this->_tmWidth?' width="'.$this->_tmWidth.'"':'').' />';
	}
	// http://www.appelsiini.net/projects/lazyload
	public function htmlLazyLoad($size = 100, $method="fit"){
		return '<img src="/css/images/1x1.gif" original="'.$this->tm($size, $method).'"'.($this->_tmHeight?' height="'.$this->_tmHeight.'"':'').($this->_tmWidth?' width="'.$this->_tmWidth.'"':'').' class="preloader" />';
	}
}