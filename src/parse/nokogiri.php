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
    public function get($expression){
        if (strpos($expression, ' ') !== false){
            $a = explode(' ', $expression);
            $first = array_shift($a);
            $sub = implode(' ', $a);
            echo ' find('.$first.')->get('.$sub.') ';
            return $this->getElements($first)->get($sub);
        }
        echo ' find('.$expression.') ';
        return $this->getElements($expression);
    }
    protected function getElements($expression){ // tag.class
        //echo ' get elements: ';
        $newDom = new DOMDocument('1.0', 'UTF-8');
        $root = $newDom->createElement('root');
        $newDom->appendChild($root);
        $query = '';
        if (preg_match("/([a-z0-9]+)?(#(\S+))?(\.(\S+))?/ims", $expression, $subs)){
            $tag = $subs[1];
            $id = $subs[3];
            $class = $subs[5];
            if (!strlen($tag))
                $tag = '*';
            $query = '//'.$tag;
            if (strlen($id)){
                $query .= "[@id='".$id."']";
            }
            if (strlen($class)){
                //$query .= "[@class='".$class."']";
                $query .= '[contains(concat(" ", normalize-space(@class), " "), " '.$class.' ")]';
            }
        }
        if (strlen($query)){
            echo ' query:'.$query.' ';
            $nodeList = $this->_xpath->query($query);
            if ($nodeList === false){
                throw new Exception('Malformed xpath');
            }
            // echo ' no errors ';
            /* append all nodes from $nodeList to the new dom, as children of $root: */
            foreach ($nodeList as $domElement){
                // echo ' node found ';
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
        //echo '<h3>to array</h3>';
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
            //echo ' has child nodes ';
            if ($node->childNodes->length == 1){
                $array[$node->firstChild->nodeName] = $this->toArray($node->firstChild);
            }else{
                foreach ($node->childNodes as $childNode){
                    //echo ' child ';
                    if ($childNode->nodeType != XML_TEXT_NODE){
                        $array[$childNode->nodeName][] = $this->toArray($childNode);
                    }
                }
            }
        }else{
            //$array['TEXT'] = $node->nodeValue;
        }
        if ($xnode === null){
            return reset($array); // first child
        }
        return $array;
    }
}

