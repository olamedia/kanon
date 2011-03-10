<?php

/*
 * This file is part of the yuki package.
 * Copyright (c) 2011 olamedia <olamedia@gmail.com>
 *
 * This source code is release under the MIT License.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * yMetaTag
 *
 * @package yuki
 * @subpackage html
 * @author olamedia
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @version SVN: $Id$
 */
class yMetaTag extends yHtmlTag{
    protected $_isSelfClosed = true;
    public function __construct($attr = array()){
        parent::__construct('meta', $attr);
    }
    public function setContent($content){
        $this->set('content', $content);
    }
    public function pushContent($content){
        $this->forceAttribute('content')->push($content);
    }
    public function popContent($content){
        $this->forceAttribute('content')->pop($content);
    }
}

