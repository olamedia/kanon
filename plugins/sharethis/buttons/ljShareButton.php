<?php
/**
 * Description of vkontakteShareButton
 * http://www.livejournal.com/update.bml?subject=Ссылка: Кнопки социальных сетей для Blogspot Blogger&amp;event=
 * @author olamedia
 */
class ljShareButton extends shareButton{
	protected $_domain = 'livejournal.com';
	protected $_baseUrl = 'http://www.livejournal.com/update.bml';
	protected $_tip = 'Написать об этом в блоге';
	public function getShareUrl(){
		return $this->_baseUrl.
		'?subject='.urlencode($this->getTitle()).
		'&event='.urlencode($this->getTitle().': '.$this->getUrl())
		;
	}
}

