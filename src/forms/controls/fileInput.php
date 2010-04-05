<?php
require_once dirname(__FILE__).'/../control.php';
class fileInput extends control{
	protected $_files = array();
	protected $_filesPrefix = '';
	protected function _getPath(){
		return $this->_options['upload_path'];
	}
	protected function _files(){
		$files = array();
		foreach ($_FILES as $k => $f){
			$files[$k] = array();
			foreach ($f as $fk => $a){
				if (is_array($a)){
					$files[$k] = $this->_arrayAddLastKey($files[$k], $a, $fk);
				}else{
					$files[$k][$fk] = $a;
				}
			}
		}
		return $files;
	}
	protected function _arrayAddLastKey($target, $a, $lastKey){ // tmp_name => ds =>fd
		foreach ($a as $k => $v){
			if (is_array($v)){
				$target[$k] = $this->_arrayAddLastKey($target[$k], $v, $lastKey);
			}else{
				$target[$k][$lastKey] = $v;
			}
		}
		return $target;
	}
	protected function _saveFile($tmpName, $name){
		$ext = '.dat';
		if ($dotpos = strrpos($name, ".")){
			$ext = strtolower(substr($name, $dotpos));
		}
		$fileName = false;
		if (!is_file($tmpName)) return false;
		//var_dump($this->_options);
		//var_dump($this->getControlSet()->getOptions());
		echo kanon::getBasePath().' ';
		echo $this->_getPath().' ';
		$path = realpath(kanon::getBasePath().'/'.$this->_getPath());
		if ($pk = $this->getItemPrimaryKey()){
			$fileName = $path.'/'.$this->_filesPrefix.$pk.$ext;
		}else{
			return false;
		}
		if ($fileName == ''){
			$fileName = $this->_tempnam($path, $this->_filesPrefix, $ext);
		}
		if ($fileName){
			//echo $fileName;
			//exit;
			if (copy($tmpName, $fileName)){
				
				return basename($fileName);
			}
		}
		return false;
	}
	public function beforeSave(){
	}
	public function afterSave(){
		$files = $this->_files();
		$name = $this->getPostName();
		if (!isset($files[$name])) {
			return;
		}
		$key = $this->getPostKey();
		if ($key === null){
			$file = $files[$name];
		}else{
			if (!isset($files[$name][$key])) return;
			$file = $files[$name][$key];
		}
		if (isset($file['tmp_name'])){
			if ($fileName = $this->_saveFile($file['tmp_name'], $file['name'])){
				$this->setValue($fileName);
				if ($property = $this->getProperty()){
					if ($property instanceof imageFilenameProperty){
						$property->unlinkThumbs();
					}
				}
				if ($item = $this->getItem()){
					$item->save();
				}
			}
		}
	}
	public function fillFromPost(){
	}
	public function getHtml(){
		$previewHtml = '';
		if (isset($this->_options['show_preview']) && ($this->_options['show_preview'] == true)){
			if ($property = $this->getProperty()){
				if ($property instanceof imageFilenameProperty){
					$previewHtml = $property->html(100);
				}else{
					$previewHtml = $property->html();
				}
			}
		}
		return '<div>'.$previewHtml.'</div><input class="file" type="file"'.$this->getIdHtml().' name="'.$this->getName().'" value="'.$this->getValue().'" />';
	}
}