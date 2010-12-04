<?php

class embedProperty extends textProperty{
	public function html($width = '100%', $height = '400'){
		$h = $this->getValue();
		$h = preg_replace("#width=('|\")([0-9%]+)\\\1#ims", 'width="'.$width.'"', $h);
		$h = preg_replace("#height=('|\")([0-9%]+)\\\1#ims", 'height="'.$height.'"', $h);
		return $h;
	}
}
