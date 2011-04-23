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
 * yProfiler
 *
 * @package yuki
 * @subpackage profiler
 * @version SVN: $Id$
 * @revision SVN: $Revision$
 * @date $Date$
 * @author olamedia
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */
class yProfiler{
    /**
     * Tick time to register in benchmark log.
     */
    const LONG_TIME = 0.001;
    const TICK = 1;
    /**
     * Singleton instance.
     * @var yProfiler
     */
    protected static $_instance = null;
    protected static $lastTickTime = null;
    protected $_benchmarkLog = array();
    protected static $_callStatistics = array();
    protected static $_callMaxStatistics = array();
    /**
     * Get yProfiler instance.
     * @return yProfiler
     */
    public static function getInstance(){
        if (self::$_instance === null){
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    public function shutdown(){

    }
    /**
     * Starts profiler instance.
     * Register profiler handlers.
     */
    public static function start(){
        $profiler = self::getInstance();
        register_shutdown_function(array($profiler, 'shutdown'));
        register_tick_function(array($profiler, 'tickHandler'));


        declare(ticks=1); // will make first call to tickHandler
    }
    public function log($message, &$trace){
        $call = $trace[0];
        $this->_log[] = array($message, $trace);
        //$f = $call['file']; // called from this file
        //$l = $call['line']; // called at this line
    }
    /**
     * Tick handler.
     * Make use of declare(ticks=1); to log execution time.
     */
    public function tickHandler(){
        $time = microtime(true);
        if (self::$lastTickTime === null){
            self::$lastTickTime = $time;
        }
        $dt = $time - self::$lastTickTime;
        $trace = debug_backtrace();
        foreach ($trace as $i=>$t){
            $method = 'main';
            if (isset($t['function'])){
                $method = $t['function'];
                if (isset($t['class'])){
                    $method = $t['class'].'::'.$method;
                }
            }
            if ($i == 1){
                if (!isset(self::$_callStatistics[$method])){
                    self::$_callStatistics[$method] = 0;
                }
                self::$_callStatistics[$method] += $dt;
            }
            if (!isset(self::$_callMaxStatistics[$method])){
                self::$_callMaxStatistics[$method] = 0;
            }
            self::$_callMaxStatistics[$method] += $dt;
        }
        /* if (isset($trace[1])){
          $t = $trace[1];
          $method = 'main';
          if (isset($t['function'])){
          $method = $t['function'];
          if (isset($t['class'])){
          $method = $t['class'].'::'.$method;
          }
          }
          }else{
          $method = 'main';
          }
          if (!isset(self::$_callStatistics[$method])){
          self::$_callStatistics[$method] = 0;
          }
          self::$_callStatistics[$method] += $dt; */
        if ($dt > yProfiler::LONG_TIME){ // FIXME
            $this->_benchmarkLog[] = array($dt, $trace);
        }
        // connection_aborted()
        // memory_get_usage()
        //
        self::$lastTickTime = microtime(true);
    }
    public static function finish(){
        self::getInstance()->shutdown();
    }
    /**
     * @magic
     * @return string
     */
    public function __toString(){
        $s = '';
        foreach ($this->_benchmarkLog as $log){
            $s .= number_format($log[0], 6, '.', '')." at ".$log[1][0]['file'].":".$log[1][0]['line']." \n";
        }
        return $s;
    }
    public static function html(){
        arsort(self::$_callStatistics);
        $stat = array();
        $sum = 0;
        foreach (self::$_callStatistics as $method=>$time){
            $sum += $time;
            $stat[$method.' - '.number_format($time, 4, '.', '')] = $time;
        }
        foreach (self::$_callMaxStatistics as $method=>$time){
            $stat[$method.' MAX - '.number_format($time, 4, '.', '')] = $time;
        }
        $stat['Total: '.number_format($sum, 4, '.', '')] = $sum;
        arsort($stat);
        $stat = array_keys($stat);
        return '<div style="font-size: 11px;font-weight: normal;line-height: 1.2em;color: #fff;background: #333;padding: 10px;">'.
        nl2br(strval(self::$_instance)).
        '<hr />'.
        implode('<br />', $stat).
        '</div>';
    }
}

/*
  yProfiler::start();

  for ($i = 1; $i < 3; $i++){
  echo ($i)."\n";
  sleep(1);
  }
  echo yProfiler::getInstance();
  var_dump(yProfiler::getInstance());
 */