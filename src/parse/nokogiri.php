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
        if (strlen($htmlString)) {
            libxml_use_internal_errors(TRUE);
            $dom->loadHTML($htmlString);
            libxml_clear_errors();
        }
        $this->loadDom($dom);
    }
    public function get($expression){
        if (strpos($expression, ' ') !== false){
            $a = explode(' ',$expression);
            $first = array_shift($a);
            $sub = implode(' ', $a);
            echo 'find('.$first.')->get('.$sub.')';
            return $this->getElements($first)->get($sub);
        }
        return $this->getElements($expression);
    }
    protected function getElements($expression){ // tag.class
        echo ' get elements: ';
        list($tag, $class) = explode('.', $expression);
        $query = "//".$tag.'[@class=\''.$class.'\']';
        echo $query;
        $nodeList = $this->_xpath->query($query);
        if ($nodeList === false){
            throw new Exception('Malformed xpath');
        }
        echo ' no errors ';
        $newDom = new DOMDocument('1.0', 'UTF-8');
        $root = $newDom->createElement('root');
        $newDom->appendChild($root);
        /* append all nodes from $nodeList to the new dom, as children of $root: */
        foreach ($nodeList as $domElement){
            echo ' node found ';
            $domNode = $newDom->importNode($domElement, true);
            $root->appendChild($domNode);
        }
        return self::fromDom($newDom);
    }
    public function toXml(){
        return $this->_dom->saveXML();
    }
    public function toArray(){
        $array = array();
        $node = $this->_dom;
        if ($node->hasAttributes()){
            foreach ($node->attributes as $attr){
                $array[$attr->nodeName] = $attr->nodeValue;
            }
        }
        if ($node->hasChildNodes()){
            if ($node->childNodes->length == 1){
                $array[$node->firstChild->nodeName] = $node->firstChild->nodeValue;
            }else{
                foreach ($node->childNodes as $childNode){
                    if ($childNode->nodeType != XML_TEXT_NODE){
                        $array[$childNode->nodeName][] = $this->toArray($childNode);
                    }
                }
            }
        }
        return $array;
    }
}

