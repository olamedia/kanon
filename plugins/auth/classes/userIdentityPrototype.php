<?php
/**
 * @desc-ru Личность пользователя, удостоверение.
 * Основной метод - authenticate(), должен убедиться в подлинности
 * предъявленного удостоверения (e-mail, url, etc), 
 * сравнив пароли или используя особые процедуры (openid, oauth etc).
 * Параметры передаются заранее, в конструкторе.
 * Специальный метод isRegistered() предоставляет информацию о том, 
 * был ли зарегистрирован пользователь с таким удостоверением.
 * Если да, то метод getUserModel() должен вернуть модель с информацией
 * о пользователе, иначе - новую модель, без данных.
 * @author		olamedia
 * @copyright	Copyright © 2010, olamedia
 * @license		http://www.opensource.org/licenses/mit-license.php MIT
 * @version		SVN: $Id$
 */
abstract class userIdentityPrototype{
	protected $_isAuthenticated = false;
	protected $_user = null;
	protected $_identityModels = array();
	/**
	 * @return boolean whether the identity is valid
	 */
	public function isAuthenticated(){
		return $this->_isAuthenticated;
	}
	public function isRegistered(){
		return is_object($this->_user);
	}
	public function getIdentityModels(){
		return $this->_identityModels;
	}
	public function getUserModel(){
		return $this->_user;
	}
	/*	public function setUserModel($userModel){
		$this->_user = $userModel;
		}*/
}
