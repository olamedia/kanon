<?php

class profiler{
    protected static $_enableTime = null;
    protected static $_instance = null;
    protected $_sql = array();
    protected static $_isEnabled = false;
    public static function isEnabled(){
        return self::$_isEnabled;
    }
    public static function enable(){
        self::$_isEnabled = true;
        self::$_enableTime = microtime(true);
    }
    public static function disable(){
        self::$_isEnabled = false;
    }
    /**
     *
     * Enter description here ...
     * @return profiler
     */
    public static function getInstance(){
        if (self::$_instance === null){
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    public function addSql($sql, $time){
        if (!self::$_isEnabled)
            return;
        $time = -$time;
        $time += microtime(true);
        $this->_sql[] = array(
            'sql'=>$sql,
            'time'=>$time,
            'trace'=>debug_backtrace(),
        );
    }
    public function getCss(){
        return '
		.kanon-profiler{
			background: #222;
			color: #999;
			font-size: 11px;
			font-family: Verdana;
			padding: 30px;
		}
		.kanon-profiler .sql td{
			padding: 7px;
			border: solid 1px #999;
		}
		.kanon-profiler .sql td div{
			padding-top: 7px;
		}
		
		';
    }
    protected function _getTraceController($trace){
        //var_dump($trace);
        foreach ($trace as $point){
            $class = $point['class'];
            if (is_string($class)){
                $parents = class_parents($class);
                if (in_array('controller', $parents)){
                    return $point;
                }
            }else{

            }
        }
        return false;
    }
    public function html(){
        $h = '<div class="kanon-profiler">';
        $h .= '<div>Total queries: '.count($this->_sql).'</div>';
        $totalSqlTime = 0;
        foreach ($this->_sql as $sqlInfo){
            $totalSqlTime+=$sqlInfo['time'];
        }
        $h .= '<div>Total runtime: '.number_format(microtime(true) - self::$_enableTime, 6, '.', '').'</div>';
        $h .= '<div>Total time: '.number_format($totalSqlTime, 6, '.', '').'</div>';
        $h .= '<table width="100%" class="sql">';
        foreach ($this->_sql as $sqlInfo){
            $h .= '<tr><td>';
            $h .= '<strong style="font-weight: normal;color: #ddd;">'.htmlspecialchars($sqlInfo['sql']).'</strong>';
            $h .= '<div>';
            if ($sqlInfo['time'] > 0.01){
                $h .= '<span style="color: #f00">';
            }
            $h .= 'Time: '.number_format($sqlInfo['time'], 6, '.', '');
            if ($sqlInfo['time'] > 0.01){
                $h .= '</span>';
            }
            if ($traceInfo = $this->_getTraceController($sqlInfo['trace'])){
                $h .= ' '.$traceInfo['class'].$traceInfo['type'].$traceInfo['function'].'() at line '.$traceInfo['line'];
            }
            $i = 0;
            $skip = array(
                'profiler', 'mysqlDriver', 'storageDriver', 'modelStorage',
                'modelExpression', 'modelQueryBuilder', 'modelResultSet'
            );
            foreach ($sqlInfo['trace'] as $traceInfo){
                if (in_array($traceInfo['class'], $skip)){
                    array_shift($sqlInfo['trace']);
                }else{
                    break;
                }
            }
            foreach ($sqlInfo['trace'] as $traceInfo){
                $i++;
                $h .= '<div style="line-height: 1em;font-size:10px;">';
                $h .= '#'.$i.' '.$traceInfo['class'].$traceInfo['type'].$traceInfo['function'].'() at line '.$traceInfo['line'];
                $h .= '</div>';
            }
            $h .= '</div>';
            $h .= '</td></tr>';
        }
        $h .= '</table>';
        return $h.'</div>';
    }
    public function __toString(){
        return $this->html();
    }
}