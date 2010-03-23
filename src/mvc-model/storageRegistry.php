<?php
/**
 * $Id: applicationRegistry.php 27 2010-03-20 01:04:22Z olamedia $
 */
require_once dirname(__FILE__).'/../common/registry.php';
class storageRegistry extends registry{
	private static $_instance = null;
	private function __construct(){
		
	}
	public static function getInstance(){
		if (self::$_instance === null){
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	public static function dump(){
		print_r(self::$_instance);
	}
}