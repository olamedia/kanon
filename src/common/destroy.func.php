<?php
function destroy(){
	foreach (func_get_args() as $var){
		if (is_object($var)){
			$var->__destruct();
		}elseif(is_array($var)){
			foreach ($var as $k => $v){
				destroy($v);
			}
		}
		unset($var);
	}
}