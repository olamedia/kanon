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

require_once dirname(__FILE__) . '/src/common/functions/is_php.php';
/* if (is_php(dirname(__FILE__).'/kanon-autoload.php')){
  require_once(dirname(__FILE__).'/kanon-autoload.php');
  } */

class autoloadGenerator {

    protected $_classes = array();
    protected $_functions = array();
    protected $_declaredClasses = array();
    protected $_definedFunctions = array();
    protected $_skipFiles = array();
    protected $_skippedFiles = array();
    protected $_skipClasses = array();

    public function autoload($class) {
	if (isset($this->_classes[$class])) {
	    $f = dirname(__FILE__) . '/' . $this->_classes[$class];
	    include_once $f;
	    $declaredClasses = array_merge(get_declared_classes(), get_declared_interfaces());
	    $newClasses = array_diff($declaredClasses, $this->_declaredClasses);
	    if ($k = array_search($class, $newClasses)) {
		unset($newClasses[$k]);
	    }
	    foreach ($newClasses as $newClass) {
		if ($k = array_search($newClass, $declaredClasses)) {
		    unset($declaredClasses[$k]);
		}
	    }
	    $this->_declaredClasses = $declaredClasses;
	} else {
	    echo " FAILED:" . $class . " ";
	    $this->_skipClasses[$class] = $class;
	    throw new Exception($class);
	}
    }

    public function create($filename) {
	include 'cache.php';
	$this->_classes = $autoload;
	$this->_declaredClasses = array_merge(get_declared_classes(), get_declared_interfaces());
	$functions = get_defined_functions();
	$this->_definedFunctions = $functions['user'];
	$this->lookup(dirname(__FILE__) . '/src/');
	$this->lookup(dirname(__FILE__) . '/incubator/');
	if (count($this->_skippedFiles)) {
	    echo "=====================================\r\n";
	    echo "SOME FILES SKIPPED, RUN AGAIN!!!\r\n";
	} else {
	    echo "=====================================\r\n";
	    echo "FINISHED\r\n";
	}
	$classes = array();
	ksort($this->_classes);
	foreach ($this->_classes as $class => $f) {
	    $classes[] = "'" . $class . "'=>'" . $f . "'";
	}
	$classes = implode(",\r\n", $classes);
	$functions = array();
	$this->_functions = array_unique($this->_functions);
	foreach ($this->_functions as $func => $f) {
	    $functions[] = "require_once \$dirname.'" . $f . "';";
	}
	$functions = implode("\r\n", $functions);
	$php = <<<PHPFILE
<?php
\$autoload = array($classes);
PHPFILE;
	file_put_contents('cache.php', $php);
	$php = <<<PHPFILE
<?php
\$dirname = dirname(__FILE__).'/';
require_once \$dirname.'src/common/kanon.php';
require_once \$dirname.'src/common/functions/is_php.php';
kanon::registerAutoload(array(
$classes
),\$dirname);
register_shutdown_function(array('kanon', 'onShutdown'));
if (function_exists('spl_autoload_register')){
	spl_autoload_register(array('kanon', 'autoload'));
	spl_autoload_register(array('plugins', 'autoload'));
}else{
	function __autoload(\$name){
		if (!kanon::autoload(\$name)){
			plugins::autoload(\$name);
		}
	}
}
$functions
set_exception_handler(array('kanonExceptionHandler', 'handle'));
set_error_handler('kanonErrorHandler');
PHPFILE;
	file_put_contents($filename, $php);
    }

    public function rel($f) {
	return substr($f, strlen(dirname(__FILE__) . '/'));
    }

    public function lookFile($f) {
	/* if (in_array($f, $this->_skipFiles)){
	  return;
	  }
	  $this->_skipFiles[] = $f; */
	if (is_php($f)) {
	    echo "\t" . basename($f);
	    try {
		include_once $f;
		$declaredClasses = array_merge(get_declared_classes(), get_declared_interfaces());
		$newClasses = array_diff($declaredClasses, $this->_declaredClasses);
		$this->_declaredClasses = $declaredClasses;
		$functions = get_defined_functions();
		$definedFunctions = $functions['user'];
		$newFunctions = array_diff($definedFunctions, $this->_definedFunctions);
		$this->_definedFunctions = $definedFunctions;
		foreach ($newClasses as $class) {
		    if (isset($this->_skipClasses[$class])) {
			echo "\t\t\t" . ' SKIP ';
			echo " FOUND:" . 'class ' . $class . ' ';
			$this->_classes[$class] = $this->rel($f);
			$newClasses = array(); // !!!
			break;
		    }
		}
		if (count($newClasses)) {
		    echo "\t\t\t" . ' OK ';
		    foreach ($newClasses as $class) {
			echo " " . 'class ' . $class . ' ';
			$this->_classes[$class] = $this->rel($f);
		    }
		} else {
		    $classes = array_keys($this->_classes, $this->rel($f));
		    echo "\t\t\t" . ' OK ';
		    foreach ($classes as $class) {
			echo " " . 'class ' . $class . ' ';
		    }
		}
		foreach ($newFunctions as $func) {
		    echo " " . '@ function ' . $func . ' ';
		    $this->_functions[$func] = $this->rel($f);
		}
		unset($this->_skippedFiles[$f]);
	    } catch (Exception $e) {
		echo ' skip... ';
		$newClasses = array(); //$e->getMessage());
		$this->_skippedFiles[$f] = $f;
	    }
	    echo "\r\n";
	}
    }

    public function lookup($dir) {
	foreach (glob($dir . '*') as $f) {
//echo $f.' ';
	    if (is_dir($f)) {
		if (in_array(basename($f), array('prototype', 'test', 'tests', 'tmp', '_html'))) {
// skip
		} else {
		    echo "\r\n" . $this->rel($f) . '\ ' . "\r\n";
		    $this->lookup($f . '/');
		    echo "\r\n" . $this->rel($dir) . '\ ' . "\r\n";
		}
	    } elseif (is_file($f)) {
		if (substr($f, -4, 4) == '.php') {
		    $this->lookFile($f);
		}
	    }
	}
    }

}

$generator = new autoloadGenerator();
spl_autoload_register(array($generator, 'autoload'));
$generator->create('kanon-autoload.php');