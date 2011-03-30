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
 * yDocumentor
 *
 * @package yuki
 * @subpackage doc
 * @author olamedia
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @version SVN: $Id: yDocumentor.php 148 2011-02-20 04:27:36Z olamedia@gmail.com $
 */
class yDocumentor{
    protected static $_instance = null;
    protected static $_docPath = '../../doc';
    protected $_classes = array();
    protected $_packages = array();
    protected $_classPackages = array();
    public static function getInstance(){
        if (self::$_instance === null){
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    public function rebuild($path = '', $finalize = true){
        if ($finalize){
            ob_implicit_flush(true);
            echo "Rebuilding API documentation.";
            $this->_classes = array();
        }else{
            echo ".";
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
                    $this->_classes[$class] = $locationPrefix.$name;
                }
            }
        }
        if (!$finalize){
            return;
        }
        $projectHomepage = 'http://code.google.com/p/yuki/';
        $projectLogo = 'http://code.google.com/p/yuki/logo?cct=1297980533';
        // http://alexgorbatchev.com/SyntaxHighlighter/
        $header = <<<HTML
<html>
<head>
<meta charset="utf-8" />
<link type="text/css" rel="stylesheet" href="style.css" />
<link href="http://alexgorbatchev.com/pub/sh/current/styles/shCore.css" rel="stylesheet" type="text/css" />
<link href="http://alexgorbatchev.com/pub/sh/current/styles/shThemeRDark.css" rel="stylesheet" type="text/css" />
<script src="http://alexgorbatchev.com/pub/sh/current/scripts/shCore.js" type="text/javascript"></script>
<script src="http://alexgorbatchev.com/pub/sh/current/scripts/shAutoloader.js" type="text/javascript"></script>
<script src="http://alexgorbatchev.com/pub/sh/current/scripts/shBrushPhp.js" type="text/javascript"></script>
<script type="text/javascript">
SyntaxHighlighter.all();
</script>
</head>
<body>
<div class="header" style="border-top: 1px solid #C9D7F1;margin: 9px;margin-top: 24px;padding-top: 5px;margin-bottom: 10px;">
    <a href="$projectHomepage"><img src="$projectLogo" /></a>
    <div style="display: inline-block;padding-left: 11px;">
        <div class="project-name">
            <a style="text-decoration: none;color: #666;font-family: arial, sans-serif;font-size: 39px;" href="$projectHomepage">yuki</a>
        </div>
        <div class="project-summary">
            <a href="$projectHomepage" style="text-decoration: none;color: #444;font-family: arial, sans-serif;font-size: 13px;">Yuki PHP Framework</a>
        </div>
    </div>
</div>
HTML;

        ksort($this->_classes);
        $this->_packages = array(
            'yuki'=>array()
        );
        foreach ($this->_classes as $class=>$location){
            //echo "$class => $location\n";
            require_once realpath(dirname(__FILE__).'/..').'/'.$location;
            $me = new ReflectionClass($class);
            $classDoc = $me->getDocComment();
            $package = 'default';
            if (preg_match("#@package\s+([a-z0-9_]+)#ims", $classDoc, $subs)){
                $package = $subs[1];
            }
            $subpackage = 'default';
            if (preg_match("#@subpackage\s+([a-z0-9_]+)#ims", $classDoc, $subs)){
                $subpackage = $subs[1];
            }
            $this->_packages[$package][$subpackage][] = $class;
            $this->_classPackages[$class] = array($package, $subpackage);
        }
        foreach ($this->_classes as $class=>$location){
            $me = new ReflectionClass($class);
            $classDoc = $me->getDocComment();
            $classConstants = $me->getConstants();
            $classProperties = $me->getProperties();
            $classMethods = $me->getMethods();
            list($classPackage, $classSubpackage) = $this->_classPackages[$class];

            $lines = explode("\n", $classDoc);
            array_shift($lines);
            array_pop($lines);
            $desc = array();
            foreach ($lines as $k=>$line){
                $lines[$k] = $line = ltrim($line, "* \t");
                if (strlen($line) && $line{0} == '@'){
                    // annotation
                }else{
                    $desc[] = $line;
                }
            }
            $desc = nl2br(implode("\n", $desc));
            //echo $classDoc;
            $classDocFilename = realpath(dirname(__FILE__).'/'.self::$_docPath).'/'.$class.'.html';
            $sidebar = '<div class="sidebar">';
            $sidebar .= '<ul>';
            foreach ($this->_packages as $package=>$subpackages){
                $sidebar .= '<li class="package">';
                $sidebar .= $package;
                if ($package == $classPackage){
                    $sidebar .= '<ul>';
                    foreach ($subpackages as $subpackage=>$sclasses){
                        $sidebar .= '<li class="subpackage">';
                        $sidebar .= $subpackage;
                        $sidebar .= '<ul>';
                        foreach ($sclasses as $sclass){
                            $sidebar .= '<li class="class">';
                            $sidebar .= '<a href="'.$sclass.'.html">'.$sclass.'</a>';
                            $sidebar .= '</li>';
                        }
                        $sidebar .= '</ul>';
                        $sidebar .= '</li>';
                    }
                    $sidebar .= '</ul>';
                }
                $sidebar .= '</li>';
            }
            $sidebar .= '</ul>';
            $sidebar .= '</div>';
            $propertiesHtml = '';
            foreach ($classProperties as $property){
                $propertiesHtml .= '<div class="property">';
                if ($property->isPublic()){
                    $propertiesHtml .= 'public ';
                }
                if ($property->isPrivate()){
                    $propertiesHtml .= 'private ';
                }
                if ($property->isProtected()){
                    $propertiesHtml .= 'protected ';
                }
                if ($property->isStatic()){
                    $propertiesHtml .= 'static ';
                }
                $propertiesHtml .= '<a href="#property.'.$property->getName().'">$'.$property->getName().'</a>';
                $propertiesHtml .= ';';
                $propertiesHtml .= '</div>';
            }
            $hier = '';
            $childs = array();
            foreach ($this->_classes as $cclass=>$location){
                if (get_parent_class($cclass) == $class){
                    $childs[] = $cclass;
                }
            }
            $parents = class_parents($class);
            if (count($parents) || count($childs)){
                $hier = '<pre>';
                $hierClasses = $parents + array($class);
                $i = 0;
                foreach ($hierClasses as $hclass){
                    $hier .= '<div>'.str_repeat("    └ ", $i);
                    if ($hclass == $class){
                        $hier .= '<b>';
                    }else{
                        $hier .= '<a style="color: #666; text-decoration: none; border-bottom: dashed 1px #999;" href="'.$hclass.'.html">';
                    }
                    $hier .= $hclass;
                    if ($hclass == $class){
                        $hier .= '</b>';
                    }else{
                        $hier .= '</a>';
                    }
                    $hier .= '</div>';
                    $i++;
                }
                foreach ($childs as $hclass){
                    $hier .= '<div>'.str_repeat("    └ ", $i);
                    if ($hclass == $class){
                        $hier .= '<b>';
                    }else{
                        $hier .= '<a style="color: #666; text-decoration: none; border-bottom: dashed 1px #999;" href="'.$hclass.'.html">';
                    }
                    $hier .= $hclass;
                    if ($hclass == $class){
                        $hier .= '</b>';
                    }else{
                        $hier .= '</a>';
                    }
                    $hier .= '</div>';
                }
                $hier .= '</pre>';
            }
            $extends = '';
            if ($parents = class_parents($class)){
                $hier .= '<pre>';
                foreach ($parents as $parent){
                    
                }
                $hier .= '</pre>';
                $parent = reset($parents);
                $extends .= ' extends <a href="'.$parent.'.html">'.$parent.'</a>';
            }
            if ($interfaces = class_implements($class)){
                $extends .= ' implements '.implode(', ', $interfaces);
            }
            $methodsHtml = '';
            $methodsSection = '';
            $methodsSummary = '';
            foreach ($classMethods as $method){
                $yMethod = new yMethodDocumentor($method);
                $methodsHtml .= $yMethod->getSynopsisHtml();
                $methodsSection .= $yMethod->getSectionHtml();
                if ($method->isPublic()){
                    $methodsSummary .= $yMethod->getSummaryHtml();
                }
                /* $methodsHtml .= '<div class="method">';
                  if ($method->isPublic()){
                  $methodsHtml .= 'public ';
                  }
                  if ($method->isPrivate()){
                  $methodsHtml .= 'private ';
                  }
                  if ($method->isProtected()){
                  $methodsHtml .= 'protected ';
                  }
                  if ($method->isStatic()){
                  $methodsHtml .= 'static ';
                  }
                  $methodsHtml .= '<a href="#property.'.$method->getName().'">'.$method->getName().'</a>';
                  $methodsHtml .= ' ( ';
                  $args = array();
                  $optArgs = array();
                  foreach ($method->getParameters() as $parameter){
                  //$parameter = new ReflectionParameter();
                  $arg = '<span class="param">$'.$parameter->getName().'</span>';
                  if ($parameter->isDefaultValueAvailable()){
                  $val = $parameter->getDefaultValue();
                  $arg .= ' = ';
                  if (is_string($val)){
                  $arg .= '"'.$val.'"';
                  }else{
                  $arg .= $val;
                  }
                  }
                  $parameter->isPassedByReference();
                  if ($parameter->isOptional()){
                  $optArgs[] = $arg;
                  }else{
                  $args[] = $arg;
                  }
                  }
                  $argString = implode(', ', $args);
                  if (count($optArgs)){
                  $optString = implode(' [, ', $optArgs).str_repeat(']', count($optArgs) - 1);
                  }else{
                  $optString = '';
                  }
                  if (strlen($optString)){
                  if (strlen($argString)){
                  $argString .= ' [, '.$optString.']';
                  }else{
                  $argString = '['.$optString.']';
                  }
                  }
                  $methodsHtml .= strlen($argString)?$argString:'void';
                  $methodsHtml .= ' );';
                  $methodsHtml .= '</div>'; */
            }
            $html = <<<HTML
$header
<table width="100%"><tr><td width="200" valign="top">
$sidebar            
</td><td valign="top">
            
<h1>$class</h1>
<div><b>@package</b> $classPackage</div>
<div><b>@subpackage</b> $classSubpackage</div>
$hier
<!-- Hierarhy -->

<h2>Introduction</h2>
$desc


<h2>Method Summary</h2>
$methodsSummary
<h2>Method Details</h2>
$methodsSection


<h2>Class synopsis</h2>
<div class="classsynopsis">
<b class="classname">$class</b>$extends {
<div class="contents">
<h3>/* Properties */</h3>
$propertiesHtml
<h3>/* Methods */</h3>
$methodsHtml
</div>
}
</div>


</td></tr></table>
</body>
</html>
HTML;
            $html = str_replace("\r\n", "\n", $html); // Consistent new lines
            $html = str_replace("\r", "\n", $html);
            file_put_contents($classDocFilename, $html);
        }
        $html = <<<HTML
$header
<table width="100%"><tr><td width="200" valign="top">
$sidebar            
</td><td valign="top">
<h1>Yuki PHP Framework</h1>

</td></tr></table>
</body>
</html>
HTML;
        $indexFilename = realpath(dirname(__FILE__).'/'.self::$_docPath).'/index.html';
        file_put_contents($indexFilename, $html);
        $styleFilename = realpath(dirname(__FILE__).'/'.self::$_docPath).'/style.css';
        file_put_contents($styleFilename, file_get_contents(dirname(__FILE__).'/style.css'));
        echo "\nFinished.\n";
    }
}

