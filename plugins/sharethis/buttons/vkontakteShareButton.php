<?php
/**
 * Description of vkontakteShareButton
 *
 * @author olamedia
 */
class vkontakteShareButton extends shareButton{
	protected $_domain = 'vkontakte.ru';
	protected $_baseUrl = 'http://vkontakte.ru/share.php';
	public function getShareUrl(){
		return $this->_baseUrl.
		'?url='.urlencode($this->getUrl()).
		'&title='.urlencode($this->getTitle()).
		'&description='.urlencode($this->getDescription())
		;
	}
}

