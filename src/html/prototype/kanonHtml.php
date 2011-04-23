<?php

/**
 * Description of html
 *
 * @author olamedia
 */
class kanonHtml{
    protected $_source = '';
    /**
     * @var DOMNode | DOMNodeList
     */
    protected $_dom = null;
    /**
     * @var DOMNode
     */
    protected $_contextNode = null;
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
    public static function fromDom($dom, $contextNode = null){
        $me = new self();
        $me->loadDom($dom, $contextNode);
        return $me;
    }
    public function setContext($contextNode){
        $this->_contextNode = $contextNode;
    }
    public function loadDom($dom, $contextNode = null){
        $this->_dom = $dom;
        $this->setContext($contextNode);
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
    protected function getXpath(){
        if ($this->_xpath === null){
            $this->_xpath = new DOMXpath($this->getDom());
        }
        return $this->_xpath;
    }
    public function toXml(){
        return $this->getDom()->saveXML();
    }
    public function  __toString(){
        return (string) $this->toXml();
    }
    /**
     * html::a(array('id'=>1,'class'=>'selected'));
     * @var array $attributes
     *
     * @return DOMElement
     */
    public static function createElement($tagname, $attributes = array()){
        $e = new DOMElement($tagname);
        foreach ($attributes as $k=>$v){
            $e->setAttribute($k, $v);
        }
    }
}

