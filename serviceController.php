<?php
class serviceController extends applicationController{
	/**
	 * Service-specific (REST for example) responce for unknown request
	 * 
	 */
	protected function notFound($message =''){
		header($_SERVER['SERVER_PROTOCOL']." 500 Internal Server Error");
		header("Content-type: text/plain; charset=UTF-8");
		echo "Unknown request";
		exit;
	}
}