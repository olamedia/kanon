<?php
/*
 * Nokia:
 * x-Device-User-Agent
 * 
 * 
 * 
 */

/**
 * 
 * @see wurfl
 * @author olamedia
 */
class mobile{
	protected $_ua = '';
	protected $_nua = '';
	protected $_profile = '';
	protected $_browserEngine = ''; // opera/webkit/gecko/trident
	protected $_browserClass = ''; // opera/webkit/gecko/trident
	protected $_browserSubclass = ''; // opera mini/firefox/flock/ie7/ie8
	protected $_platform = ''; // win/mac/linux/ios
	protected $_deviceBrand = '';
	protected $_deviceModel = '';
	protected $_isPhone = null; // (device which can call)
	protected $_j2me = null;
	protected $_features = array(
	);
	public static function detect(){
		$m = new self();
		$m->setUseragent(request::getUseragent(''));
		$m->setProfile(request::getHttpHeader('X-Wap-Profile', ''));
		$m->setUseragent('NokiaN8-00/10.0.000 (Symbian/3; S60/5.2 Mozilla/5.0; Profile/MIDP-2.1 Configuration/CLDC-1.1) AppleWebkit/525 (KHTML, like Gecko) BrowserNG/7.2');
		$m->setProfile('http://nds1.nds.nokia.com/uaprof/NN8-00r100-3G.xml');
		$m->dump();
	}
	public function dump(){
		echo 'Browser: '.$this->_browserEngine.'/'.$this->_browserClass.'/'.$this->_browserSubclass.'<br />';
		echo 'Platform: '.$this->_platform.'<br />';
		echo 'Device: '.$this->_deviceBrand.'/'.$this->_deviceModel.'<br />';
		echo 'isPhone: '.intval($this->_isPhone).'<br />';
	}
	public function setUseragent($ua){
		$this->_ua = $ua;
		// normalize useragent string
		$ua = str_replace('(', ';', $ua);
		$ua = str_replace(')', ';', $ua);
		$ua = str_replace('-', ';', $ua);
		$ua = str_replace('_', ';', $ua);
		$ua = str_replace('/', ';', $ua);
		$ua = explode(';', $ua);
		foreach ($ua as $k => $v)
			$ua[$k] = trim($v);
		$this->_nua = $n = strtolower(';;'.implode(';', $ua).';;');
		if (strpos($n, ';symbian;')){
			$this->_platform = 'symbian';
		}elseif (strpos($n, ';android;')){
			$this->_platform = 'android';
		}elseif (strpos($n, ';linux;')){
			$this->_platform = 'linux';
		}elseif (strpos($n, ';windows;')){
			$this->_platform = 'win32';
		}elseif (strpos($n, ';ios;')){
			$this->_platform = 'ios';
		}elseif (strpos($n, ';mac;')){
			$this->_platform = 'mac';
		}
		// Match for Opera Mini
		// ; Opera Mini/buildnumber;
		if (strpos($n, ';opera mini;')){
			$this->_browserClass = 'opera';
			$this->_browserSubclass = 'opera mini';
			$this->_j2me = true;
			$this->_isPhone = true;
		}
		// Match for Opera Mobile
		// ; Opera Mobi/buildnumber;
		if (strpos($n, ';opera mobi;')){
			$this->_browserClass = 'opera';
			$this->_browserSubclass = 'opera mobile';
			$this->_isPhone = true;
		}
		// Match for MSIE
		// ; MSIE 8.0;; MSIE 7.0;
		if (strpos($n, ';msie 6.0;')){
			$this->_browserEngine = 'trident';
			$this->_browserClass = 'trident';
			$this->_browserSubclass = 'ie6';
		}
		if (strpos($n, ';msie 7.0;')){
			$this->_browserClass = 'trident';
			$this->_browserSubclass = 'ie7';
		}
		if (strpos($n, ';msie 8.0;')){
			$this->_browserClass = 'trident';
			$this->_browserSubclass = 'ie8';
		}
		if (strpos($n, ';iemobile')){
			$this->_browserClass = 'trident';
			$this->_isPhone = true;
		}
		// Match for Webkit browsers
		// ) AppleWebKit/534.12 (KHTML, like Gecko)
		if (strpos($n, ';applewebkit;')){
			$this->_browserEngine = 'webkit';
			$this->_browserClass = 'webkit';
			if (strpos($n, ';chrome')){
				$this->_browserSubclass = 'chrome';
			}
			if (strpos($n, 'browserng')){
				$this->_browserSubclass = 'browserng';
				$this->_isPhone = true;
			}
		}
		// Mobile browser: UP.Browser
		if (strpos($n, 'up.browser')){
			$this->_browserClass = 'up.browser';
			$this->_browserSubclass = 'up.browser';
			$this->_isPhone = true;
		}
		// Mobile browser: Access Netfront
		// Browser/NetFront/
		if (strpos($n, ';netfront;')){
			$this->_browserClass = 'netfront';
			$this->_browserSubclass = 'netfront';
			$this->_isPhone = true;
		}
		// Mobile browser: Teleca-Obigo
		// Browser/Teleca_obigo Obigo/Q05A ObigoInternetBrowser/Q03C Browser/Obigo-Q05A/3.12
		if (strpos($n, 'teleca')||strpos($n, 'obigo')){
			$this->_browserClass = 'obigo';
			$this->_browserSubclass = 'obigo';
			$this->_isPhone = true;
		}
		$this->_isPhone = $this->_isPhone?true:false;
		$this->lookupModel();
	}
	protected function lookupModel(){
		//
		if (preg_match("#^W3C\-mobile#ims", $this->_ua)){
			// Brand: W3C XD
			$this->_deviceBrand = 'w3c';
			$this->_isPhone = true; // test support
		}
		if (preg_match("#i(Phone|Pod|Pad)#", $this->_ua)){
			// Brand: Apple
			if (preg_match("#\(iPhone;#", $this->_ua)){
				// iPhone
			}
			if (preg_match("#\(iPod;#", $this->_ua)){
				// iPod
			}
			if (preg_match("#\(iPad;#", $this->_ua)){
				// iPad
			}
		}
		if (preg_match("#Nokia#ims", $this->_ua)){
			// Brand: Nokia
			$this->_deviceBrand = 'nokia';
			$this->_isPhone = true;
		}
		if (preg_match("#BlackBerry#ims", $this->_ua)){
			// Brand: BlackBerry
			$this->_deviceBrand = 'blackberry';
			$this->_isPhone = true;
		}
		if (preg_match("#^LGE?\-#ims", $this->_ua)){
			// Brand: LG
			$this->_deviceBrand = 'lg';
			$this->_isPhone = true;
		}
		if (preg_match("#^MOT\-#ims", $this->_ua)){
			// Brand: Motorola
			$this->_deviceBrand = 'motorola';
			$this->_isPhone = true;
		}
		if (preg_match("#^SAMSUNG\-#ims", $this->_ua)){
			// Brand: Samsung
			$this->_deviceBrand = 'samsung';
			$this->_isPhone = true;
		}
		if (preg_match("#^ZTE\-#ims", $this->_ua)){
			// Brand: ZTE
			$this->_deviceBrand = 'zte';
			$this->_isPhone = true;
		}
		if (preg_match("#^SonyEricsson#ims", $this->_ua)){
			// Brand: SonyEricsson
			$this->_deviceBrand = 'sonyericsson';
			$this->_isPhone = true;
		}
	}
	public function setProfile($rdfLocation){
		$this->_profile = $rdfLocation;
		if (!strlen($this->_profile)) return;
		try{
			$profile = simplexml_load_file($this->_profile);
			$profile->registerXPathNamespace('rdf', "http://www.w3.org/1999/02/22-rdf-syntax-ns#");
			// rdf:RDF/rdf:Description rdf:ID="Profile"<prf:component>
			// <rdf:Description rdf:ID="HardwarePlatform">
			// <prf:Model>N8-00</prf:Model>
			foreach ($profile->xpath('//rdf:Description') as $platformDesc) {
				if ($platformDesc['rdf:ID'] == "HardwarePlatform"){
					foreach ($platformDesc->xpath('//prf:Model') as $model){
						$this->_deviceModel = $model;
					}
				}
			}
		}catch(Exception $e){
			throw $e;
		}
	}
}

