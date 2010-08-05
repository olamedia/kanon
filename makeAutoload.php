<?php
require_once dirname(__FILE__).'/src/common/functions/is_php.php';

class autoloadGenerator{
	protected $_classes = array();
	protected $_functions = array();
	protected $_declaredClasses = array();
	protected $_definedFunctions = array();
	protected $_skipFiles = array();
	public function autoload($class){
		echo " autoload... ";
		//$this->lookup(dirname(__FILE__).'/src/');
		if (!class_exists($class)){
			throw new Exception($class);
		}
	}
	public function create($filename){
		$this->_declaredClasses = array_merge(get_declared_classes(), get_declared_interfaces());
		$functions = get_defined_functions();
		$this->_definedFunctions = $functions['user'];
		$this->lookup(dirname(__FILE__).'/src/');
		$classes = array();
		ksort($this->_classes);
		foreach ($this->_classes as $class => $f){
			$classes[] = "'".$class."'=>'".$f."'";
		}
		$classes = implode(",\r\n", $classes);
		$functions = array();
		$this->_functions = array_unique($this->_functions);
		foreach ($this->_functions as $func => $f){
			$functions[] = "require_once \$dirname.'".$f."';";
		}
		$functions = implode("\r\n", $functions);
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
set_exception_handler(array('kanonExceptionHandler', 'handle'));
set_error_handler('kanonErrorHandler');
$functions
PHPFILE;
		file_put_contents($filename, $php);
	}
	public function rel($f){
		return substr($f, strlen(dirname(__FILE__).'/'));
	}
	public function lookFile($f){
		if (in_array($f, $this->_skipFiles)){
			return;
		}
		$this->_skipFiles[] = $f;
		if (is_php($f)){
			echo "\t".basename($f);
			try{
				include_once $f;
				$declaredClasses = array_merge(get_declared_classes(), get_declared_interfaces());
				$newClasses = array_diff($declaredClasses, $this->_declaredClasses);
				$this->_declaredClasses = $declaredClasses;
			}catch(Exception $e){
				$newClasses = array($e->getMessage());
			}
			$functions = get_defined_functions();
			$definedFunctions = $functions['user'];
			$newFunctions = array_diff($definedFunctions, $this->_definedFunctions);
			$this->_definedFunctions = $definedFunctions;
			foreach ($newClasses as $class){
				echo " ".'class '.$class.' ';
				$this->_classes[$class] = $this->rel($f);
			}
			foreach ($newFunctions as $func){
				echo " ".'@ function '.$func.' ';
				$this->_functions[$func] = $this->rel($f);
			}
			echo "\r\n";
		}
	}
	public function lookup($dir){
		foreach (glob($dir.'*') as $f){
//echo $f.' ';
			if (is_dir($f)){
				if (in_array(basename($f), array('prototype', 'test', 'tests', 'tmp'))){
// skip
				}else{
					echo "\r\n".$this->rel($f).'\ '."\r\n";
					$this->lookup($f.'/');
				}
			}elseif (is_file($f)){
				if (substr($f, -4, 4)=='.php'){
					$this->lookFile($f);
				}
			}
		}
	}
}

$generator = new autoloadGenerator();
spl_autoload_register(array($generator, 'autoload'));
$generator->create('kanon-autoload.php');