<?php
class profiler{
	protected static $_instance = null;
	protected $_sql = array();
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
		$this->_sql[] = array(
		'sql' => $sql,
		'time' => $time,
		'trace' => debug_backtrace(),
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
		foreach ($trace as $point){
			$class = $point['class'];
			$parents = class_parents($class);
			if (in_array('controller', $parents)){
				return $traceInfo;
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
		$h .= '<div>Total time: '.number_format($totalSqlTime, 6,'.','').'</div>';
		$h .= '<table width="100%" class="sql">';
		foreach ($this->_sql as $sqlInfo){
			$h .= '<tr><td>';
			$h .= '<strong style="font-weight: normal;color: #ddd;">'.htmlspecialchars($sqlInfo['sql']).'</strong>';
			$h .= '<div>';
			if ($sqlInfo['time'] > 0.01){
				$h .= '<span style="color: #f00">';
			}
			$h .= 'Time: '.number_format($sqlInfo['time'], 6,'.','');
			if ($sqlInfo['time'] > 0.01){
				$h .= '</span>';
			}
			if ($traceInfo = $this->_getTraceController($sqlInfo['trace'])){
				$h .= ' '.$traceInfo['class'].'::'.$traceInfo['function'].'() at line #'.$traceInfo['line'];
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