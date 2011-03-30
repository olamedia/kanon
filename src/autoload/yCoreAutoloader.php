<?php

/*
 * This file is part of the yuki package.
 * Copyright (c) 2011 olamedia <olamedia@gmail.com>
 *
 * This source code is release under the MIT License.
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * yCoreAutoloader
 *
 * @package yuki
 * @subpackage autoload
 * @author olamedia
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @version SVN: $Id: yCoreAutoloader.php 149 2011-02-20 06:58:24Z olamedia@gmail.com $
 */
class yCoreAutoloader extends yAutoloader{
    protected static $_instance = null;
    /**
     * @internal
     * @var array
     */
    protected $_classes = array(
        'yAutoloader'=>'autoload/yAutoloader.php',
        'yCoreAutoloader'=>'autoload/yCoreAutoloader.php',
        'yDebugger'=>'debug/yDebugger.php',
        'yDirectory'=>'file/yDirectory.php',
        'yDocBlock'=>'doc/yDocBlock.php',
        'yDocumentor'=>'doc/yDocumentor.php',
        'yFile'=>'file/yFile.php',
        'yFileInput'=>'forms/yFileInput.php',
        'yFileSystem'=>'file/yFileSystem.php',
        'yFormControl'=>'forms/yFormControl.php',
        'yFormControlSet'=>'forms/yFormControlSet.php',
        'yFormResultIterator'=>'forms/yFormResultIterator.php',
        'yHeadTag'=>'html/yHeadTag.php',
        'yHtmlAttribute'=>'html/yHtmlAttribute.php',
        'yHtmlHelper'=>'html/yHtmlHelper.php',
        'yHtmlTag'=>'html/yHtmlTag.php',
        'yHtmlTagList'=>'html/yHtmlTagList.php',
        'yIFormControlValidator'=>'forms/yIFormControlValidator.php',
        'yLocalFileSystem'=>'file/yLocalFileSystem.php',
        'yMetaTag'=>'html/yMetaTag.php',
        'yMethodDocumentor'=>'doc/yMethodDocumentor.php',
        'yStyleTag'=>'html/yStyleTag.php',
        'yTextInput'=>'forms/yTextInput.php',
        'yTextNode'=>'html/yTextNode.php',
        'yUri'=>'uri/yUri.php',
        'yUriAuthority'=>'uri/yUriAuthority.php',
        'yUriFragment'=>'uri/yUriFragment.php',
        'yUriPath'=>'uri/yUriPath.php',
        'yUriQuery'=>'uri/yUriQuery.php',
        'yUriScheme'=>'uri/yUriScheme.php',
        'yValidatorException'=>'forms/yValidatorException.php',
        'yVirtualFileSystem'=>'file/yVirtualFileSystem.php',
    );
    protected function __construct(){
        $this->_locationPrefix = realpath(dirname(__FILE__).'/..').'/';
    }
    /**
     *
     * @return yCoreAutoloader
     */
    public static function getInstance(){
        if (self::$_instance === null){
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    /**
     * Rebuilds the association array between class names and paths.
     * Idea (to rebuild self) taken from Symfony sfCoreAutoload
     *
     * @internal
     * @param string $path
     * @param boolean $finalize
     */
    public function rebuild($path = '', $finalize = true){
        if ($finalize){
            ob_implicit_flush(true);
            echo "Rebuilding yCoreAutoloader...\n";
            $this->_classes = array();
        }
        $locationPrefix = substr($path, 0, strlen($path) - 1).'/';
        $searchpath = realpath(dirname(__FILE__).'/..').'/'.$path;
        foreach (glob($searchpath.'*', GLOB_NOSORT) as $filename){
            $name = basename($filename);
            if (is_dir($filename)){
                $this->rebuild($path.$name.'/', false);
            }elseif (is_file($filename)){
                if ($name{0} == 'y'){
                    $class = reset(explode('.', $name));
                    $this->add(array($class=>$name), $locationPrefix);
                }
            }
        }
        if (!$finalize){
            return;
        }
        ksort($this->_classes);
        $classesString = 'protected $_classes = array('."\n";
        foreach ($this->_classes as $k=>$v){
            $classesString .= "        '".$k."'=>'".$v."',\n";
        }
        $classesString .= "    );";
        $contents = file_get_contents(__FILE__);
        $contents = preg_replace("#(protected\s+\\\$_classes\s*=\s*array\([^)]*\);)#ims", $classesString, $contents);
        file_put_contents(__FILE__, $contents);
        echo "Finished.\n";
    }
    /**
     * Handles autoloading of classes.
     * @param string $class A class name.
     * @return boolean Returns true if the class has been loaded 
     */
    public function autoload($class){
        if ($class{0} === 'y'){ // all core classes are prefixed with "y"
            return parent::autoload($class);
        }
        return false;
    }
}

