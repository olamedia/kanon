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
 * ySitemap
 *
 * @package yuki
 * @subpackage sitemap
 * @author olamedia
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @version SVN: $Id$
 */
class ySitemap{
    /**
     * URL of the sitemap.
     * @var string 
     */
    protected $_loc = null;
    /**
     * The date of last modification of the sitemap.
     * Year:
     *      YYYY (eg 1997)
     * Year and month:
     *      YYYY-MM (eg 1997-07)
     * Complete date:
     *      YYYY-MM-DD (eg 1997-07-16)
     * Complete date plus hours and minutes:
     *      YYYY-MM-DDThh:mmTZD (eg 1997-07-16T19:20+01:00)
     * Complete date plus hours, minutes and seconds:
     *      YYYY-MM-DDThh:mm:ssTZD (eg 1997-07-16T19:20:30+01:00)
     * Complete date plus hours, minutes, seconds and a decimal fraction of a second
     *      YYYY-MM-DDThh:mm:ss.sTZD (eg 1997-07-16T19:20:30.45+01:00)
     * @var string 
     */
    protected $_lastmod = null;
    protected $_set = null;
    protected $_outputMode = 'sitemap';
    protected $_controller = null;
    public function setController($sitemapController){
        $this->_controller = $sitemapController;
        return $this;
    }
    protected $_name = null;
    public function setName($name){
        $this->_name = $name;
        return $this;
    }
    public function __construct($url){
        $this->_loc = $url;
        $this->_set = new ySitemapUrlSet();
    }
    /**
     *
     * @param ySitemapUrl $url
     * @return ySitemap
     */
    public static function create($url){
        return new ySitemap($url);
    }
    public function add($url){
        $this->_set->add($url);
        return $this;
    }
    public function setTime($timestamp){
        // YYYY-MM-DDThh:mmTZD
        $this->_lastmod = date('c', $timestamp); // as of php5
        return $this;
    }
    public function setDate($timestamp){
        // Complete date: YYYY-MM-DD
        $this->_lastmod = date('Y-m-d', $timestamp); // as of php5
        return $this;
    }
    public function setOutputMode($mode){
        $this->_outputMode = $mode;
        return $this;
    }
    public function __toString(){

        if ($this->_outputMode == 'sitemap'){
            return '<?xml version="1.0" encoding="UTF-8"?>'.$this->_set;
        }else{
            if ($this->_controller instanceof controller){
                $a = array('<loc>'.$this->_controller->rel($this->_name, true).'</loc>');
            }else{
                $a = array('<loc>'.$this->_loc.'</loc>');
            }
            if ($this->_lastmod !== null){
                $a[] = '<lastmod>'.$this->_lastmod.'</lastmod>';
            }
            return '<sitemap>'."\n\t".implode("\n\t", $a)."\n".'</sitemap>';
        }
    }
}
