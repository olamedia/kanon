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
 * yPage
 *
 * @package yuki
 * @subpackage page
 * @author olamedia
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 * @version SVN: $Id$
 */
class yPage{
    protected $_meta = null;
    protected $_language = 'en';
    protected $_charset = 'UTF-8';
    protected $_title = null;
    protected $_registry = null;
    public function __construct(){
        $this->_registry = new yRegistry();
        $this->_meta = new yPageMetaCollection();
    }
    public function getTitle(){
        return $this->_registry->get('content/title', '');
    }
    public function setTitle($title){
        $this->_registry->set('content/title', $title);
        return $this;
    }
    public function getDescription(){
        return $this->_registry->get('content/description', '');
    }
    public function setDescription($description){
        $this->_registry->set('content/description', $description);
        return $this;
    }
    // NOODP NOYDIR
    // <META NAME="Slurp" CONTENT="NOYDIR">
    // class="robots-nocontent" Yahoo
    public function index(){
        $this->_meta->replace('robots', 'noindex', 'index');
        return $this;
    }
    public function noIndex(){
        $this->_meta->replace('robots', 'index', 'noindex');
        return $this;
    }
    public function follow(){
        $this->_meta->replace('robots', 'nofollow', 'follow');
        return $this;
    }
    public function noFollow(){
        $this->_meta->replace('robots', 'follow', 'nofollow');
        return $this;
    }
    public function archive(){
        $this->_meta->replace('robots', 'noarchive', 'archive');
        return $this;
    }
    public function noArchive(){
        $this->_meta->replace('robots', 'archive', 'noarchive');
        return $this;
    }
    public function getLanguage(){
        return $this->_registry->get('content/language', 'en');
    }
    public function setLanguage($language){
        $this->_registry->set('content/language', $language);
        //$this->setHttp('Content-Language', $language);
        //$this->setMeta('language', $language);
        return $this;
    }
    public function getCharset(){
        return $this->_registry->get('content/charset', 'UTF-8');
    }
    public function setCharset($charset){
        $this->_registry->set('content/charset', $charset);
        return $this;
    }
    public function setMeta($name, $content, $isHttp = false){
        $this->_meta->set($name, $content, $isHttp);
        return $this;
    }
    public function setHttp($name, $content){
        $this->_meta->set($name, $content, true);
        return $this;
    }
    /**
     *
     * @param type $code ISO 3166-1 alpha-2 for countries and territories
     * @param type $name 
     */
    public function setGeoCountry($code, $name){
        $this->_registry->set('content/geo/country-code', $code);
        $this->_registry->set('content/geo/country', $name);
        $this->_updateGeoRegion();
    }
    public function setGeoLocation($name){
        $this->_registry->set('content/geo/placename', $name);
        $this->_updateGeoRegion();
    }
    public function setGeoRegion($name){
        $this->_registry->set('content/geo/region', $name);
        $this->_updateGeoRegion();
    }
    protected function _updateGeoRegion(){
        $countryCode = $this->_registry->get('content/geo/country-code', false);
        $country = $this->_registry->get('content/geo/country', false);
        $region = $this->_registry->get('content/geo/region', false);
        $location = $this->_registry->get('content/geo/location', false);
        //<meta name="geo.region" content="RU-город Москва" />
        if ($countryCode && $region){
            $this->setMeta('geo.region', $countryCode.'-'.$region);
        }
        //<meta name="geo.placename" content="город Москва, Россия" />
        if ($country && $location){
            $this->setMeta('geo.placename', $location.', '.$country);
        }
    }
    /**
     * Sets the following meta tags:
     * <meta name="geo.position" content="55.755786;37.617633" />
     * <meta name="ICBM" content="55.755786, 37.617633" />
     * @param type $lat
     * @param type $lng 
     */
    public function setGeoPosition($lat, $lng){
        $this->_registry->set('content/geo/lat', $lat);
        $this->_registry->set('content/geo/lng', $lng);
        $this->setMeta('geo.position', $lat.';'.$lng);
        $this->setMeta('ICBM', $lat.', '.$lng);
    }
    public function requireCss($uri){
        $this->_registry->push('css/include', $uri);
        return $this;
    }
    public function getStylesheets(){
        $list = new yHtmlTagList();
        foreach ($this->_registry->get('css/include', array()) as $uri){
            $list->appendChild(yHtmlTag::create(
                            'link', array(
                        'rel'=>'stylesheet',
                        'type'=>'text/css',
                        'href'=>$uri
                            ), true
                    ));
        }
        return $list;
    }
    public function getScripts(){
        $list = new yHtmlTagList();
        foreach ($this->_registry->get('js/include', array()) as $uri){
            $list->appendChild(yHtmlTag::create(
                            'script', array(
                        'type'=>'text/javascript',
                        'src'=>$uri
                            ), false
                    ));
        }
        return $list;
    }
    public function requireJs($uri){
        $this->_registry->push('js/include', $uri);
        return $this;
    }
    /**
     * Small subsets of css can be included directly into the HTML document.
     * This reduces the number of requests made by a web page 
     * This can reduce the time it takes to display content to the user, especially in older browsers.
     */
    public function css($cssString){
        $this->_registry->append('css/plain', $cssString);
        return $this;
    }
    /**
     * Small subsets of javscript can be included directly into the HTML document.
     * This reduces the number of requests made by a web page 
     * This can reduce the time it takes to display content to the user, especially in older browsers.
     */
    public function js($jsString){
        $this->_registry->append('js/plain', $jsString);
        return $this;
    }
    public function getStyleTag(){
        $css = $this->_registry->get('css/plain', '');
        if (strlen($css)){
            $tag = new yStyleTag();
            $tag->text($css);
            return $tag;
        }
        return new yTextNode();
    }
    public function getScriptTag(){
        $js = $this->_registry->get('js/plain', '');
        if (strlen($js)){
            $tag = yHtmlTag::create('script', array('type'=>'text/javascript'));
            $tag->text($js);
            return $tag;
        }
        return new yTextNode();
    }
    public function getHtmlStart(){
        $eol = "\r\n";
        //<?xml version="1.0" encoding="utf-8"?
        return '<!DOCTYPE html><html lang="'.$this->getLanguage().'">'."\n".$this->getHead();
    }
    public function getHead(){
        //<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        $head = yHtmlTag::create('head')
                // http-equiv="content-type" content="text/html;charset=utf-8"
                // charset="utf-8"
                ->appendChild(yHtmlTag::create('meta', array('charset'=>$this->getCharset())))
                ->appendChild(yHtmlTag::create('meta', array(
                            'http-equiv'=>'X-UA-Compatible',
                            'content'=>'IE=edge,chrome=1'
                        )))
                //<meta name="viewport" content="width=device-width, initial-scale=1.0" />
                ->appendChild(yHtmlTag::create('meta', array(
                            'name'=>'viewport',
                            'content'=>'width=device-width, initial-scale=1.0'
                        )))
                ->appendChild(yHtmlTag::create('title')->text($this->getTitle()))
                ->addMeta('title', htmlspecialchars($this->getTitle()));
        if (strlen($this->getDescription())){
            $head->addMeta('description', $this->getDescription());
        }
        foreach ($this->_meta->toArray() as $meta){
            $head->appendChild($meta);
        }
        // DO NOT CHANGE: Put external scripts after external stylesheets if possible.
        $head->appendChild($this->getStyleTag());
        $head->appendChild($this->getStylesheets());
        $head->appendChild($this->getScripts());
        //var_dump(yHtmlTag::create('meta', array('charset'=>$this->getCharset())));
        //return $head;
        // Favicon:
        $head->appendChild(yHtmlTag::create('link', array('rel'=>'shortcut icon', 'href'=>'/favicon.ico'), true));
        // DO NOT CHANGE: Put inline scripts after other resources if possible.
        $head->appendChild($this->getScriptTag());
        return $head;
        return substr(yHtmlHelper::format($head), strlen('<?xml version="1.0"?>') + 1);
    }
}

/*
$page = new yPage();
$page->setLanguage('ru')
        ->setTitle('My Page')
        ->noIndex()
        ->follow()
        ->css('body{color: #111;}')
        ->js('alert("hello!")')
        ->requireCss('/css.css')
        ->requireJs('/js.js')
;
echo $page->getHtmlStart();*/