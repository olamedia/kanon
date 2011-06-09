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
    /**
     * Converts number base (up to base 64)
     * Works similary to base_convert, but supports larger numbers
     * @param mixed $number
     * @param integer $fromBase
     * @param integer $toBase 
     * @return mixed
     */
    public static function baseConvert($number, $fromBase, $toBase){
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
            $e .= $alphabet[$number % $base];
            $number = floor($number / $base);
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
        $base = strlen($alphabet);
        while ($l > 0){
            $number *= $base;
            $number += strpos($alphabet, $encoded[$l - 1]);
            $l--;
        }
        return $number;
    }
}

