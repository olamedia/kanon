<?php
require_once dirname(__FILE__).'/../modelProperty.php';
class floatProperty extends modelProperty{
	protected $_dataType = modelProperty::TYPE_DOUBLE;
}