<?php
/**
 * Description of odnoklassnikiShareButton
 *
 * @author olamedia
 */
class odnoklassnikiShareButton{
	protected $_domain = 'odnoklassniki.ru';
	protected $_baseUrl = 'http://www.odnoklassniki.ru/dk?st.cmd=addShare&st._surl=';
	public function getShareUrl(){
		return $this->_baseUrl.urlencode($this->getUrl());
	}
}

?>
