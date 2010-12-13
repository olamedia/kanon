<?php

/**
 * Description of ruPlural
 *
 * @author olamedia
 */
class ruPlural{
	public function getForm($number, $forms = array(0, 1, 2)){
		$cases = array(0, 1, 2, 2, 2, 0);
		return $forms[($number%100>4&&$number%100<20)?0:$cases[min($number%10, 5)]];
	}
}

