<?php

/**
 * Description of eml
 *
 * @author olamedia
 */
class eml{
	protected $_attachments = array();
	protected $_topic = '';
	protected $_body = '';
	protected $_charset = 'UTF-8';
	protected $_headers = array();
	public function setTopic($topic){
		$this->_topic = $topic;
		return $this;
	}
	public function setBody($body){
		$this->_body = $body;
		return $this;
	}
	public function addHeader($header){
		$name = strtolower(trim(reset(explode(':', $header))));
		$this->_headers[$name] = $header;
		return $this;
	}
	/**
	 * $eml->attach(new emlAttachment(filename($filename)));
	 * @param emlAttachment $attachment
	 * @return eml
	 */
	public function attach($attachment){
		// do something with attachment
		if (!($attachment instanceof emlAttachment)){
			$attachment = new emlAttachment($attachment);
		}
		$this->_attachments[] = $attachment;
		return $this;
	}
	protected function _getEncodedTopic(){
		return '=?'.$this->_charset.'?B?'.base64_encode($this->_topic).'?=';
	}
	protected function _getHeaders(){
		return implode("\r\n", $this->_headers);
	}
	protected function _getEncodedBody(){
		return chunk_split(base64_encode($this->_body));
	}
	public function sendTo($email){
		$this->addHeader("MIME-Version: 1.0\r\nContent-Transfer-Encoding: BASE64");
		mail($email, $this->_getEncodedTopic(), $this->_getEncodedBody(), $this->_getHeaders());
		return $this;
	}
}

