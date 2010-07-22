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
		.kanon-profiler .sql td{
			padding: 3px;
			border: solid 1px #ddd;
		}
		
		';
	}
	public function html(){
		$h = '<div class="kanon-profiler">';
		$h .= '<table width="100%" class="sql">';
		foreach ($this->_sql as $sqlInfo){
			$h .= '<tr><td>';
			$h .= htmlspecialchars($sqlInfo['sql']);
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