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
        if (!count($forms)){
            return '';
        }
        // if no number with word, then use $form[0] for singular and $form[1] for plural or zero
        if (count($forms) === 2)
            return $count == 1?$forms[0]:$forms[1];
        // FIXME: CLDR defines 4 plural forms. Form with decimals missing.
        // See http://unicode.org/repos/cldr-tmp/trunk/diff/supplemental/language_plural_rules.html#ru
        //$forms = $this->preConvertPlural($forms, 3);
        if ($count > 10 && floor(( $count % 100 ) / 10) == 1){
            return $forms[2];
        }else{
            switch ($count % 10){
                case 1: return $forms[0];
                case 2:
                case 3:
                case 4: return $forms[1];
                default: return $forms[2];
            }
        }
    }
}

