<?php
#require_once dirname(__FILE__).'/textInput.php';
class passwordInput extends textInput{
	public function getHtml(){
		return '<input type="password" autocomplete="off"'.$this->getIdHtml().' class="'.$this->getInputCssClass().'" name="'.$this->getName().'" value="'.$this->getValue().'" />';
	}
}