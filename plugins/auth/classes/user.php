<?php
class user{
	protected $_isAuthenticated = false;
	protected $_credentials = array();
	protected $_identity = null;
	protected $_model = 'registeredUser'; // real model
	/**
	 * Login using valid authenticated identity
	 * @example
	 * $identity=new emailUserIdentity($email,$password);
	 * if ($identity->authenticate()) kanon::app()->user->login($identity);
	 * @param IUserIdentity $identity
	 * @param integer $timeout Keep the user logged in for [default is 7 days].
	 */
	public function login($identity, $timeout = 604800){
		$this->_identity = $identity;
		$this->setAuthenticated();
	}
	/*public function model(){
		
	}*/
	public function getCollection(){
		return modelCollection::getInstance($this->_model);
	}
	public function logout(){
		$this->_identity = null;
		$this->setAuthenticated(false);
		$this->clearCredentials();
	}
	public function setAuthenticated($isAuthenticated = true){
		$this->_isAuthenticated = $isAuthenticated;
	}
	public function isAuthenticated(){
		return $this->_isAuthenticated;
	}
	public function addCredentials(){ // assign, addCredentials
		$credentials = func_get_args();
		foreach ($credentials as $credential){
			$this->addCredential($credential);
		}
	}
	public function addCredential($credential){
		$this->_credentials[$credential] = true;
		return $this;
	}
	public function hasCredential($credential){
		return isset($this->_credentials[$credential]);
	}
	public function removeCredential($credential){ // revoke, removeCredential
		unset($this->_credentials[$credential]);
		return $this;
	}
	public function clearCredentials(){
		$this->_credentials = array();
		return $this;
	}
}