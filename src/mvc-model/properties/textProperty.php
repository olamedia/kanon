<?php
require_once dirname(__FILE__).'/stringProperty.php';
class textProperty extends stringProperty{
	public function getCreateSql(){
		return "`".$this->_fieldName."` longtext".($this->_notNull?" NOT NULL":'');
	}
}