<?php

/*
 * This file is part of the yuki package.
 * Copyright (c) 2011 olamedia <olamedia@gmail.com>
 *
 * Licensed under The MIT License
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * yMath
 * General mathematic methods, using bcmath or gmp if available
 * 
 * @package yuki
 * @subpackage math
 * @author olamedia
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */
class yMath{
    const dec = '0123456789';
    const base10 = '0123456789';
    const hex = '0123456789abcdef';
    const base16 = '0123456789abcdef';
    const base32 = '0123456789abcdefghijklmnopqrstuvwxyz';
    // base58 - flickr reduced alphabet without 0,O,l,I letters (they looks like o,1)
    const base58 = '123456789abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ';
    const base64 = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    const modeNative = 0;
    const modeBcmath = 1;
    const modeGmp = 2;
    private static $_mode = null;
    /**
     * Sets mode, choosing between native, bcmath & gmp imlementations
     * For testing purposes primary
     * @param integer $mode 
     */
    public static function setMode($mode){
        self::$_mode = $mode;
    }
    public static function getMode(){
        if (self::$_mode === null){
            self::$_mode = self::gmpExists()?
                    self::modeGmp:
                    (self::bcmathExists()?
                            self::modeBcmath:
                            self::modeNative
                    );
        }
        return self::$_mode;
    }
    public static function bcmathExists(){
        return function_exists('bcadd');
    }
    public static function gmpExists(){
        return function_exists('gmp_add');
    }
    /**
     * Checks if calculations will work with arbitrary-length numbers
     * @return boolean 
     */
    public static function hasArbitraryPrecision(){
        return self::bcmathExists() || self::gmpExists();
    }
    public static function add($x, $y){
        switch (self::getMode()){
            case self::modeGmp:
                return gmp_strval(gmp_add($x, $y));
                break;
            case self::modeBcmath:
                return bcadd($x, $y);
                break;
            case self::modeNative:
                return $x + $y;
                break;
        }
    }
    public static function sub($x, $y){
        switch (self::getMode()){
            case self::modeGmp:
                return gmp_strval(gmp_sub($x, $y));
                break;
            case self::modeBcmath:
                return bcsub($x, $y);
                break;
            case self::modeNative:
                return $x - $y;
                break;
        }
    }
    public static function mod($x, $y){
        switch (self::getMode()){
            case self::modeGmp:
                return gmp_strval(gmp_mod($x, $y));
                break;
            case self::modeBcmath:
                return bcmod($x, $y);
                break;
            case self::modeNative:
                // fallback by Andrius Baranauskas and Laurynas Butkus
                $take = 5;
                $mod = '';
                do{
                    $a = (int) $mod.substr($x, 0, $take);
                    $x = substr($x, $take);
                    $mod = $a % $y;
                }while (strlen($x));
                return (int) $mod;
                return $x % $y;
                break;
        }
    }
    public static function div($x, $y){
        switch (self::getMode()){
            case self::modeGmp:
                return gmp_strval(gmp_div($x, $y));
                break;
            case self::modeBcmath:
                return bcdiv($x, $y);
                break;
            case self::modeNative:
                return $x / $y;
                break;
        }
    }
    public static function mul($x, $y){
        switch (self::getMode()){
            case self::modeGmp:
                return gmp_strval(gmp_mul($x, $y));
                break;
            case self::modeBcmath:
                return bcmul($x, $y);
                break;
            case self::modeNative:
                return $x * $y;
                break;
        }
    }
    /**
     * Converts number base (up to base 64)
     * Works similary to base_convert, but supports larger numbers
     * @param mixed $number
     * @param integer $fromBase
     * @param integer $toBase 
     * @return mixed
     */
    public static function baseConvert($number, $fromBase, $toBase){
        if (self::gmpExists()){
            // native gmp variant
            return gmp_strval(gmp_init($number, $fromBase), $toBase);
        }
        $fromAlphabet = substr(self::base64, 0, $fromBase);
        $toAlphabet = substr(self::base64, 0, $toBase);
        return self::alphabetConvert($number, $fromAlphabet, $toAlphabet);
    }
    /**
     * Converts number using custom alphabets
     * @note PHP strings are copy-on-write
     * @param mixed $number
     * @param integer $fromBase
     * @param integer $toBase 
     * @return mixed
     */
    public static function alphabetConvert($number, $fromAlphabet, $toAlphabet){
        return self::baseEncode(self::baseDecode($number, $fromAlphabet), $toAlphabet);
    }
    /**
     * Encodes integer according to the given alphabet
     * @param integer $number Number
     * @param string $alphabet Alphabet
     * @return string Encoded number
     */
    public static function baseEncode($number, $alphabet){
        $base = strlen($alphabet);
        $e = '';
        while ($number){
            $m = self::mod($number, $base);
            $x = self::div(self::sub($number, $m), $base);
            $e .= $alphabet[$m];
            //echo $number.' = '.$base.' * '.$x.' + '.$m."\n";
            $number = $x;
        }
        return strrev($e);
    }
    /**
     * Decodes integer according to the given alphabet
     * @param string $encoded Encoded number
     * @param string $alphabet Alphabet
     * @return integer Number
     */
    public static function baseDecode($encoded, $alphabet){
        $number = 0;
        $l = strlen($encoded);
        $rev = strrev($encoded);
        $base = strlen($alphabet);
        while ($l > 0){
            $number = self::mul($number, $base);
            $number = self::add($number, strpos($alphabet, $rev[$l - 1]));
            $l--;
        }
        return $number;
    }
}

/*/
$dec = yMath::baseDecode('537d9f604f5d4511858d350d3a4e233c', yMath::hex);

echo "DEC:";
echo $dec;
echo "\n";
echo "\n";
$hex = yMath::baseEncode($dec, yMath::hex);
echo "HEX:";
echo $hex;
echo "\n";
echo "\n";

echo "10 = ".yMath::baseDecode('a', yMath::hex)."\n";
echo "254 = ".yMath::baseDecode('fe', yMath::hex)."\n";

echo "a = ".yMath::baseEncode('10', yMath::hex)."\n";
echo "fe = ".yMath::baseEncode('254', yMath::hex)."\n";
/*/



