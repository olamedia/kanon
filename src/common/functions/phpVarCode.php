<?php
function phpVarCode($value, $tabs = 0){
	$t = str_repeat("\t", $tabs);
	$c = '';
	if (is_array($value)){
		//$t."\t".
		$c .= 'array(';
		$ca = array();
		$nested = false;
		$i = 0;
		foreach ($value as $k => $v){
			if (is_array($v)) $nested = true;
			if (is_int($k) && $k == $i){
				$ca[] = phpVarCode($v, $tabs+1);
			}else{
				$ca[] = phpVarCode($k)." => ".phpVarCode($v, $tabs+1);
			}
			$i++;
		}
		$multiline = count($ca)>1 || $nested;
		if ($multiline){
			$c .= "\r\n".$t."\t\t";
			$splitter = ",\r\n".$t."\t\t";
		}else{
			$c .= '';
			$splitter = ', ';
		}//$t."\t".
		$c .= implode($splitter, $ca);
		if ($multiline){
			$c .= "\r\n".$t."\t";
		}
		$c .= ")";
	}elseif(is_string($value)){
		$c .= "'".str_replace("'","\\'",$value)."'";
	}elseif(is_int($value) || is_float($value)){
		$c .= $value;
	}elseif(is_null($value)){
		$c .= 'null';
	}elseif(is_bool($value)){
		$c .= $value?'true':'false';
	}elseif(is_object($value)){
		$c .= 'null/* object here */';
	}else{
		throw new Exception('Unknown type: '.gettype($value));
	}
	return $c;
}