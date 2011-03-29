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
 * yRuInflector
 *
 * @package yuki
 * @subpackage inflector
 * @author olamedia
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */
class yRuInflector{
    protected static $_genitiveRegexp = array(
        '#([к])([ио]й)$#imsu'=>'\1ого',
        '#([в])([ы]й)$#imsu'=>'\1ого',
        '#([н])([и]й)$#imsu'=>'\1его',
        '#([н])([я]я)$#imsu'=>'\1ей',
        '#([г])([о]й)$#imsu'=>'\1оя',
        '#([к])([и])$#imsu'=>'\1ов',
        '#([лтрг])$#imsu'=>"\\1\\2а",//([аеёиоуыэюя])
        '#([дм])(а)$#imsu'=>'\1ы',
        '#([нм])(ь)$#imsu'=>'\1и',
        '#([бвгджзйклмнпрстфхцчшщъь])(ск)$#imsu'=>'\1ска',
    );
    public static function genitiveForm($words){
        if (strpos($words, ' ') !== false){
            $worda = explode(' ', $words);
            foreach ($worda as $k=>$word){
                $worda[$k] = self::genitiveForm($word);
            }
            return implode(' ', $worda);
        }
        $word = $words;
        foreach (self::$_genitiveRegexp as $pattern=>$replacement){
            if (preg_match($pattern, $word)){
                return preg_replace($pattern, $replacement, $word);
            }
        }
        return $word;
    }
}

