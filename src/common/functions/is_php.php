<?php
function is_php($file) {
	exec("php -l $file",$error,$code);
	if($code==0) return true;
	return false;
}