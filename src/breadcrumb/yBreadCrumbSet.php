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
 * yBreadCrumbSet - Represents a set of Breadcrumbs.
 *
 * @package Expression package is undefined on line 12, column 15 in Templates/Scripting/PHPClass.php.
 * @subpackage 
 * @author olamedia
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @version SVN: $Id$
 */
class yBreadCrumbSet implements Countable{
    /**
     * First Breadcrumb in trail.
     * @var yBreadcrumb 
     */
    protected $_first = null;
    /**
     * Last Breadcrumb in trail.
     * @var yBreadcrumb 
     */
    protected $_last = null;
    protected static $_instances = array();
    /**
     *
     * @param string $name
     * @return yBreadCrumbSet 
     */
    public static function getInstance($name = 'default'){
        if (!isset(self::$_instances[$name])){
            self::$_instances[$name] = new self();
        }
        return self::$_instances[$name];
    }
    public function append($breadcrumb){
        if ($this->_first === null){
            $this->_first = $this->_last = $breadcrumb;
        }else{
            $this->_last->append($breadcrumb);
        }
    }
    public function __toString(){
        return (string) $this->_first;
    }
    public function count(){
        if ($this->_first === null){
            return 0;
        }
        return count($this->_first);
    }
}

