<?php
require_once dirname(__FILE__).'/../modelProperty.php';
class booleanProperty extends modelProperty{
	protected $_dataType = modelProperty::TYPE_INTEGER;
	protected $_dataSize = 1;
}