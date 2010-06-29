<?php
require_once dirname(__FILE__).'/integerProperty.php';
class idProperty extends integerProperty{ // BIGINT(20) UNSIGNED
	protected $_dataSize = 20;
	protected $_dataUnsigned = true;
}