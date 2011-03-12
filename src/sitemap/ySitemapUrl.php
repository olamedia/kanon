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
 * ySitemapUrl
 *
 * @package yuki
 * @subpackage sitemap
 * @author olamedia
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @version SVN: $Id$
 */
class ySitemapUrl{
    const
    FREQ_ALWAYS = 'always',
    FREQ_HOURLY = 'hourly',
    FREQ_DAILY = 'daily',
    FREQ_WEEKLY = 'weekly',
    FREQ_MONTHLY = 'monthly',
    FREQ_YEARLY = 'yearly',
    FREQ_NEVER = 'never'
    ;
    /**
     * URL of the page.
     * @var string 
     */
    protected $_loc = null;
    /**
     * The date of last modification of the file.
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
    /**
     * How frequently the page is likely to change.
     * always|hourly|daily|weekly|monthly|yearly|never
     * @var string
     */
    protected $_changefreq = null;
    /**
     * The priority of this URL relative to other URLs on your site. 
     * 0.0...1.0
     * @var float
     */
    protected $_priority = null;
    public function __construct($location){
        $this->_loc = $location;
    }
    public function getLocation(){
        return $this->_loc;
    }
    /**
     *
     * @param string $location
     * @return ySitemapUrl 
     */
    public static function create($location){
        return new self($location);
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
    public function setChangeFreq($freq){
        $this->_changefreq = $freq;
        return $this;
    }
    public function setPriority($priority){
        $this->_priority = $priority;
        return $this;
    }
    public function __toString(){
        $a = array('<loc>'.$this->_loc.'</loc>');
        if ($this->_lastmod !== null){
            $a[] = '<lastmod>'.$this->_lastmod.'</lastmod>';
        }
        if ($this->_changefreq !== null){
            $a[] = '<changefreq>'.$this->_changefreq.'</changefreq>';
        }
        if ($this->_priority !== null){
            $a[] = '<priority>'.number_format($this->_priority, 1, '.', '').'</priority>';
        }
        return '<url>'."\n\t".implode("\n\t", $a)."\n".'</url>';
    }
}

/*
echo ySitemapUrl::create('http://revda09.ru/')
        ->setChangeFreq(ySitemapUrl::FREQ_HOURLY)
        ->setTime(time())
        ->setPriority(0.4);

*/