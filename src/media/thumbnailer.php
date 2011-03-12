<?php

#require_once dirname(__FILE__).'/image.php';
#require_once dirname(__FILE__).'/../common/kanon.php';

class thumbnailer{
    protected $_filename = null;
    protected $_basePath = null;
    protected $_baseUrl = null;
    protected $_rel = null;
    protected $_maxSize = 500;
    protected $_image = null;
    protected $_gcPath = null; // garbage collector path
    protected $_gcProb = 0.01; // garbage collector probability
    public function setMaxSize($maxSize = 500){
        $this->_maxSize = $maxSize;
    }
    public function __construct(){
        $requestUri = request::getUri();
        $requestUri = reset(explode('?', $requestUri));
        $this->_filename = basename($requestUri);
        $trace = debug_backtrace();
        $file = $trace[0]['file'];
        $this->_basePath = dirname($file);
        $this->_baseUrl = kanon::getBaseUri();
        //$this->_rel = dirname(substr($requestUri, strlen($this->_baseUrl)));
        $this->_rel = dirname(substr($requestUri, strlen($this->_baseUrl)));
    }
    public function onShutdown(){
        /* if (!$this->_gcProb)
          return;
          mt_srand(microtime(true) * 100000);
          if (mt_rand(0, 1 / $this->_gcProb) != 1)
          return;
          $path = $this->_gcPath;
          foreach (glob($path.'/*') as $file){
          if (!is_file($file))
          continue;
          if (substr(basename($file), 0, 2) != 'tm')
          continue; // minimize possible damage
          if (fileatime($file) < (time() - 60 * 60 * 24 * 14)){ // not accessed for 2 weeks
          //echo $file;
          unlink($file);
          }
          } */
    }
    public function prepareShutdown($path){
        $this->_gcPath = $path;
        register_shutdown_function(array($this, 'onShutdown'));
        ignore_user_abort(true);
        set_time_limit(0);
        //$this->onShutdown();
        //exit;
    }
    public function notFound(){
        response::notFound();
        //$this->prepareShutdown();
    }
    public function getSourcePath($testPrefix = ''){
        $prefix = $this->getPrefix();
        $filename = substr($this->_filename, strlen($prefix) + 1);
        //echo $filename.'?';
        $filePath = $this->_basePath.'/'.dirname($this->_rel).'/'.$testPrefix.$filename;
        //echo $filePath.'?!!! ';
        if (is_file($filePath)){
            return $filePath;
        }else{
            throw new Exception('source file '.$filePath.' not found in directory');
        }
        return false;
    }
    public function getPrefix(){
        if (strpos($this->_filename, '_') !== false){
            return reset(explode('_', $this->_filename));
        }
        return false;
    }
    public function makeThumbnail($filename){
        $this->_image = new image($filename);
        $prefix = $this->getPrefix();
        switch (substr($prefix, 0, 3)){
            case 'tmm':
                if (preg_match("#^tmm([0-9]+)x([0-9]+)$#ims", $prefix, $subs)){
                    if ($subs[1] > $this->_maxSize || $subs[2] > $this->_maxSize)
                        return false;
                    return $this->_image->fit($subs[1], $subs[2]);
                }
                break;
            case 'tmw':
                if (preg_match("#^tmw([0-9]+)$#ims", $prefix, $subs)){
                    if ($subs[1] > $this->_maxSize)
                        return false;
                    return $this->_image->fitWidth($subs[1], 0);
                }
                break;
            case 'tmh':
                if (preg_match("#^tmh([0-9]+)$#ims", $prefix, $subs)){
                    if ($subs[1] > $this->_maxSize)
                        return false;
                    return $this->_image->fitHeight(0, $subs[1]);
                }
                break;
            case 'tmc':
                if (preg_match("#^tmc([0-9]+)x([0-9]+)$#ims", $prefix, $subs)){
                    if ($subs[1] > $this->_maxSize || $subs[2] > $this->_maxSize)
                        return false;
                    return $this->_image->crop($subs[1], $subs[2]);
                }
                break;
            case 'tms':
                if (preg_match("#^tms([0-9]+)x([0-9]+)$#ims", $prefix, $subs)){
                    if ($subs[1] > $this->_maxSize || $subs[2] > $this->_maxSize)
                        return false;
                    return $this->_image->stretch($subs[1], $subs[2]);
                }
                break;
        }
        return false;
    }
    public function optimizePng($filename){
        $info = getimagesize($filename);
        if ($info[2] == IMAGETYPE_PNG){
            exec('which pngcrush', $output, $return);
            if (!count($output) || $return > 0){
                return;
                //throw new RuntimeException('The pngcrush program is not available nor accessible by php');
            }
            $tmpFile = sprintf('%s.tmp', $filename);
            exec(sprintf('pngcrush %s %s 2>/dev/null', escapeshellarg($filename), escapeshellarg($tmpFile)), $output, $return);
            if (file_exists($tmpFile) && filesize($tmpFile) < filesize($filename)){
                copy($tmpFile, $filename);
            }
            unlink($tmpFile);
        }
    }
    public function readFile($filename){
        header('X-Powered-By: Kanon thumbnailer', true);
        $mtime = filemtime($filename);
        $gmt = gmdate('r', $timestamp);
        $if = isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])?$_SERVER['HTTP_IF_MODIFIED_SINCE']:'';
        if ($if == $gmt){
            response::notModified();
        }
        header("Cache-Control: public,max-age=".(3*24*60*60));
        header('Last-Modified: '.$gmt);
        $info = getimagesize($filename);
        $type = $info[2];
        $mime = image_type_to_mime_type($type);
        header('Content-Type: '.$mime);
        readfile($filename);
        exit;
    }
    public function run(){
        //echo 'Filename: '.$this->_filename.'<br />';
        // echo 'Base path: '.$this->_basePath.'<br />';
        // echo 'Base url: '.$this->_baseUrl.'<br />';
        // echo 'Relative: '.$this->_rel.'<br />';
        //var_dump($this);
        $tmFilename = $this->_basePath.'/'.dirname($this->_rel).'/.thumb/'.$this->_filename;
        if (is_file($tmFilename)){
            /*
             * IMPORTANT: Workaround for open_file_cache of nginx
             */
            $this->readFile($tmFilename);
        }
        if (strpos($this->_filename, '_') !== false){
            if (basename($this->_rel) == '.thumb'){
                if (($filename = $this->getSourcePath()) || ($filename = $this->getSourcePath('l_'))){

                    // Check path
                    $path = $this->_basePath.'/'.$this->_rel;
                    if (!is_dir($path)){
                        mkdir($path, 0777, true);
                    }
                    if (is_dir($path)){
                        if (($thumb = $this->makeThumbnail($filename))){
                            // FOUND
                            $this->prepareShutdown($path);
                            if (is_file($tmFilename)){
                                //response::notFound();
                                $this->optimizePng($tmFilename);
                                // Finally, throw away redirects
                                $this->readFile($tmFilename);
                                response::redirect($_SERVER['REQUEST_URI']);
                            }else{
                                //echo $tmFilename;

                                throw new Exception('thumbnail was created but not found 0_o');
                                //response::notFound();
                            }
                        }
                    }else{
                        throw new Exception('can\'t create directory');
                    }
                }else{
                    throw new Exception('source file not found in directory');
                }
            }else{
                var_dump($this);
                throw new Exception('basename is not correct ('.$this->_rel.')');
            }
        }
        $this->notFound();
    }
}