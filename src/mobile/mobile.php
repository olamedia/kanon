<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of mobile
 *
 * @author olamedia
 */
class mobile{
	protected $_ua = '';
	protected $_nua = '';
	protected $_profile = '';
	protected $_browserClass = ''; // opera/webkit/gecko/trident
	protected $_browserSubclass = ''; // opera mini/firefox/flock/ie7/ie8
	public static function detect(){
		$m = new self();
		$m->setUseragent(request::getUseragent(''));
		$m->setProfile(request::getHttpHeader('Profile', ''));
		var_dump($m);
	}
	public function setUseragent($ua){
		$this->_ua = $ua;
		// normalize useragent string
		$ua = str_replace('(', ';', $ua);
		$ua = str_replace(')', ';', $ua);
		$ua = explode(';', $ua);
		foreach ($ua as $k => $v)
			$ua[$k] = trim($v);
		$this->_nua = $n = strtolower(';;'.implode(';', $ua).';;');
		// Match for Opera Mini
		// ; Opera Mini/buildnumber;
		if (strpos(';opera mini/', $n)){
			$this->_browserClass = 'opera';
			$this->_browserSubclass = 'opera mini';
		}
		// Match for Opera Mobile
		// ; Opera Mobi/buildnumber;
		if (strpos(';opera mobi/', $n)){
			$this->_browserClass = 'opera';
			$this->_browserSubclass = 'opera mobile';
		}
		// Match for MSIE
		// ; MSIE 8.0;; MSIE 7.0;
		if (strpos(';msie 7.0;', $n)){
			$this->_browserClass = 'trident';
			$this->_browserSubclass = 'ie7';
		}
		if (strpos(';msie 8.0;', $n)){
			$this->_browserClass = 'trident';
			$this->_browserSubclass = 'ie8';
		}
		// Match for Webkit browsers
		// ) AppleWebKit/534.12 (KHTML, like Gecko)
		if (strpos(';applewebkit/', $n)){
			$this->_browserClass = 'webkit';
			if (strpos(';chrome', $n)){
				$this->_browserSubclass = 'chrome';
			}
		}
	}
	public function setProfile($rdfLocation){
		$this->_profile = $rdfLocation;
	}
}

