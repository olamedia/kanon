<?php
require_once dirname(__FILE__).'/../2d/rectangle.php';
class image{
	protected $_filename = null;
	protected $_targetFilename = null;
	protected $_originalImage = null;
	protected $_info = null;
	protected $_keepAnimation = true;
	protected $_frameLimit = 24;
	protected $_forceType = false; // @todo forceType('jpg')
	public function __construct($filename, $originalImage = null){
		$this->_filename = $filename;
		$this->_originalImage = $originalImage;
	}
	public function setTarget($filename){ // force thumbnail filename
		return $this->_targetFilename = $filename;
	}
	public function getWidth(){
		return $this->getRectangle()->getWidth();
	}
	public function getHeight(){
		return $this->getRectangle()->getHeight();
	}
	public function getInfo(){
		if ($this->_info == null){
			$this->_info = getimagesize($this->_filename);
		}
		return $this->_info;
	}
	public function getType(){
		if ($info = $this->getInfo()){
			return $info[2];
		}
	}
	/**
	 * @return rectangle
	 */
	public function getRectangle(){
		if ($info = $this->getInfo()){
			$rect = new rectangle($info[0], $info[1]);
			return $rect;
		}
		return false;
	}
	public function getThumbnailPath($prefix){
		if ($this->_targetFilename !== null) return $this->_targetFilename;
		//echo 'Creating '.dirname($this->_filename).'/.thumb/'.$this->getThumbnailFilename($prefix);
		return dirname($this->_filename).'/.thumb/'.$this->getThumbnailFilename($prefix);
	}
	public function getThumbnailFilename($prefix){
		return $prefix.'_'.basename($this->_filename);
	}
	public function makeThumbnail($newFilename, $rect){ // stretch
		//echo '<pre>';
		//die(var_dump($rect));
		if (class_exists('Imagick')){
			// @todo: repair Imagick functionality for crop
			// faster, can keep animation
			return $this->makeThumbnailWithImagick($newFilename, $rect);
		}
		if (function_exists('imagecreatetruecolor')){
			// slow, high memory usage
			return $this->makeThumbnailWithGd($newFilename, $rect);
		}
		return false;
	}
	/**
	 *
	 * @param string $newFilename
	 * @param rectangle $rect
	 */
	public function makeThumbnailWithGd($newFilename, $rect){ // stretch
		if ($type = $this->getType()){
			switch ($type){
				case 1:	$image = imagecreatefromgif($this->_filename); break;
				case 2:	$image = imagecreatefromjpeg($this->_filename); break;
				case 3:	$image = imagecreatefrompng($this->_filename); break;
				default: return false; break;
			}
			$thumb = imagecreatetruecolor($rect->getWidth(), $rect->getHeight());
			imagealphablending($thumb, false);
			imagesavealpha($thumb, true);
			imagecopyresampled($thumb, $image, $rect->getX(), $rect->getY(), $rect->getSourceX(), $rect->getSourceY(), $rect->getWidth(), $rect->getHeight(), $rect->getSourceWidth(), $rect->getSourceHeight());
			imagedestroy($image);
			switch ($type) {
				case 1:	imagegif($thumb, $newFilename); break;
				case 2:
					$q = 70;
					if ($rect->getWidth() <= 200 || ($rect->getHeight() <= 200)) $q = 85;
					imagejpeg($thumb, $newFilename, $q);
					break;
				case 3:	imagepng($thumb, $newFilename); break;
				default: break;
			}
			imagedestroy($thumb);
			return true;
		}
	}
	protected function _thumbnailWithImagick($source, &$thumb, $rect, $level = 0){
		//echo $source->getNumberImages().'('.$level.') ';
		$w = $rect->getWidth();
		$h = $rect->getHeight();
		$sw = $rect->getSourceWidth();
		$sh = $rect->getSourceHeight();
		$sx = $rect->getSourceX();
		$sy = $rect->getSourceY();
		$scalex = $w/$sw;
		$scaley = $h/$sh;
		$x = $sx*$scalex;
		$y = $sy*$scaley;

		//$view = $source->getImage();//new Imagick();// $source->getImage();
		$fi = 0;
		//$frame = new Imagick();
		//$view->newImage($w, $h, new ImagickPixel('red'));
		//$view->addImage($frame);
		foreach ($source as $k => $frame){
			///if ($frame->getNumberImages()){
			//$this->_thumbnailWithImagick($frame, $thumb, $rect);
			//}
			/*$fullSizeFrame->addImage($frame->getImage());
			 $fullSizeFrame->flattenImages();*/
			$fi++;
			if ($fi<$this->_frameLimit){
				try{
					$frameInfo = $frame->getImagePage();
					$fx = $frameInfo['x']*$scalex;
					$fy = $frameInfo['y']*$scaley;
					$fw = $frameInfo['width']*$scalex;
					$fh = $frameInfo['height']*$scaley;
					$area = $frame->getImageRegion($sw, $sh, $sx, $sy);
					$area->resizeImage($w, $h, imagick::FILTER_GAUSSIAN, 1);
					$area->enhanceImage();// Improves the quality of a noisy image
					$area->reduceNoiseImage(2);// Smooths the contours of an image while still preserving edge information. The algorithm works by replacing each pixel with its neighbor closest in value.
					
					$thumb->addImage($area->getImage());
					$thumb->setImagePage($w, $h, 0, 0);
				}catch(ImagickException $e){
				}
			}
		}
		try{
			$thumb->optimizeImageLayers();
		}catch(ImagickException $e){
		}
		//die();
	}
	/**
	 * @todo fix crop (use of sourceX, sourceY, sourceWidth, sourceHeight)
	 * @param string $newFilename
	 * @param rectangle $rect
	 */
	public function makeThumbnailWithImagick($newFilename, $rect){ // stretch
		//var_dump($rect);
		try{
			$source = new Imagick($this->_filename);
		}catch(ImagickException $e){
			return false;
		}
		/*
		 * coalesceImages() Returns a new Imagick object
		 * where each image in the sequence
		 * is the same size as the first
		 * and composited with the next image in the sequence.
		 */
		$source = $source->coalesceImages();
		$thumb = new Imagick();
		$this->_thumbnailWithImagick($source, $thumb, $rect);
		$thumb->setImageCompression(Imagick::COMPRESSION_JPEG); 
		$thumb->setImageCompressionQuality(80);
		$thumb->setCompression(Imagick::COMPRESSION_JPEG); 
		$thumb->setCompressionQuality(80); 
		$thumb->commentImage(date("<mtime=d.m.Y H:i:s>", filemtime(__FILE__)));
		$thumb->writeImages($newFilename, true);
		/*header('Content-type: image/png');
		$thumb->setImageFormat('png');
		echo $thumb->getImagesBlob();
		die();*/
	}
	public function stretch($width, $height){
		if ($rect = $this->getRectangle()){
			$rect = new rectangle($width, $height);
			$filename = $this->getThumbnailPath('tms'.$width.'x'.$height);
			if ($this->makeThumbnail($filename, $rect)){
				return new self($filename, $this);
			}
		}
		return false;
	}
	public function fit($width, $height){
		if ($rect = $this->getRectangle()){
			$rect = $rect->fit($width, $height);
			$filename = $this->getThumbnailPath('tmm'.$width.'x'.$height);
			if ($this->makeThumbnail($filename, $rect)){
				return new self($filename, $this);
			}
		}
		return false;
	}
	public function fitWidth($width){
		if ($rect = $this->getRectangle()){
			$rect = $rect->fitWidth($width);
			$filename = $this->getThumbnailPath('tmw'.$width);
			if ($this->makeThumbnail($filename, $rect)){
				return new self($filename, $this);
			}
		}
		return false;
	}
	public function fitHeight($height){
		if ($rect = $this->getRectangle()){
			$rect = $rect->fitHeight($height);
			$filename = $this->getThumbnailPath('tmh'.$height);
			if ($this->makeThumbnail($filename, $rect)){
				return new self($filename, $this);
			}
		}
		return false;
	}
	public function crop($width, $height){
		if ($rect = $this->getRectangle()){
			$rect = $rect->crop($width, $height);
			//var_dump($rect);
			//die();
			$filename = $this->getThumbnailPath('tmc'.$width.'x'.$height);
			if ($this->makeThumbnail($filename, $rect)){
				return new self($filename, $this);
			}
		}
		return false;
	}
}