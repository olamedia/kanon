<?php

/*
 * This file is part of the yuki package.
 * Copyright (c) 2011 olamedia <olamedia@gmail.com>
 *
 * This source code is release under the MIT License.
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * uuid - Universally unique identifier generator
 *
 * @package kanon
 * @subpackage uuid
 * @author olamedia
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */
class uuid{
    protected $_uuidString = null;
    public function __construct($uuidString){
        $this->_uuidString = $uuidString;
    }
    /**
     * UUID v4 (random data based)
     * @return string 
     */
    public static function v4(){
        return new uuid(
                sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                        // 32 bits for "time_low"
                        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
                        // 16 bits for "time_mid"
                        mt_rand(0, 0xffff),
                        // 16 bits for "time_hi_and_version",
                        // four most significant bits holds version number 4
                        mt_rand(0, 0x0fff) | 0x4000,
                        // 16 bits, 8 bits for "clk_seq_hi_res",
                        // 8 bits for "clk_seq_low",
                        // two most significant bits holds zero and one for variant DCE1.1
                        mt_rand(0, 0x3fff) | 0x8000,
                        // 48 bits for "node"
                        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
                )
        );
    }
    /**
     * UUID v3 (name based, MD5)
     * @param string|uuid $namespace
     * @param string $name
     * @return string 
     */
    public static function v3($namespace, $name){
        $namespace = new uuid(strval($namespace));
        if (!$namespace->isValid()){
            return false;
        }
        $hash = md5($namespace->toBinary().$name);
        return new uuid(
                sprintf('%08s-%04s-%04x-%04x-%12s',
                        // 32 bits for "time_low"
                        substr($hash, 0, 8),
                        // 16 bits for "time_mid"
                        substr($hash, 8, 4),
                        // 16 bits for "time_hi_and_version",
                        // four most significant bits holds version number 5
                        (hexdec(substr($hash, 12, 4)) & 0x0fff) | 0x3000,
                        // 16 bits, 8 bits for "clk_seq_hi_res",
                        // 8 bits for "clk_seq_low",
                        // two most significant bits holds zero and one for variant DCE1.1
                        (hexdec(substr($hash, 16, 4)) & 0x3fff) | 0x8000,
                        // 48 bits for "node"
                        substr($hash, 20, 12)
                )
        );
    }
    /**
     * UUID v3 (name based, SHA-1)
     * @param string|uuid $namespace
     * @param string $name
     * @return string 
     */
    public static function v5($namespace, $name){
        $namespace = new uuid(strval($namespace));
        if (!$namespace->isValid()){
            return false;
        }
        $hash = sha1($namespace->toBinary().$name);
        return new uuid(
                sprintf('%08s-%04s-%04x-%04x-%12s',
                        // 32 bits for "time_low"
                        substr($hash, 0, 8),
                        // 16 bits for "time_mid"
                        substr($hash, 8, 4),
                        // 16 bits for "time_hi_and_version",
                        // four most significant bits holds version number 5
                        (hexdec(substr($hash, 12, 4)) & 0x0fff) | 0x5000,
                        // 16 bits, 8 bits for "clk_seq_hi_res",
                        // 8 bits for "clk_seq_low",
                        // two most significant bits holds zero and one for variant DCE1.1
                        (hexdec(substr($hash, 16, 4)) & 0x3fff) | 0x8000,
                        // 48 bits for "node"
                        substr($hash, 20, 12)
                )
        );
    }
    public function toBinary(){
        $hex = str_replace(array('-', '{', '}', '(', ')'), '', $this->_uuidString);
        $bin = '';
        for ($i = 0, $max = strlen($hex); $i < $max; $i += 2){
            $bin .= chr(hexdec($hex[$i].$hex[$i + 1]));
        }
        return $bin;
    }
    public static function validate($uuid){
        return preg_match('#^\{?[0-9a-f]{8}\-?[0-9a-f]{4}\-?[0-9a-f]{4}\-?[0-9a-f]{4}\-?[0-9a-f]{12}\}?$#im', strval($uuid));
    }
    public function isValid(){
        return self::validate($this->_uuidString);
    }
    public function __toString(){
        return $this->_uuidString;
    }
}

