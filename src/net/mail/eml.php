<?php
 
/**
 * Description of eml
 *
 * @author olamedia
 */
class eml{
    protected $_attachments = array();
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
}

