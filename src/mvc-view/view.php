<?php
class view{
	protected $_filename = null;
	protected $_uri = null;
	protected $_slots = array();
	protected $_openedSlots = array();
	public function start($name){
		$this->_slots[$name] = '';
		$this->_openedSlots[] = $name;
		ob_start();
	}
	public function end(){
		$name = array_pop($this->_openedSlots);
		$this->_slots[$name] = ob_get_clean();
	}
	public function set($name, $value){
		$this->_slots[$name] = $value;
	}
	public function get($name){
		return $this->_slots[$name];
	}
	public function extendWithFile($filename){

	}
	public function extend(){

	}
	public function setFilename($filename){
		$this->_filename = $filename;
	}
	public function rel($relativeUri){
		$relativeUri = ($relativeUri instanceof uri)?$relativeUri:uri::fromString(strval($relativeUri));
		$rel = new uri();
		$rel->setPath(array_merge($this->_uri->getPath(),$relativeUri));
		return $rel;
	}
	public function setUri($uri){
		$this->_uri = $uri;
	}
	public function render($parameters){
		foreach($parameters as $k => $v){
			$$k = $v;
		}
		include $this->_filename;
	}
	protected function _renderFile(){
		$this->start('_content');

		$this->end();
	}
	public function __set($name, $value){
		$this->set($name, $value);
	}
	public function __get($name){
		return $this->get($name);
	}
}