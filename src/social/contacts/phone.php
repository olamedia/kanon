<?php

class phone{
	protected static $_countryCodes = array(
		1,
		7,
		// 2 digits
		20, 27,
		30, 31, 32, 33, 34, 36, 39,
		40, 41, 43, 44, 45, 46, 47, 48, 49,
		51, 52, 53, 54, 55, 56, 57, 58,
		60, 61, 62, 63, 64, 65, 66,
		81, 82, 84, 86,
		90, 91, 92, 93, 94, 95, 98,
		// 3 digits
		212, 213, 216, 218,
		220, 221, 222, 223, 224, 225, 226, 227, 228, 229,
		230, 231, 232, 234, 235, 236, 237, 238, 239,
		240, 241, 242, 243, 244, 245, 246, 249,
		250, 251, 252, 253, 254, 255, 256, 257, 258,
		260, 261, 262, 263, 264, 265, 266, 267, 268, 269,
		290, 291, 297, 298, 299,
		350, 351, 352, 353, 354, 355, 356, 357, 358, 359,
		370, 371, 372, 373, 374, 375, 376, 377, 378,
		380, 381, 382, 385, 386, 387, 389,
		420, 421, 423,
		500, 501, 502, 503, 504, 505, 506, 507, 508, 509,
		590, 591, 592, 593, 594, 595, 596, 597, 598, 599,
		670, 672, 673, 674, 675, 676, 677, 678, 679,
		680, 681, 682, 683, 685, 686, 687, 688, 689,
		690, 691, 692,
		850, 852, 853, 855, 856,
		880, 886,
		960, 961, 962, 963, 964, 965, 966, 967, 968,
		970, 971, 972, 973, 974, 975, 976, 977,
		992, 993, 994, 995, 996, 998,
	);
	protected static $_defaultCountryCode = '7'; // +7 - russia
	protected static $_defaultCityCode = '373';
	protected static $_cityDialPrefix = '8';
	protected static $_countryDialPrefix = '810';
	protected $_countryCode = '';
	protected $_cityCode = '';
	protected $_number = '';
	protected function __construct(){
		
	}
	public static function setDefaultCountryCode($code){
		self::$_defaultCountryCode = $code;
	}
	public static function setDefaultCityCode($code){
		self::$_defaultCityCode = $code;
	}
	public static function fromString($phoneString){
		$phone = new self($phoneString);
		return $phone;
	}
	public function setCountryCode($code){
		$this->_countryCode = $code;
	}
	public function setCityCode($code){
		$this->_cityCode = $code;
	}
	public function setNumber($number){
		$this->_number = $number;
	}
	public function normalize($phoneString){
		$phone = new self();
		if (preg_match("#(\+[0-9])#ims", $phoneString, $subs)){
			// +7 950-556-27-20
			// country full
		}
		if (preg_match("#([0-9])#ims", $phoneString, $subs)){
			$f = $subs[1];
			if ($f=='8'){
				// country call
				// 11 digits
				// 8 950-556-27-20
				// 8 10 country ... - international call (russia)
				$ds = preg_replace("#([^0-9])#ims", '', $phoneString);
				if (substr($ds, 1, 2)=='10'){
					// international call (810)
					$phone->setCountryCode(substr());
					$phone->setNumber(substr($ds, 1));
				}else{
					$phone->setCountryCode(self::$_defaultCountryCode);
					$phone->setNumber(substr($ds, 1));
				}
				return $phone;
			}
		}
		return false;
	}
	public function __toString(){
		
	}
}
