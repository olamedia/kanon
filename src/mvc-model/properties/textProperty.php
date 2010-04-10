<?php
require_once dirname(__FILE__).'/stringProperty.php';
class textProperty extends stringProperty{
	protected $_dataType = modelProperty::TYPE_TEXT;
	public function getCreateSql(){
		return "`".$this->_fieldName."` longtext".($this->_notNull?" NOT NULL":'');
	}
}