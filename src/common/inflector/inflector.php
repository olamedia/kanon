<?php
class inflector{
	public static function lowerCamelCase($string){
		$a = preg_split('/\p{Zs}/', $string, -1, PREG_SPLIT_NO_EMPTY);
		$f = strtolower(array_shift($a));
		foreach ($a as &$s){
			$s = ucfirst($s);
		}
		return $f.implode('',$a);
	}
	public static function underscore($string){
		$s = preg_replace('/(.)([\p{Lu}])/u', '\1 \2', $string); // split camel case
		$a = preg_split('/\p{Zs}/', $s, -1, PREG_SPLIT_NO_EMPTY);
		return implode('_',$a);
	}
}