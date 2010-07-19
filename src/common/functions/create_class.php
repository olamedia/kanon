<?php
function create_class($class, $prototype){
	if (!class_exists($class)){
		eval('class '.$class.' extends '.$prototype.' {}');
	}
}