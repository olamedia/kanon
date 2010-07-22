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
	public function html(){
		$h = '';
		$h .= '<table width="100%">';
		foreach ($this->_sql as $sqlInfo){
			$h .= '<tr><td>';
			$h .= htmlspecialchars($sqlInfo['sql']);
			$h .= '<br />';
			$h .= 'Time: '.$sqlInfo['time'];
			$h .= '</td></tr>';
		}
		$h .= '</table>';
		return $h;
	}
	public function __toString(){
		return $this->html();
	}
}