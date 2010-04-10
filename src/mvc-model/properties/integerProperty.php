<?php
require_once dirname(__FILE__).'/../modelProperty.php';
class integerProperty extends modelProperty{
	protected $_dataType = modelProperty::TYPE_INTEGER;
	protected $_dataSize = 20;
	protected $_dataUnsigned = false;
}