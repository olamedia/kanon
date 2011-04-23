<?php
// TODO
class form{
	protected $_enctype = null;
	protected function __construct($url, $method="GET", $upload = false){
		if ($upload) $this->_enctype = "multipart/form-data";
		ob_start();
		echo '<form action="'.$url.'" method="'.$method.'"';
		if ($upload) echo ' enctype="multipart/form-data"';
		echo '>';
	}
	public static function start(){
		$args = func_get_args();
		return call_user_func_array(array(self,'__construct'), $args);
	}
	public function inputText(){
		
	}
}