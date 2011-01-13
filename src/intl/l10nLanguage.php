<?php

/**
 * Description of l10nLanguage
 *
 * @author olamedia
 */
class l10nLanguage{
    /**
     * Provides an alternative text depending on specified gender.
     * Usage {{gender:username|masculine|feminine|neutral}}.
     * username is optional, in which case the gender of current user is used,
     * but only in (some) interface messages; otherwise default gender is used.
     * If second or third parameter are not specified, masculine is used.
     * These details may be overriden per language.
     */
    public static function gender($gender, $forms){
        if (!count($forms)){
            return '';
        }
        //$forms = $this->preConvertPlural($forms, 2);
        if ($gender === 'male'){
            return $forms[0];
        }
        if ($gender === 'female'){
            return $forms[1];
        }
        return isset($forms[2])?$forms[2]:$forms[0];
    }
    public static function plural($count, $forms){
        if (!count($forms)){
            return '';
        }
        //$forms = $this->preConvertPlural($forms, 2);
        return ($count == 1)?$forms[0]:$forms[1];
    }
}

