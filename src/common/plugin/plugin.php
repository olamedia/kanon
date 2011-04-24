<?php

class plugin {

    protected $_name = '';
    protected $_title = '';
    protected $_description = '';

    public function __construct($name) {
	$this->_name = $name;
    }

    public function getName() {
	return $this->_name;
    }

    public function getTitle() {
	return $this->_title;
    }

    public function setTitle($title) {
	$this->_title = $title;
    }

    public function getDescription() {
	return $this->_description;
    }

    public function setDescription($description) {
	$this->_description = $description;
    }

}