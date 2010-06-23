<?php
class view{
	protected $_slots = array();
	protected $_openedSlots = array();
	public function start($name){
		$this->_slots[$name] = '';
		$this->_openedSlots[] = $name;
		ob_start();
	}
	public function end(){
		$name = array_pop($this->_openedSlots);
		$this->_slots[$name] = ob_get_contents();
		ob_end_clean();
	}
	public function extendWithFile($filename){
		
	}
	public function extend(){
		
	}
	protected function _renderFile(){
		$this->start('_content');
		
		$this->end();
	}
}