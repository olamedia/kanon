<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of mediaFilenameProperty
 *
 * @author olamedia
 */
class mediaFilenameProperty extends imageFilenameProperty{
    public function canUpload($tmp){
        $path = $this->getPath();
        if (!is_writable($path)){
            if (!headers_sent()){
                header('X-Log-'.get_class($this).'1: not writable: '.$path);
            }
            return false;
        }
        $info = getimagesize($tmp);
        if (!in_array($info[2], array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_SWF, IMAGETYPE_SWC))){
            header('X-Log-'.get_class($this).'1: not media: '.$info[2]);
            return false;
        }
        if (filesize($tmp) > $this->_maxFileSize){
            header('X-Log-'.get_class($this).'1: too large: '.filesize($tmp));
            return false;
        }
        return true;
    }
    protected function _getSize(){
        if ($this->getValue() == '')
            return false;
        list($fw, $fh) = getimagesize($this->getPath().$this->getValue());
        $w = $fw;
        $h = $fh;
        //$item = $this->getItem();
        //echo get_class($item);
        if (isset($this->_options['width'])){
            $wk = $this->_options['width'];
            if (is_object($wk))
                $wk = $wk->getValue();
            if (strlen($wk))
                $w = $wk;
        }
        $w = $w?$w:$fw;
        if (isset($this->_options['height'])){
            $hk = $this->_options['height'];
            if (is_object($hk))
                $hk = $hk->getValue();
            if (strlen($hk)
            )
                $h = $hk;
        }
        $h = $h?$h:$fh;
        //echo ' w:'.$w;
        //echo ' h:'.$h;
        return array($w, $h);
    }
    protected function _px($px){
        if (strval(intval($px)) == strval($px)){
            return $px.'px';
        }
        return $px;
    }
    protected function _flashHtml($width = 'auto', $height = 'auto'){
        if ($this->getValue() == '')
            return '';
        list($w, $h) = $this->_getSize(); //getimagesize($this->sourcePath());
        if ($width == 'auto')
            $width = $w;
        if ($height == 'auto')
            $height = $h;

        return //'<div style="width: '.$this->_px($width).';height: '.$this->_px($height).'">'.
        '<object width="'.$width.'" height="'.$height.'">'.
        '<param name="movie" value="'.$this->source().'"></param>'.
        '<param name="wmode" value="transparent"></param>'.
        '<param name="allowFullScreen" value="true"></param>'.
        '<param name="allowscriptaccess" value="always"></param>'.
        '<embed src="'.$this->source().'" type="application/x-shockwave-flash" '.
        'wmode="transparent" allowscriptaccess="always" allowfullscreen="true" width="'.$width.'" height="'.$height.'"></embed>'.
        '</object>'; //</div>';
    }
    public function _imageHtml($size = 100, $method="fit"){
        if ($size == 'auto'){
            $tm = $this->source();
            list($w, $h) = getimagesize($this->sourcePath());
            $in = ' height="'.$h.'" width="'.$w.'"';
        }else{
            $tm = $this->tm($size, $method);
            $in = ' height="'.$this->_tmHeight.'" width="'.$this->_tmWidth.'"';
        }
        return '<img src="'.$tm.'"'.$in.' />';
    }
    public function _imageSourceHtml($size = 100, $method="fit"){
        $tm = $this->source();
        $path = $this->getPath();
        $file = $this->getPath().$this->getValue();
        list($w, $h) = getimagesize($file);
        $in = ' height="'.$h.'" width="'.$w.'"';
        return '<img src="'.$tm.'"'.$in.' />';
    }
    public function getHeight(){
        list($w, $h) = $this->_getSize();
        return $h;
    }
    public function getWidth(){
        list($w, $h) = $this->_getSize();
        return $w;
    }
    public function html($width = 'auto', $height = 'auto'){
        $ext = end(explode(".", $this->getValue()));
        if ($ext == 'swf'){
            return $this->_flashHtml(); //$width, $height
        }else{
            $size = 'auto';
            if ($width != 'auto' && $height != 'auto'){
                $size = max($width, $height);
            }else{
                if ($width != 'auto')
                    $size = $width;
                if ($height != 'auto')
                    $size = $height;
            }
            return $this->_imageSourceHtml($size);
        }
    }
}
