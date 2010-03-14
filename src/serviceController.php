<?php
/**
 * $Id$
 */
require_once dirname(__FILE__).'/controller.php';
class serviceController extends controller{
	/**
	 * Service-specific (REST for example) responce for unknown request
	 * 
	 */
	public function notFound($message =''){
		header($_SERVER['SERVER_PROTOCOL']." 500 Internal Server Error");
		header("Content-type: text/plain; charset=UTF-8");
		echo "Unknown request";
		exit;
	}
}