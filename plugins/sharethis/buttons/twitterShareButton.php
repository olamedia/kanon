<?php
/**
 * Description of facebookShareButton
 *
 * @author olamedia
 */
class twitterShareButton extends shareButton{
	protected $_domain = 'twitter.com';
    protected $_baseUrl = 'http://twitter.com/home?status=';
	protected $_tip = 'Опубликовать в Facebook';
	public function getShareUrl(){
		return $this->_baseUrl.urlencode($this->getTitle().' - '.$this->getUrl())
		;
	}
}


