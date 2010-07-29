<?php

class CircularReferenceException extends Exception{

}
/**
 * Simple test for circular references
 * Example: detect_circular_references(get_defined_vars());
 * Currently tries to detect recursion within objects
 * @param mixed $vars
 * @param array $refs
 * @param array $objrefs
 */
function detect_circular_references($vars = array(), &$refs = array(), &$objrefs = array()) {
    foreach ($vars as $name => $var) {
        if (is_array($var)) {
            detect_circular_references($var, $refs, $objrefs);
        } else {
            if (is_object($var)) {
                ob_start();
                debug_zval_dump($var);
                $dump = ob_get_clean();
                if (strpos($dump, '*RECURSION*') !== false){
                    //echo '*RECURSION*';
                    throw new CircularReferenceException('Circular reference in $'.$name);
                }
                /*$ref = spl_object_hash($var);
                if (isset($objrefs[$ref])){
                    throw new CircularReferenceException();
                }
                $objrefs[$ref] = true;
                $reflection = new ReflectionObject($var);
                $properties = $reflection->getProperties();
                $pvars = array();
                foreach ($properties as $property) {
                    if (version_compare(PHP_VERSION, '5.3', '>=')) {
                        $property->setAccessible(true); // PHP 5.3
                        $pvars[] = $property->getValue();
                    } else {
                        if ($property->isPublic()) {
                            $pvars[] = $property->getValue();
                        }
                    }
                }
                detect_circular_references($pvars, $refs, $objrefs);*/
            }
        }
    }
}