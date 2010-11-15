<?php

/**
 * Description of sharethis
 *
 * @author olamedia
 */
class sharethis{
	protected $_url = '';
	protected $_title = '';
	protected $_description = '';
	protected $_buttons = array();
	public function setUrl($url){
		$this->_url = $url;
	}
	public function setTitle($title){
		$this->_title = $title;
	}
	public function setDescription($description){
		$this->_description = $description;
	}
	public function addButton($domain){
		$button = $this->getButton($domain);
		if ($button){
			$this->_buttons[] = $button;
			return $button;
		}
		return false;
	}
	public function getButton($domain){
		$path = dirname(__FILE__).'/buttons/';
		foreach (glob($path.'*') as $f){
			$class = array_shift(explode('.', basename($f)));
			require_once $f;
			/* @var $button sharethisButton */
			$button = new $class($this);
			if ($button->load($domain)){
				return $button;
			}
		}
		return false;
	}
}

