<?php

/**
 * Description of nokogiri
 *
 * @author olamedia
 */
class nokogiri{
    protected $_source = '';
    /**
     * @var DOMDocument
     */
    protected $_dom = null;
    /**
     * @var DOMXpath
     * */
    protected $_xpath = null;
    public function __construct($htmlString = ''){
        $this->loadHtml($htmlString);
    }
    public static function fromHtml($htmlString){
        $me = new self();
        $me->loadHtml($htmlString);
        return $me;
    }
    public static function fromDom($dom){
        $me = new self();
        $me->loadDom($dom);
        return $me;
    }
    public function loadDom($dom){
        $this->_dom = $dom;
        $this->_xpath = new DOMXpath($this->_dom);
    }
    public function loadHtml($htmlString = ''){
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        if (strlen($htmlString)){
            libxml_use_internal_errors(TRUE);
            $dom->loadHTML($htmlString);
            libxml_clear_errors();
        }
        $this->loadDom($dom);
    }
    function __invoke($expression){
        return $this->get($expression);
    }
    public function get($expression){
        if (strpos($expression, ' ') !== false){
            $a = explode(' ', $expression);
            foreach ($a as $k => $sub){
                $a[$k] = $this->getXpathSubquery($sub);
            }
            return $this->getElements(implode('', $a));
        }
        return $this->getElements($this->getXpathSubquery($expression));
    }
    protected function getXpathSubquery($expression){
        $query = '';
        if (preg_match("/(?P<tag>[a-z0-9]+)?(\[(?P<attr>\S+)=(?P<value>\S+)\])?(#(?P<id>\S+))?(\.(?P<class>\S+))?/ims", $expression, $subs)){
            $tag = $subs['tag'];
            $id = $subs['id'];
            $attr = $subs['attr'];
            $attrValue = $subs['value'];
            $class = $subs['class'];
            if (!strlen($tag))
                $tag = '*';
            $query = '//'.$tag;
            if (strlen($id)){
                $query .= "[@id='".$id."']";
            }
            if (strlen($attr)){
                $query .= "[@".$attr."='".$attrValue."']";
            }
            if (strlen($class)){
                //$query .= "[@class='".$class."']";
                $query .= '[contains(concat(" ", normalize-space(@class), " "), " '.$class.' ")]';
            }
        }
        return $query;
    }
    protected function getElements($xpathQuery){ // tag.class
        $newDom = new DOMDocument('1.0', 'UTF-8');
        $root = $newDom->createElement('root');
        $newDom->appendChild($root);
        echo ' query: '.$xpathQuery.' ';
        if (strlen($xpathQuery)){
            $nodeList = $this->_xpath->query($xpathQuery);
            if ($nodeList === false){
                throw new Exception('Malformed xpath');
            }
            foreach ($nodeList as $domElement){
                echo ' found ';
                $domNode = $newDom->importNode($domElement, true);
                $root->appendChild($domNode);
            }
            return self::fromDom($newDom);
        }
    }
    public function toXml(){
        return $this->_dom->saveXML();
    }
    public function toArray($xnode = null){
        $array = array();
        if ($xnode === null){
            $node = $this->_dom;
        }else{
            $node = $xnode;
        }
        if ($node->nodeType == XML_TEXT_NODE){
            return $node->nodeValue;
        }
        if ($node->hasAttributes()){
            foreach ($node->attributes as $attr){
                $array[$attr->nodeName] = $attr->nodeValue;
            }
        }
        if ($node->hasChildNodes()){
            if ($node->childNodes->length == 1){
                $array[$node->firstChild->nodeName] = $this->toArray($node->firstChild);
            }else{
                foreach ($node->childNodes as $childNode){
                    if ($childNode->nodeType != XML_TEXT_NODE){
                        $array[$childNode->nodeName][] = $this->toArray($childNode);
                    }
                }
            }
        }
        if ($xnode === null){
            return reset(reset($array)); // first child
        }
        return $array;
    }
}

