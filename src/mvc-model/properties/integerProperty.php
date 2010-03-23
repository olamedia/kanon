<?php
require_once dirname(__FILE__).'/../modelProperty.php';
class integerProperty extends modelProperty{
	public function getCreateTablePropertySql(){
		return "`".$this->_fieldName."` bigint(20) unsigned NOT NULL";
	}
}