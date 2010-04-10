<?php
require_once dirname(__FILE__).'/integerProperty.php';
class floatProperty extends integerProperty{
	protected $_dataType = modelProperty::TYPE_FLOAT;
	
}