<?php

/**
 * Simple HTML parser
 *
 * @author olamedia <olamedia@gmail.com>
 * @deprecated
 * @see https://github.com/olamedia/nokogiri
 */
class nokogiri implements IteratorAggregate{
    protected $_source = '';
    /**
     * @var DOMDocument
     */
    protected $_dom = null;
    /**
     * @var DOMDocument
     */
    protected $_tempDom = null;
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
            foreach ($a as $k=>$sub){
                $a[$k] = $this->getXpathSubquery($sub);
            }
            return $this->getElements(implode('', $a));
        }
        return $this->getElements($this->getXpathSubquery($expression));
    }
    protected function getNodes(){

    }
    protected function getDom(){
        if ($this->_dom instanceof DOMDocument){
            return $this->_dom;
        }elseif ($this->_dom instanceof DOMNodeList){
            if ($this->_tempDom === null){
                $this->_tempDom = new DOMDocument('1.0', 'UTF-8');
                $root = $this->_tempDom->createElement('root');
                $this->_tempDom->appendChild($root);
                foreach ($this->_dom as $domElement){
                    $domNode = $this->_tempDom->importNode($domElement, true);
                    $root->appendChild($domNode);
                }
            }
            return $this->_tempDom;
        }
    }
    protected function getXpath(){
        if ($this->_xpath === null){
           $this->_xpath = new DOMXpath($this->getDom());
        }
        return $this->_xpath;
    }
    protected function getXpathSubquery($expression){
        $query = '';
        if (preg_match("/(?P<tag>[a-z0-9]+)?(\[(?P<attr>\S+)=(?P<value>\S+)\])?(#(?P<id>\S+))?(\.(?P<class>\S+))?(:(?P<pseudo>first-child))?/ims", $expression, $subs)){
            $tag = isset($subs['tag']) && !empty($subs['tag'])?$subs['tag']:'*';
            $query = '//'.$tag;
            if (isset($subs['id']) && !empty($subs['id'])){
                $query .= "[@id='".$subs['id']."']";
            }
            if (isset($subs['attr']) && !empty($subs['attr'])){
                $attrValue = isset($subs['value']) && !empty($subs['value'])?$subs['value']:'';
                $query .= "[@".$subs['attr']."='".$attrValue."']";
            }
            if (isset($subs['class']) && !empty($subs['class'])){
                //$query .= "[@class='".$class."']";
                $query .= '[contains(concat(" ", normalize-space(@class), " "), " '.$subs['class'].' ")]';
            }
            if (isset($subs['pseudo']) && !empty($subs['pseudo'])){
                //$query .= "[@class='".$class."']";
                if ('first-child' === $subs['pseudo']){
                    $query .= '[1]';
                }
            }
        }
        return $query;
    }
    protected function getElements($xpathQuery){
        if (strlen($xpathQuery)){
            $nodeList = $this->getXpath()->query($xpathQuery);
            if ($nodeList === false){
                throw new Exception('Malformed xpath');
            }
            return self::fromDom($nodeList);
        }
    }
    public function toXml(){
        return $this->getDom()->saveXML();
    }
    public function toArray($xnode = null){
        $array = array();
        if ($xnode === null){
            if ($this->_dom instanceof DOMNodeList){
                foreach ($this->_dom as $node){
                    $array[] = $this->toArray($node);
                }
                return $array;
            }
            $node = $this->getDom();
        }else{
            $node = $xnode;
        }
        if (in_array($node->nodeType, array(XML_TEXT_NODE,XML_COMMENT_NODE))){
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
                    $array[$childNode->nodeName][] = $this->toArray($childNode);
                }
            }
        }
        if ($xnode === null){
            return reset(reset($array)); // first child
        }
        return $array;
    }
    public function getIterator(){
        $a = $this->toArray();
        return new ArrayIterator($a);
    }
}

