<?php
function kanonErrorHandler($severity, $message, $file_name, $line_number){
	if (error_reporting() == 0){
		return;
	}
	if (error_reporting() & $severity){
		throw new ErrorException($message, 0, $severity, $file_name, $line_number);
	}
}