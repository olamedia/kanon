<?php
/**
 * MVC: View
 * Modelled after symfony 2.0
 * $Id$
 * @author olamedia
 *
 */
class view{
	protected $_filename = null;
	protected $_uri = null;
	protected $_slots = array();
	protected $_openedSlots = array();
	protected $_layout = null;
	protected $_layoutParameters = array();
	public function start($name){
		$this->_openedSlots[] = $name;
		ob_start();
	}
	public function end(){
		$name = array_pop($this->_openedSlots);
		$this->_slots[$name] = ob_get_contents();
		ob_end_clean();
	}
	public function set($name, $value){
		$this->_slots[$name] = $value;
	}
	public function get($name){
		return $this->_slots[$name];
	}
	public function extend($filename, $parameters = array()){
		$this->_layout = $filename;
		$this->_layoutParameters = $parameters;
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
	public function render($filename, $parameters, $uri = null){
		if ($uri === null) $uri = $this->_uri;
		$view = new view();
		$view->setFilename(dirname($this->_filename).'/'.$filename);
		$view->setUri($uri);
		$view->show($parameters);
	}
	public function show($parameters){
		foreach($parameters as $k => $v){
			$$k = $v;
		}
		$this->start('_content');
		if (!is_file($this->_filename)){
			throw new Exception($this->_filename." not found");
		}
		$view = &$this;
		include kanon::getThemedViewFilename($this->_filename);
		$this->_content = '';
		$this->end();
		if ($this->_layout !== null){
			$this->_filename = dirname($this->_filename).'/'.$this->_layout;
			$this->_layout = null;
			$parameters = $this->_layoutParameters;
			$this->_layoutParameters = null;
			$this->show($parameters);
		}else{
			echo $this->get('_content');
		}
	}
	public function __set($name, $value){
		$this->set($name, $value);
	}
	public function __get($name){
		return $this->get($name);
	}
}