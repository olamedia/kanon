<?php
/**
 * Description of mailShareButton
 *
 * @author olamedia
 */
class mailShareButton extends shareButton{
	protected $_domain = 'mail.ru';
	protected $_baseUrl = 'http://connect.mail.ru/share?share_url=';
	protected $_tip = 'Опубликовать в Моем Мире';
	public function getShareUrl(){
		return $this->_baseUrl.urlencode($this->getUrl());
	}
}

