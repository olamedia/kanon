<?php
/**
 * Description of facebookShareButton
 *
 * @author olamedia
 */
class facebookShareButton extends shareButton{
	protected $_domain = 'facebook.com';
    protected $_baseUrl = 'http://www.facebook.com/sharer.php';
	protected $_tip = 'Опубликовать в Facebook';
	public function getShareUrl(){
		return $this->_baseUrl.
		'?u='.urlencode($this->getUrl()).
		'&t='.urlencode($this->getTitle())
		;
	}
}


