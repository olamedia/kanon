<?php
class stringProperty extends modelProperty{
    protected $_dataType = modelProperty::TYPE_VARCHAR;
    public function ellipsis($maxChars = 100, $ellipsis = '…'){
        $v = $this->getValue();
        $l = mb_strlen($v, 'UTF-8');
        if ($l > $maxChars){
            $h = floor($maxChars/2);
            $n = $maxChars-1;
            if (preg_match("#^(.[$h,$n]\S)\s+#imsu", $v, $subs)){
                return $subs[1].$ellipsis;
            }
            return mb_substr($v,0, $maxChars).$ellipsis; // cut
        }
        return $v;
    }
}