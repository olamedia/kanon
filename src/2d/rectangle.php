<?php
#require_once dirname(__FILE__).'/shape.php';
#require_once dirname(__FILE__).'/point.php';
/**
 * @todo java.awt.Rectangle
 * @author olamedia
 *
 */
class rectangle implements shape{
	protected $_x = 0;
	protected $_y = 0;
	protected $_width = 0;
	protected $_height = 0;
	protected $_sourceX = 0;
	protected $_sourceY = 0;
	protected $_sourceWidth = 0;
	protected $_sourceHeight = 0;
	protected $_enlarge = true;
	protected function _makeSource(){
		$this->_sourceX = $this->_x;
		$this->_sourceY = $this->_y;
		$this->_sourceWidth = $this->_width;
		$this->_sourceHeight = $this->_height;
	}
	/**
	 * @return rectangle
	 */
	public function getBounds(){
		return $this;
	}
	public function __construct($width, $height){
		$this->_width = $width;
		$this->_height = $height;
	}
	public function setWidth($width){
		$this->_width = $width;
		return $this;
	}
	public function setHeight($height){
		$this->_height = $height;
		return $this;
	}
	public function getWidth(){
		return $this->_width;
	}
	public function getHeight(){
		return $this->_height;
	}
	public function setX($x){
		$this->_x = $x;
		return $this;
	}
	public function setY($y){
		$this->_y = $y;
		return $this;
	}
	public function getX(){
		return $this->_x;
	}
	public function getY(){
		return $this->_y;
	}
	public function setSource($rect){
		$this->setSourceX($rect->getX());
		$this->setSourceY($rect->getY());
		$this->setSourceWidth($rect->getWidth());
		$this->setSourceHeight($rect->getHeight());
	}
	public function setSourceX($x){
		$this->_sourceX = $x;
		return $this;
	}
	public function setSourceY($y){
		$this->_sourceY = $y;
		return $this;
	}
	public function setSourceWidth($width){
		$this->_sourceWidth = $width;
		return $this;
	}
	public function setSourceHeight($height){
		$this->_sourceHeight = $height;
		return $this;
	}
	public function getSourceX(){
		return $this->_sourceX;
	}
	public function getSourceY(){
		return $this->_sourceY;
	}
	public function getSourceWidth(){
		return $this->_sourceWidth;
	}
	public function getSourceHeight(){
		return $this->_sourceHeight;
	}
	public function allowEnlarge($allow = true){
		return $this->_enlarge = $allow;
	}
	public function fitWidth($width){
		$this->_makeSource();
		if ($this->_width > $width || ($this->_width < $width && $this->_enlarge)){
			$box = new self($width, round(($this->_height/$this->_width)*$width));
			$box->setSourceWidth($this->getWidth());
			$box->setSourceHeight($this->getHeight());
			return $box;
		}
		return $this;
	}
	public function fitHeight($height){
		$this->_makeSource();
		if ($this->_height > $height || ($this->_height < $height && $this->_enlarge)){
			$box = new self(round(($this->_width/$this->_height)*$height), $height);
			$box->setSourceWidth($this->getWidth());
			$box->setSourceHeight($this->getHeight());
			return $box;
		}
		return $this;
	}
	public function fit($width, $height){
		$this->_makeSource();
		if ($this->_width/$this->_height > $width/$height){
			return $this->fitWidth($width);
		}else{
			return $this->fitHeight($height);
		}
	}
	public function crop($width, $height){
		$this->_makeSource();
		if ($this->_width/$this->_height > $width/$height){
			$resizeBox = $this->fitHeight($height);
			$resizeBox->setSourceHeight($this->_height);
			//$resizeBox->setX(round(($resizeBox->getWidth() - $width)/2));
			$resizeBox->setSourceWidth(round($width*($this->_height/$height)));
			$resizeBox->setSourceX(round(($this->_width-$resizeBox->getSourceWidth())/2));
			//*($this->_width/$width)
			//$this->_width - $resizeBox->getSourceX()*2
			$resizeBox->setWidth($width);
		}else{
			$resizeBox = $this->fitWidth($width);
			$resizeBox->setSourceWidth($this->_width);
			$resizeBox->setSourceHeight(round($height*($this->_width/$width)));
			//$resizeBox->setY(round(($resizeBox->getHeight() - $height)/2));
			$resizeBox->setSourceY(round(($this->_height-$resizeBox->getSourceHeight())/2));
			$resizeBox->setHeight($height);
		}
		return $resizeBox;
	}
}