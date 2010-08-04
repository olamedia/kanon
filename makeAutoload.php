<?php
require_once dirname(__FILE__).'/src/common/functions/is_php.php';

class autoloadGenerator{
	protected $_classes = array();
	protected $_functions = array();
	protected $_declaredClasses = array();
	protected $_definedFunction = array();
	public function create($filename){
		$this->_declaredClasses = get_declared_classes();
		$this->_definedFunction = get_defined_functions();
		$this->lookup(dirname(__FILE__).'/src/');
		$classes = array();
		foreach ($this->_classes as $class => $f){
			$classes[] = "'".$class."'=>'".$f."'";
		}
		$classes = implode(",\r\n", $classes);
		$functions = array();
		foreach ($this->_functions as $func => $f){
			$functions[] = "require_once \$dirname.'".$f."';";
		}
		$functions = implode("\r\n", $functions);
		$php = <<<PHPFILE
<?php
\$dirname = dirname(__FILE__).'/';
require_once \$dirname.'src/common/functions/is_php.php';
\$autoload = array(
$classes
);
kanon::registerAutoload(\$autoload,\$dirname);
$functions
PHPFILE;
		file_put_contents($filename, $php);
	}
	public function rel($f){
		return substr($f, strlen(dirname(__FILE__).'/'));
	}
	public function lookFile($f){
		if (is_php($f)){
			echo $f.' ';
			require_once $f;
			$declaredClasses = get_declared_classes();
			$definedFunction = get_defined_functions();
			$newClasses = array_diff($declaredClasses, $this->_declaredClasses);
			$newFunctions = array_diff($definedFunction, $this->_definedFunction);
			foreach ($newClasses as $class){
				$this->_classes[$class] = $this->rel($f);
			}
			foreach ($newFunctions as $func){
				$this->_functions[$func] = $this->rel($f);
			}
		}
	}
	public function lookup($dir){
		foreach (glob($dir.'*') as $f){
			//echo $f.' ';
			if (is_dir($f)){
				if (in_array(basename($f), array('prototype', 'test', 'tests','tmp'))){
					// skip
				}else{
					echo basename($f).' ';
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
$generator->create('kanon-autoload.php');