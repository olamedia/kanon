<?php
/**
 * url represents an url 
 * providing some useful functions to interact with url
 * 
 * @author olamedia
 */
class url implements dataSource{
	protected $_url = null;
	public function __construct($url){
		$this->_url = $url;
	}
	public function getData(){
		return file_get_contents($this->_url);
	}
	public function __toString(){
		return (string) $this->_url;
	}
}
