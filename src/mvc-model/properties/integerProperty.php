<?php
class integerProperty extends zenMysqlCell{
	public function getCreateTablePropertySql(){
		return "`".$this->_fieldName."` bigint(20) unsigned NOT NULL";
	}
}