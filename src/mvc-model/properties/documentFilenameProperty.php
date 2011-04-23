<?php

class documentFilenameProperty extends stringProperty{
    protected $_path = null;
    protected $_uri = null;
    protected $_maxFileSize = 20971520; // 20 Mb
    public function setPath($path){
        $this->_path = $path;
        return $this;
    }
    public function getPath(){
        if ($this->_path !== null){
            return $this->_path;
        }
        return kanon::getBasePath().'/'.(isset($this->_options['path'])?$this->_options['path']:'');
    }
    public function setUri($uri){
        $this->_uri = $uri;
        return $this;
    }
    public function getUri(){
        if ($this->_uri !== null){
            return $this->_uri;
        }
        $baseUrl = 'http://'.request::getServerName().'';
        //$baseUrl.
        return $this->_options['url'];
        return kanon::getBaseUri().'/'.$this->_options['url'];
    }
    public function source(){
        return $this->getUri().'/'.$this->getValue();
    }
    public function getValue(){
        $value = parent::getValue();
        /* if ($value!==''){
          if (!is_file($this->getPath().$value)){
          $value = '';
          }
          } */
        return $value;
    }
    protected $_supportedTypes = array(
        'doc','xls'
    );
    public function canUpload($tmp, $ext){
        $path = $this->getPath();
        if (!is_writable($path)){
            if (!headers_sent()){
                header('X-Log-'.get_class($this).'1: not writable: '.$path);
            }
            return false;
        }
        if (!in_array($ext, $this->_supportedTypes)){
            return false;
        }
        if (filesize($tmp) > $this->_maxFileSize){
            return false;
        }
        return true;
    }
    public function upload($tmp, $uniqid, $ext){
        $path = $this->getPath();
        if ($this->canUpload($tmp, $ext)){
            $basename = $uniqid.'.'.$ext;
            $filename = $path.$basename;
            if (copy($tmp, $filename)){
                $this->setValue($basename);
                return true;
            }
        }
        return false;
    }
    protected $_changed = false;
    public function setValue($value){
        parent::setValue($value);
        $this->_changed = true;
    }
    public function preUpdate(){
        if ($this->hasChangedValue() || $this->_changed){
            $path = $this->getPath();
            //foreach (glob($path.'tm*_'.$this->getValue()) as $filename){
            //    unlink($filename);
            //}
        }
    }
    public function html(){
        $ext = end(explode('.', $this->getValue()));
        return '<a href="'.$this->source().'">'.$this->getValue().'</a>';
    }
}