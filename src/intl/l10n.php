<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of l10n
 *
 * @author olamedia
 */
class l10n{
    protected static $_mCache = array();
    /**
     * Get localized message
     * Ex: l10n::_('$1 added $2 users', l10n::word('alice')->gender('female'), l10n::num(3))
     * Ex: l10n::_('$1 added $2 users', l10n::word('alice and tom')
     *      ->gender('female', 'male')
     *      ->num(2), l10n::num(1))
     * @return string
     */
    public static function _(){
        // "Сделано $1 {{PLURAL:$1|изменение|изменения|изменений}}"
        $args = func_get_args();
        $msg = new l10nMessage(array_shift($args));
        $msg->setArguments($args);
        $msg->setLocale('ru');
        return $msg;
    }
    public static function loadFile($locale, $filename){
        $messages = array();
        if (is_file($filename)){
            include $filename;
        }
        foreach ($messages as $a => $b){
            self::$_mCache[$locale][$a] = $b;
        }
    }
    public static function getLocalizedMessageTemplate($locale, $message){
        
    }
    public static function getTemplate($locale, $msg){
        if (isset(self::$_mCache[$locale][$msg])){
            return self::$_mCache[$locale][$msg];
        }
        return $msg;
    }
    public static function lCall($languageMethod, $args){
        return call_user_func_array(array(self::getLClass(), $languageMethod), $args);
    }
    public static function getLClass(){
        return 'ruLanguage';
    }
    public static function word($word){
        $w = new l10nWord($word);
        return $w;
    }
    public static function num($num){
        $w = new l10nWord($num);
        return $w->num($num);
    }
    public static function gender($gender){
        $w = new l10nWord($gender);
        return $w->gender($gender);
    }
}

