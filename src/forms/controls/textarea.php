<?php
#require_once dirname(__FILE__).'/textInput.php';
class textarea extends textInput{
	public function getHtml(){
		$style = array();
		if (isset($this->_options['width'])){
			$style[] = 'width: '.$this->_options['width'];
		}
		if (isset($this->_options['height'])){
			$style[] = 'height: '.$this->_options['height'];
		}
		$ss = count($style)?' style="'.implode(";", $style).'"':'';
		return '<textarea id="'.$this->getId().'" class="'.$this->getInputCssClass().'" name="'.$this->getName().'"'.$ss.'>'.$this->getValue().'</textarea>';
	}
}