<?php
function keep(){
	foreach (func_get_args() as $var){
		if (is_object($var)){
			if (method_exists($var, 'keep')){
				$var->keep();
			}
		}elseif(is_array($var)){
			foreach ($var as $k => $v){
				echo 'keep '.$k;
				keep($v);
			}
		}
	}
}