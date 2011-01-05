<?php

class inflector{
    public static function lowerCamelCase($string){
        $a = preg_split('/\p{Zs}/', $string, -1, PREG_SPLIT_NO_EMPTY);
        $f = strtolower(array_shift($a));
        foreach ($a as &$s){
            $s = ucfirst($s);
        }
        return $f.implode('', $a);
    }
    public static function underscore($string){
        $s = preg_replace('/(.)([\p{Lu}])/u', '\1 \2', $string); // split camel case
        $a = preg_split('/\p{Zs}/', $s, -1, PREG_SPLIT_NO_EMPTY);
        return implode('_', $a);
    }
    /**
     * Encode with base58 by default 123456789abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ
     * @param integer $i 
     */
    public static function baseEncode($i, $alphabet = '123456789abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ'){
        $base = strlen($alphabet);
        $e = '';
        while ($i >= $base){
            $m = $i % $base;
            echo $m.']';
            $i = floor($i/$base);
            $e .= $alphabet[$m];
        }
        if ($i){
            $e .= $alphabet[$i];
        }
        echo '[['.$e.']]';
        return strrev($e);
    }
    /**
     * Decode with base58 by default 123456789abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ
     * @param string $i 
     */
    public static function baseDecode($i, $alphabet = '123456789abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ'){
        $dec = 0;
        //$i = strrev($i);
        $l = strlen($i);
        $base = strlen($alphabet);
        while ($l > 0){
            $dec *= $base;
            $dec += strpos($alphabet, $i[$l-1]);
            $l--;
        }
        return $dec;
    }
}