<?php

echo '<!DOCTYPE html>';
echo '<title>'.magic::get('title', 'Untitled page').'</title>';
$css = magic::get('css', '');
if ($css != ''){
    echo '<style type="text/css">';
    echo $css;
    echo '</style>';
}