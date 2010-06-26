<?php
class view{
	protected $_filename = null;
	protected $_uri = null;
	protected $_slots = array();
	protected $_openedSlots = array();
	protected $_layout = null;
	public function start($name){
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
	public function extend($viewName){
		$this->_layout = $viewName;
	}
	public function setFilename($filename){
		$this->_filename = $filename;
	}
	public function rel($relativeUri){
		$relativeUri = ($relativeUri instanceof uri)?$relativeUri:uri::fromString(strval($relativeUri));
		$rel = new uri();
		$rel->setPath(array_merge($this->_uri->getPath(),$relativeUri->getPath()));
		return $rel;
	}
	public function setUri($uri){
		$this->_uri = $uri;
	}
	public function setView($viewName){
	}
	public function render($viewName, $parameters){
		
	}
	public function show($parameters){
		foreach($parameters as $k => $v){
			$$k = $v;
		}
		$this->start('_content');
		if (!is_file($this->_filename)){
			throw new Exception($this->_filename." not found");
		}
		include kanon::getThemedViewFilename($this->_filename);
		$this->end();
		if ($this->_layout !== null){
			$this->setView($this->_layout);
			$this->show();
		}
		echo $this->get('_content');
	}
	public function __set($name, $value){
		$this->set($name, $value);
	}
	public function __get($name){
		return $this->get($name);
	}
}