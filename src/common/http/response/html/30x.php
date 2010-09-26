<?php
magic::set('title', 'Переадресация');
$location = array_shift($args);
magic::call('html/header');
echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">';
echo '<noscript>';
//echo '<meta http-equiv="refresh" content="1; url=&#39;'.htmlspecialchars($location).'&#39;">';
echo '</noscript>';
echo '<p>Подождите...</p>';
echo '<p>Если переадресация не сработала, перейдите по <a href="'.$location.'">ссылке</a> вручную.</p>';
echo $location;
/*echo '<script type="text/javascript" language="javascript">';
echo 'function r(){location.replace("'.$location.'");}';
echo 'window.onload=function(){setTimeout(\'r\', 500)}';
echo '</script>';*/
magic::call('html/footer');
