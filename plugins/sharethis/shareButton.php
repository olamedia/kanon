<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of sharethisButton.php
 *
 * @author olamedia
 */
class shareButton{
	protected $_collection = null;
	protected $_domain = '';
	protected $_imageUrl = '';
	protected $_tip = '';
	protected $_rel = '';
	public function __construct($collection){
		$this->_collection = $collection;
	}
	public function load($domain){
		return ($this->_domain == $domain);
	}
	public function end(){
		return $this->_collection;
	}
	public function setImage($url){
		$this->_imageUrl = $url;
		return $this;
	}
	public function getUrl(){
		return $this->_collection->getUrl();
	}
	public function getTitle(){
		return $this->_collection->getTitle();
	}
	public function getDescription(){
		return $this->_collection->getDescription();
	}
	public function getShareUrl(){
		return '#';
	}
	public function getImageUrl(){
		return $this->_imageUrl;
	}
	public function getTip(){
		return $this->_tip;
	}
	public function getHtml(){
		return '<a rel="'.$this->_rel.'" href="'.$this->getShareUrl().'" title="'.$this->getTip().'"><img src="'.$this->getImageUrl().'" /></a>';
	}
}

?>
