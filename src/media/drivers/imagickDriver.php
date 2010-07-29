<?php
class imagickDriver{
	protected static $_enhance = true;
	protected static $_frameLimit = 24;
	public function load(){
		return class_exists('Imagick');
	}
	/**
	 *
	 * @param image $image
	 */
	public function save($image){
		if ($image->meta instanceof Imagick){
			$imagick = $image->meta;
			$imagick->setImageCompression(Imagick::COMPRESSION_JPEG);
			$imagick->setImageCompressionQuality(85);
			$imagick->setCompression(Imagick::COMPRESSION_JPEG);
			$imagick->setCompressionQuality(85);
			$imagick->commentImage("Kanon PHP Framework");
			$imagick->writeImages($image->getFilename(), true);
		}
	}
	public function init($image){
		if (!($image->meta instanceof Imagick)){
			try{
				if (is_file($image->getFilename())){
					$image->meta = new Imagick($image->getFilename());
				}else{
					$image->meta = new Imagick();
				}
			}catch(ImagickException $e){
				return false;
			}
		}
		return true;
	}
	/**
	 *
	 * @param image $image
	 * @param rectangle $rectangle
	 */
	public function fromArea($image, $sourceImage, $rectangle){
		$image->init();
		$sourceImage->init();
		if ($image->meta instanceof Imagick){
			if ($sourceImage->meta instanceof Imagick){
				$source = $sourceImage->meta->coalesceImages();
				$this->_thumbnail($sourceImage->meta, $image->meta, $rectangle);
			}
		}
	}
	public function destruct($image){
		if ($image->meta instanceof Imagick){
			$image->meta->destroy();
		}
	}
	protected function _thumbnail($source, &$thumb, $rect){
		$source = $source->coalesceImages();
		$fi = 0;
		foreach ($source as $frame){
			$fi++;
			if ($fi < self::$_frameLimit){
				try{
						
					$area = $frame->getImageRegion(
					$rect->getSourceWidth(),
					$rect->getSourceHeight(),
					$rect->getSourceX(),
					$rect->getSourceY()
					);
					$area->resizeImage(
					$rect->getWidth()*2,
					$rect->getHeight()*2,
					imagick::FILTER_LANCZOS,
					1
					);
					if (self::$_enhance){
						$area->enhanceImage();// Improves the quality of a noisy image
						//$area->reduceNoiseImage(2);// Smooths the contours of an image while still preserving edge information. The algorithm works by replacing each pixel with its neighbor closest in value.
						//$area->sharpenImage(2,1);
					}
					$area->resizeImage(
					$rect->getWidth(),
					$rect->getHeight(),
					imagick::FILTER_LANCZOS,
					1
					);
						
					if (self::$_enhance){

						$area->sharpenImage(1.4,1);
						//
						//
					}
					$thumb->addImage($area->getImage());
						
					$thumb->setImagePage(
					$rect->getWidth(),
					$rect->getHeight(),
					0,
					0
					);
						
				}catch(ImagickException $e){
				}
			}
		}
		try{
			$thumb->optimizeImageLayers();
		}catch(ImagickException $e){
		}
	}
}