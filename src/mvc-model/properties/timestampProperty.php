<?php
require_once dirname(__FILE__).'/integerProperty.php';
class timestampProperty extends integerProperty{
	protected $_size = 10;
	protected $_unsigned = true;
	/**
	 * @return string Human presentation
	 */
	public function format($format = "d.m.Y H:i:s"){
		return date($format, $this->getValue());
	}
	public function chanFormat(){//Вск 06 Дек 2009
		$ts = $this->getValue();
		$wa = array('Вск','Пнд','Втр','Срд','Чтв','Птн','Сбт');
		$ma = array(null,'Янв','Фев','Мар','Апр','Май','Июн','Июл','Авг','Сен','Окт','Ноя','Дек');
		return $wa[date("w", $ts)].' '.date("d", $ts).' '.$ma[date("n", $ts)].' '.date("Y H:i:s", $ts);
	}
}