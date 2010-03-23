<?php
class creationTimestampProperty extends timestampProperty{
	public function preInsert(){
		$this->setValue(time());
	}
}