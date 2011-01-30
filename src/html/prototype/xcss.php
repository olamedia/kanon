<?php

/**
 * Extended css parser (sass inspired http://sass-lang.com/)
 * 
 * $xcss = new xcss('style.xcss');
 * echo $xcss;
 * $xcss->save('style.css');
 *
 * @author olamedia
 *
 * xcss syntax:
 *
 *
 * $blue: #3bbfce;
 * $margin: 16px;
 * 
 *
 */
class xcss{
    protected $_tokens = array(
        'comment'=>array(
            'open'=>'/*',
            'close'=>'*/'
        ),
        /* 'expression'=>array(
          'close'=>';',
          'contains'=>array(
          'string', 'string2'
          )
          ), */
        'block'=>array(
            'open'=>'{',
            'close'=>'}',
            'contains'=>array(
                'block', 'expression', 'string', 'string2'
            )
        ),
        'string'=>array(
            'open'=>'"',
            'close'=>'"',
            'escape'=>'\\',
        ),
        'string2'=>array(
            'open'=>"'",
            'close'=>"'",
            'escape'=>'\\',
        ),
    );
    protected $_tokensPrepared = array();
    protected $_syntax = array();
    protected $_filename = null;
    protected $_source = null;
    protected $_blocks = array();
    protected $_vars = array();
    const
    XCSS_TEXT = 0,
    XCSS_BLOCK = 1,
    XCSS_EXPRESSION = 2;
    protected function _prepareTokens(){
        foreach ($this->_tokens as $name=>$a){
            foreach ($a as $k=>$v){
                $this->_tokensPrepared[$k][$name] = $v;
            }
        }
    }
    public function __construct($filename){
        if (!is_file($filename)){
            throw new Exception('no such file');
        }
        $this->_filename = $filename;
        $this->_prepareTokens();
        $this->getSource();
    }
    public function &getSource(){
        if ($this->_source === null){
            $this->_source = file_get_contents($this->_filename);
            $this->_stripComments();
            $this->_explode();
        }
        return $this->_source;
    }
    protected function _stripComments(){
        while (($p = strpos($this->_source, '/*')) !== false){
            if (($cp = strpos($this->_source, '*/', $p)) !== false){
                $this->_source = substr($this->_source, 0, $p).substr($this->_source, $cp);
            }
        }
    }
    protected function _explode(){
        $offset = 0;
        while ($block = $this->_getBlock($offset)){
            $this->_blocks[] = $block;
            $offset = $block['close'];
        }
    }
    protected function _getChild($offset, $closing, $level = 0){
        echo str_repeat(' ', $level)."$offset _getChild?\n";
        $clp = strpos($this->_source, $closing, $offset);
        list($nextType, $nextP, $nextOp, $nextCl) = $this->_getBlockOpen($offset);
        if ($clp === false){
            // error
            var_dump($this->_blocks);
            var_dump($closing);
            throw new Exception('closing token not found');
        }
        if ($nextP === false){
            return false;
        }
        if ($nextP > $clp){
            return false;
        }
        return $this->_getBlock($offset, $clp, $level + 1);
    }
    protected function _getBlock($offset, $endOffset = false, $level = 0){
        echo str_repeat(' ', $level)."$offset ";
        $block = array(
            'type'=>'text',
            'childNodes'=>array(),
            'content'=>'',
            'open'=>$offset,
            'close'=>$offset
        );
        list($type, $p, $op, $closing) = $this->_getBlockOpen($offset);
        if ($endOffset !== false && $p > $endOffset){
            $block['close'] = $endOffset;
            $block['content'] = substr($this->_source, $offset, $endOffset - $offset);
            echo "text ".$block['content']."\n";
            return $block;
        }elseif ($closing == ''){
            $p = strlen($this->_source);
            if ($endOffset !== false && $p > $endOffset){
                $p = $endOffset;
            }
            if ($p > $offset){
                $block['close'] = $p;
                $block['content'] = substr($this->_source, $offset, $p - $offset);
                echo "text ".$block['content']."\n";
                return $block;
            }
            return false;
        }elseif ($p > $offset){
            $block['close'] = $p;
            $block['content'] = substr($this->_source, $offset, $p - $offset);
            echo "text ".$block['content']."\n";
            return $block;
        }else{
            echo "$op $type\nsearching child nodes...\n";
            $block['type'] = $type;
            $childOffset = $p + strlen($op);
            while ($node = $this->_getChild($childOffset, $closing, $level + 1)){
                $childOffset = $node['close'];
                $block['childNodes'][] = $node;
            }
            echo "$op $type\nsearching child nodes finished\n";
            $clp = strpos($this->_source, $closing, $childOffset);
            if ($clp > $childOffset){
                $node = $this->_getBlock($childOffset, $clp-1, $level+1);
                $block['childNodes'][] = $node;
            }
            $block['close'] = $clp;
            echo "$closing $type closed\n";
            return $block;
        }
    }
    protected function _getBlockClose($offset){
        return $this->_getBlockOpen($offset, 'close');
    }
    protected function _getBlockOpen($offset = 0, $type = 'open'){
        $min = array('text', 0, '', '');
        $minP = null;
        $tokens = $this->_tokensPrepared[$type];
        foreach ($tokens as $type=>$op){
            $p = strpos($this->_source, $op, $offset);
            if ($p !== false){
                if ($minP === null || $p < $minP){
                    $minP = $p;
                    $closing = $this->_tokens[$type]['close'];
                    $min = array($type, $p, $op, $closing);
                }
            }
        }
        return $min;
    }
}

