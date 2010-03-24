<?php
require_once dirname(__FILE__).'/textarea.php';
class htmlTextarea extends textarea{
	protected $_inputCssClass = 'htmlarea';
	public function getHtml(){
		$html = parent::getHtml();
		$config = '';
		if (isset($this->_options['filemanager_url'])){
			$url = $this->_options['filemanager_url'];
			$config  = '<script type="text/javascript">var '.$this->getId().'_filemanager = "'.$url.'";</script>';
		}
		return $html.$config;
	}
}