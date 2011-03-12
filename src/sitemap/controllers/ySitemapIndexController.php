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
 * ySitemapIndexController
 *
 * @package yuki
 * @subpackage sitemap
 * @author olamedia
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @version SVN: $Id$
 */
class ySitemapIndexController extends controller{
    public function _action($action){
        
    }
    public function _initIndex(){
        response::xml();
        response::sendHeaders();
        echo ySitemapIndex::getInstance()->setController($this);
    }
}

