<?php
class modificationTimestampProperty extends timestampProperty{
	public function preSave(){
		$this->setValue(time());
	}
}