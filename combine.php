<?php
/**
 * $Id$
 */
class phpCombinator{
	private static $_data = "<?php\r\n";
	private static $_fileData = array();
	private static $_fileRequire = array();
	protected static function _addFiles($files = array(), $path){
		$path = realpath(dirname(__FILE__).'/'.$path);
		foreach (glob($path.'/*',GLOB_NOSORT) as $filePath){
			if (end(explode('.',$filePath)) == 'php'){
				$fileName = basename($filePath);
				if (!in_array($fileName, array('combine.php', 'kanon-framework.php'))){
					$files[$fileName] = $filePath;
				}
			}
		}
		return $files;
	}
	public static function combine($path = '', $finalFilename, $realData = false){
		$files = array();
		if (is_array($path)){
			foreach ($path as $subpath){
				$files = self::_addFiles($files, $subpath);
			}
		}else{
			$files = self::_addFiles($files, $path);
		}
		$require = array();
		$datas = array();
		foreach ($files as $fileName => $filePath){
			echo $fileName.' ';
			$data = file_get_contents($filePath);
			echo 'o ';
			// remove <?php
			$data = preg_replace("#^<\?php#ims", "", $data);
			echo 'r ';
			// remove known requires
			foreach ($files as $knownFileName => $knownFilePath){
				$match = "#((require|include)(_once)?\s*\(?\s*[a-zA-Z0-9\(\)_\.'\"\s/]*".preg_quote($knownFileName,"#")."['\"]*\s*\)?\s*;)#ims";
				if (preg_match($match, $data)){
					$data = preg_replace($match, "", $data);
					self::$_fileRequire[$fileName][] = $knownFileName;
				}
			}
			self::$_fileData[$fileName] = $data;
			echo 'k ';
		}
		$match = "#((require|include)(_once)?[^=;]+['\"];)#ims";
		foreach (self::$_fileData as $fileName => $data){
			self::$_fileData[$fileName] = preg_replace($match, "", $data);
		}
		//			self::$_fileData[$fileName] = $data;
		foreach ($files as $fileName => $filePath){
			self::_put($fileName, $files, $realData);
		}
		file_put_contents($finalFilename, self::$_data);
		//echo self::$_data;
	}
	private static function _put($fileName, $files, $realData = false){
		if (isset(self::$_fileRequire[$fileName])){
			foreach (self::$_fileRequire[$fileName] as $requiredFileName){
				self::_put($requiredFileName, $files, $realData);
			}
		}
		if (isset(self::$_fileData[$fileName])){
			if ($realData){
				self::$_data .= self::$_fileData[$fileName]."\r\n";
			}else{
				self::$_data .= "require_once '".$files[$fileName]."';\r\n";
			}
			unset(self::$_fileData[$fileName]);
		}
	}
}
header("Content-type: text/plain; charset=UTF-8");
phpCombinator::combine(
array(
		'src/common', 
		'src/common/functions', 
		'src/common/plugin', 
		'src/common/extension', 
		'src/common/profiler', 
		'src/events', 
		'src/simpleStorage', 
		'src/simpleStorage/drivers', 
		'src/forms',
		'src/forms/controls',
		'src/mvc-controller',
		'src/mvc-model',
		'src/mvc-model/behaviors',
		'src/mvc-model/behaviors/models',
		'src/mvc-model/properties',
		'src/mvc-model/storageDrivers',
		'src/mvc-view',
		'src/scaffolding',
		'src/media',
		'src/net',
		'src/intl/ru',
),
	'kanon-framework.php',
false
);