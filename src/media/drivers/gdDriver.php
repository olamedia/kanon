<?php
class gdDriver{
	protected static $_jpegQuality = 85;
	protected static $_pngCompression = 2;
	protected static $_pngFilters = PNG_ALL_FILTERS;
	public function load(){
		return function_exists('gd_info');
	}
	protected function init($image){
		if (!is_resource($image->meta)){
			if (is_file($image->getFilename())){
				if ($type = $image->getType()){
					switch ($type){
						case 1:	$image->meta = imagecreatefromgif($image->getFilename()); break;
						case 2:	$image->meta = imagecreatefromjpeg($image->getFilename()); break;
						case 3:	$image->meta = imagecreatefrompng($image->getFilename()); break;
						default: return false; break;
					}
				}
				/*}else{
				 $image->meta = new Imagick();*/
			}
		}
		return true;
	}
	/**
	 *
	 * @param image $image
	 */
	public function save($image){
		if (is_resource($image->meta)){
			switch ($image->getType()) {
				case 1:	imagegif($image->meta, $image->getFilename()); break;
				case 2: imagejpeg($image->meta, $image->getFilename(), self::$_jpegQuality); break;
				case 3:	imagepng($image->meta, $image->getFilename(),self::$_pngCompression,self::$_pngFilters); break;
				default: break;
			}
		}
	}
	/**
	 *
	 * @param image $image
	 * @param rectangle $rectangle
	 */
	public function fromArea($image, $sourceImage, $rectangle){
		$image->init();
		$sourceImage->init();
		if (is_resource($sourceImage->meta)){
			$image->meta = imagecreatetruecolor($rectangle->getWidth(), $rectangle->getHeight());
			$this->_thumbnail($sourceImage->meta, $image->meta, $rectangle);
		}
	}
	protected function _thumbnail($source, &$thumb, $rect){
		imagealphablending($thumb, false);
		imagesavealpha($thumb, true);
		imagecopyresampled($thumb, $source, $rect->getX(), $rect->getY(), $rect->getSourceX(), $rect->getSourceY(), $rect->getWidth(), $rect->getHeight(), $rect->getSourceWidth(), $rect->getSourceHeight());
	}
	public function destruct($image){
		if (is_resource($image->meta)){
			imagedestroy($image->meta);
		}
	}
}