<?php
/**
 * $Id$
 */
#require_once dirname(__FILE__).'/../common/registry.php';
class applicationRegistry extends registry{
	private static $_instance = null;
	private function __construct(){
		
	}
	public static function getInstance(){
		if (self::$_instance === null){
			self::$_instance = new self();
		}
		return self::$_instance;
	}
}