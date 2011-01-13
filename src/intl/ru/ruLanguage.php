<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ruLanguage
 *
 * @author olamedia
 */
class ruLanguage extends l10nLanguage{
    public static function plural($count, $forms){
        return $forms[0];
    }
}

