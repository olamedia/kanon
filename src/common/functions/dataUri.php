<?php
/**
 * data: URI
 * @param $filename source
 * @param $mime ex:image/png
 * @param $charset optional
 * @return string data: URI
 */
function dataUri($filename, $mime, $charset = false){  
  $contents = file_get_contents($filename);
  $base64   = base64_encode($contents);
  return ('data:'.$mime.';base64'.($charset?'charset='.$charset:'').','.$base64);
}