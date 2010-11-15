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
	public function getHtml(){
		$h = '';
		$ha = array();
		foreach ($this->_buttons as $button){
			$ha[] = $button->getHtml();
		}
		$h = '<div class="sharethis">'.implode('', $ha).'</div>';
		return $h;
	}
	public function __toString(){
		return (string) $this->getHtml();
	}
}

