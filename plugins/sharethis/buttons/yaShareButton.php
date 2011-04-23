<?php
/**
 * Description of vkontakteShareButton
 *
 * @author olamedia
 */
class yaShareButton extends shareButton{
	protected $_domain = 'ya.ru';
	protected $_baseUrl = 'http://my.ya.ru/posts_add_link.xml';
	protected $_tip = 'Написать об этом в блоге';
	public function getShareUrl(){
		return $this->_baseUrl.
		'?URL='.urlencode($this->getUrl()).
		'&title='.urlencode($this->getTitle())
		;
	}
}

