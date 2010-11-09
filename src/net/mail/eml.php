<?php
/**
 * Description of eml
 *
 * @author olamedia
 */
class eml {
	protected $_attachments = array();
    public function attach($attachment){
		// do something with attachment
		$this->_attachments[] = $attachment;
		return $this;
	}
}

