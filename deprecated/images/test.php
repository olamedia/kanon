<?php
$path = dirname(__FILE__).'/articles/.thumb/';
foreach (glob($path.'*') as $file){
	if (is_file($file)) unlink($file);
}

echo '<img src="articles/.thumb/tmc200x100_1.gif" style="border: solid 1px #f00;">';
echo '<img src="articles/.thumb/tmc100x200_1.gif" style="border: solid 1px #f00;">';