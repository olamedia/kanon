<?php

/*
 * This file is part of the yuki package.
 * Copyright (c) 2011 olamedia <olamedia@gmail.com>
 *
 * Licensed under The MIT License
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * yLocalFileSystem
 * Local filesystem abstraction layer
 *
 * @package yuki
 * @subpackage file
 * @author olamedia
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */
class yLocalFileSystem extends yFileSystem{
    /**
     * Creates file or modifies atime+mtime of resource
     * @param yFilesystemResource $yResource 
     */
    public function touchResource($yResource){
        touch($yResource->getPath());
    }
    /**
     * Uploads local file into given location.
     * @param string $tmp Local file name.
     * @param yFilesystemResource $yResource Location to upload
     */
    public function uploadResource($tmp, $yResource){
        copy($tmp, $yResource->getPath());
    }
    /**
     * Gets contents of resource.
     * @param yFilesystemResource $yResource
     * @return string
     */
    public function getResourceContents($yResource){
        return file_get_contents($yResource->getPath());
    }
    /**
     * Removes resource at given location if possible
     * @param yFilesystemResource $yResource
     * @return yFilesystemResource
     */
    public function unlinkResource($yResource){
        try{
            if (is_file($yResource->getPath())){
                unlink($yResource->getPath());
            }elseif (is_dir($yResource->getPath())){
                rmdir($yResource->getPath());
            }
        }catch(Exception $e){
            
        }
        return $this->getResource($yResource->getPath());
    }
    /**
     * Checks if resource exists.
     * @param yFilesystemResource $yResource
     * @return boolean
     */
    public function resourceExists($yResource){
        return file_exists($yResource->getPath());
    }
    /**
     * Gets filesystem resource.
     * @param string $path 
     * @return yFilesystemResource
     */
    public function getResource($path){
        if (is_file($path)){
            return new yFile($path, $this);
        }
        if (is_dir($path)){
            return new yDirectory($path, $this);
        }
        return new yFilesystemResource($path, $this); // not existing resource (ex, for uploading)
    }
    /**
     * Makes directory at given location.
     * @param yFilesystemResource $yResource 
     * @return yDirectory
     */
    public function makeResourceDirectory($yResource){
        if (!file_exists($yResource->getPath())){
            mkdir($yResource->getPath());
        }
        return $this->getResource($yResource->getPath());
    }
    /**
     * Removes resource at given location.
     * If second paremeter is true, forces recursive removing.
     * @param yFilesystemResource $yResource 
     * @param boolean $force
     */
    public function removeResource($yResource, $force = false){
        if ($force){
            foreach (glob($yResource->getPath().'/*') as $f){
                $rn = basename($f);
                $yResource->getResource($rn)->remove($force);
            }
        }
        $this->unlinkResource($yResource);
    }
}

