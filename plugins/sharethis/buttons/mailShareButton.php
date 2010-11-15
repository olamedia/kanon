<?php
/**
 * Description of mailShareButton
 *
 * @author olamedia
 */
class mailShareButton{
	protected $_domain = 'mail.ru';
	protected $_baseUrl = 'http://connect.mail.ru/share?share_url=';
	public function getShareUrl(){
		return $this->_baseUrl.urlencode($this->getUrl());
	}
}

