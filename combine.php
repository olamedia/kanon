<?php
class phpCombinator{
	private static $_data = '';
	public static function combine($path = ''){
		$path = realpath(dirname(__FILE__).'/'.$path);
		$files = array();
		foreach (glob($path.'/*',GLOB_NOSORT) as $filePath){
			if (end(explode('.',$filePath)) == 'php'){
				$fileName = basename($filePath);
				if (!in_array($fileName, array('combine.php', 'kanon-framework.php'))){
					$files[$fileName] = $filePath;
				} 
			}
		}
		foreach ($files as $fileName => $filePath){
			echo $fileName.'<br />';
			$data = file_get_contents($filePath);
			// remove <?php
			$data = preg_replace("#^<\?php#ims", "", $data);
			// remove known requires
			foreach ($files as $knownFileName => $knownFilePath){
				$data = preg_replace("#((require|include)(_once)?\s*\(?\s*[a-zA-Z0-9\(\)_\.'\"\s/]*".preg_quote($knownFileName,"#")."['\"]*\s*\)?\s*;)#ims", "", $data);
			}
			self::$_data .= $data."\r\n";
		}
		file_put_contents('kanon-framework.php', self::$_data);
		//echo self::$_data;
	}
}
header("Content-type: text/plain; charset=UTF-8");
phpCombinator::combine();