<?php

class CircularReferenceException extends Exception{

}
function detect_circular_references($vars = array(), &$refs = array(), &$objrefs = array()) {
    foreach ($vars as $var) {
        if (is_array($var)) {
            detect_circular_references($var, $refs, $objrefs);
        } else {
            if (is_object($var)) {
                $ref = spl_object_hash($var);
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
                detect_circular_references($pvars, $refs, $objrefs);
            }
        }
    }
}