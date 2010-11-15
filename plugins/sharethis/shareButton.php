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
	public function load($domain){
		return ($this->_domain = $domain);
	}
	public function __construct($collection){
		$this->_collection = $collection;
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
}

?>
