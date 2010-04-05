<?php
require_once dirname(__FILE__).'/fileInput.php';
class imageFileInput extends fileInput{
	protected $_filesPrefix = 'l_';
	public function checkIsImage(){
		if (!$this->isImage($this->getValue())){
			$this->setValue('');
		}
	}
	protected function isImage($filename){
		if ($filename == '') return false;
		if ($info = getimagesize($filename)){
			if (in_array($info[2], array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG))){
				return true;
			}
		}
		return false;
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
			if ($this->isImage($file['tmp_name'])){
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
	}
}