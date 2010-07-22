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
		);
	}
	public function getCss(){
		return '
		.kanon-profiler{
			background: #222;
			color: #eee;
			font-size: 11px;
			font-family: Verdana;
		}
		.kanon-profiler .sql td{
			padding: 3px;
			border: solid 1px #ddd;
		}
		
		';
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
			$h .= '<strong style="font-weight: normal;color: #fff;">'.htmlspecialchars($sqlInfo['sql']).'</strong>';
			$h .= '<br />';
			$h .= 'Time: '.number_format($sqlInfo['time'], 6,'.','');
			$h .= '</td></tr>';
		}
		$h .= '</table>';
		return $h.'</div>';
	}
	public function __toString(){
		return $this->html();
	}
}