<?php
/**
 * Authentication: Who is the user?
 * @author olamedia
 *
 */
class userIdentityPrototype{
	protected $_isAuthenticated = false;
	protected $_user = null;
	protected $_identityModel = null;
	/**
	 * @return boolean whether the identity is valid
	 */
	public function isAuthenticated(){
		return $this->_isAuthenticated;
	}
	public function isRegistered(){
		return is_object($this->_user);
	}
	public function getIdentityModel(){
		return $this->_identityModel;
	}
	public function getUserModel(){
		return $this->_user;
	}
}
