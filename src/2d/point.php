<?php
/**
 * java.awt.Point port
 *
 * @todo point.hashCode()
 * @todo point.toString()
 *
 * @author olamedia
 *
 */
class point{ // java.awt.Point
	protected $_x = 0;
	protected $_y = 0;
	public function __construct(){
		// Point()
		// Constructs and initializes a point at the origin (0, 0) of the coordinate space.
		// Point(int, int)
		// Constructs and initializes a point at the specified (x, y) location in the coordinate space.
		// Point(Point)
		// Constructs and initializes a point with the same location as the specified Point object.
		$args = func_get_args();
		switch (count($args)){
			case 1:
				$arg = $args[0];
				if ($arg instanceof point){
					$this->_x = $arg->getX();
					$this->_y = $arg->getY();
				}
				break;
			case 2:
				$this->_x = $args[0];
				$this->_y = $args[1];
				break;
			default:
				break;
		}
	}
	/**
	 *
	 * @param point $point
	 */
	public function equals($point){
		return ($this->_x == $point->getX() && $this->_y == $point->getY());
	}
	public function setLocation(){
		$args = func_get_args();
		switch (func_num_args()){
			case 1:
				$point = array_shift($args);
				$this->_x = $x->getX();
				$this->_y = $x->getY();
				break;
			case 2:
				$x = array_shift($args);
				$y = array_shift($args);
				$this->_x = $x;
				$this->_y = $y;
				break;
		}
		return $this;
	}
	public function getLocation(){
		return $this;
	}
	public function move($x, $y){
		$this->_x = $x;
		$this->_y = $y;
		return $this;
	}
	public function translate($dx, $dy){
		$this->_x += $dx;
		$this->_y += $dy;
		return $this;
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
}