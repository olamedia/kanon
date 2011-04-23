<?php
abstract class imageDriver{
	/**
	 *
	 * @param image $image
	 */
	public function save($image){

	}
	/**
	 *
	 * @param image $image
	 * @param rectangle $rectangle
	 */
	public function fromArea($image, $rectangle){

	}
	public function load($image){
		return false;
	}
	/**
	 * Free all associated resources
	 * @param image $image
	 */
	public function destruct($image){
		
	}
}