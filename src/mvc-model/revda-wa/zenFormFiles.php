<?php
class fileInput extends propertyControl{
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
			$ext = substr($name, $dotpos);
		}
		$fileName = false;
		if (!is_file($tmpName)) return false;
		$path = realpath($this->_getPath());
		if ($pk = $this->getItemPrimaryKey()){
			$fileName = $path.'/'.$this->_filesPrefix.$pk.$ext;
		}else{
			return false;
		}
		if ($fileName == ''){
			$fileName = $this->_tempnam($path, $this->_filesPrefix, $ext);
		}
		if ($fileName){
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
/*
class filesInput extends fileInput{
	public function getHtml(){
		$inh = '<div><input type="file" id="'.$this->getId().'" name="'.$this->getName().'[]" /></div>';
		return $inh.'
		<div id="'.$this->getId().'_append_target"></div>
		<script type="text/javascript">
		function apph_'.$this->getId().'(){
			h = \''.addslashes($inh).'\';
			e = document.getElementById(\''.$this->getId().'_append_target\');
			e.innerHTML = e.innerHTML + h;
		}
		</script>
		<a href="javascript:apph_'.$this->getId().'()">еще</a>';
	}
}
class imageFileInput extends fileInput{
	protected $_filesPrefix = 'l_';
	public function checkIsImage(){
		if (!$this->isImage($this->getValue())){
			$this->setValue('');
		}
	}
	protected function isImage($filename){
		//echo $filename;
		if ($filename == '') return false;
		if ($info = getimagesize($filename)){
			if (in_array($info[2], array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG))){
				//if ($p = strpos($filename, ".")){
				//	$ext = substr($filename, $p);
				//	if (is_array($ext, array('jpg', 'jpeg', 'png', 'gif'))){
						return true;
				//	}
				//}
			}
		}
		return false;
	}
	public function beforeSave($files = null){
		if ($files === null) $files = $this->_files;
		if ($files === null) {
			$this->setValue('');
		}
		$this->checkIsImage();
	}
	public function afterSave($files = null){
		if ($files === null) $files = $this->_files;
		//var_dump($files);
		if (count($files)){
			$savedFiles = array();
			if (isset($files['tmp_name'])){
				if ($this->isImage($files['tmp_name'])){
				if ($fileName = $this->_saveFile($files['tmp_name'], $files['name'])){
					$savedFiles[] = $fileName;
				}
				}
			}else{
				foreach ($files as $file){
					if ($this->isImage($files['tmp_name'])){
					if ($fileName = $this->_saveFile($file['tmp_name'], $file['name'])){
						$savedFiles[] = $fileName;
					}
					}
				}
			}
			$this->setValue(implode(",", $savedFiles));
			if ($item = $this->_getItem()){
				$item->save();
			}
		}else{
			if ($files === null) {
				// first call
				$this->setValue('');
			}
		}
	}
	public function getKeys(){
		$name = ($this->_prefix===null?'':$this->_prefix.'_').$this->_name;
		if (isset($_FILES[$name]['tmp_name'])){
			if (is_array($_FILES[$name]['tmp_name'])){
				$keys = array();
				foreach ($_FILES[$name]['tmp_name'] as $key => $tmpName){
					if ($this->isImage($_FILES[$name]['tmp_name'][$key])) $keys[] = $key;
				}
				return $keys;
			}else{
				if ($this->isImage($_FILES[$name]['tmp_name'])) return array(null);
			}
		}
		return false;
	}
	public function inPost($key = null){
		$name = ($this->_prefix===null?'':$this->_prefix.'_').$this->_name;
		if (($key = parent::inPost($key)) !== false){
			if ($key === null){
				if ($this->isImage($_FILES[$name]['tmp_name'])) return null;
			}else{
				if ($this->isImage($_FILES[$name]['tmp_name'][$key])) return $key;
			}
		}
		return false;
	}
}*/