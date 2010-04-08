<?php
require_once dirname(__FILE__).'/../control.php';
class checkboxInput extends control{ // 0/1 values
	/**
	 * @var string
	 */
	protected $_inputCssClass = 'checkbox';
	public function getHtml(){
		return '<div style="margin: 5px 0;"><input type="hidden" name="'.$this->getName().'" value="0" />'.
		'<input type="checkbox"'.$this->getIdHtml().' class="'.$this->getInputCssClass().'" name="'.$this->getName().'" value="1"'.($this->getValue()?' checked="checked"':'').' /></div>';
	}
}