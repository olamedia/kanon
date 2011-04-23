<?php
if (!function_exists('class_alias')) {
    function class_alias($original, $alias) {
        eval('class '.$alias.' extends '.$original.' {}');
    }
}