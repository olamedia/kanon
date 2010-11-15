<?php
/**
 * Description of odnoklassnikiShareButton
 *
 * @author olamedia
 */
class odnoklassnikiShareButton extends shareButton{
	protected $_domain = 'odnoklassniki.ru';
	protected $_baseUrl = 'http://www.odnoklassniki.ru/dk?st.cmd=addShare&st._surl=';
	protected $_tip = 'Опубликовать в Одноклассниках';
	public function getShareUrl(){
		return $this->_baseUrl.urlencode($this->getUrl());
	}
}

?>
