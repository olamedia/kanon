<?php
require_once dirname(__FILE__).'/../modelProperty.php';
class integerProperty extends modelProperty{
	protected $_unsigned = false;
	protected $_size = 20;
	public function getCreateSql(){
		$type = 'bigint';
		if ($this->_size < 11) $type = 'int';
		if ($this->_size < 4) $type = 'tinyint';
		return "`".$this->_fieldName."` ".$type."(".$this->_size.")".($this->_unsigned?" unsigned":'').($this->_notNull?" NOT NULL":'');
	}
}