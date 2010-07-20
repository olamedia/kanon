<?php

// header('X-XRDS-Location: http://'.$_SERVER['SERVER_NAME'].$this->rel('OpenIDXRDS'));
// echo '<meta http-equiv="X-XRDS-Location" content="http://'.$_SERVER['SERVER_NAME'].$this->rel('OpenIDXRDS').'" />';

class openidUserIdentity extends userIdentityPrototype{
	protected $_openidIdentity = '';
	public function __construct($openidIdentity){
		$this->_openidIdentity = $openidIdentity;
	}
	protected function authenticateOpenId($openidIdentity){
		// 3rd-party library: http://gitorious.org/lightopenid
		// Required: PHP 5, curl
		$openid = new LightOpenID;
		if (isset($_GET['openid_mode'])){
			$result = $openid->validate();
			$this->_openidIdentity = $openid->identity;
			return $result;
		}
		$openid->identity = $openidIdentity;
		header('Location: '.$openid->authUrl());
		exit;
	}
	/**
	 * @return boolean whether authentication succeeds
	 */
	public function authenticate(){
		$openid = $this->_openidIdentity;
		if (!$this->authenticateOpenId($openid)){
			throw new authException('Invalid OpenID');
		}
		$openid = $this->_openidIdentity;
		$users = user::getCollection(); //modelCollection::getInstance('registeredUser');
		$openids = modelCollection::getInstance('userOpenid');
		$result = $users->select($openids, $openids->openid->is($this->_openidIdentity))->fetch();
		if (!$result){
			// throw new authException('OpenID "'.$this->_openidIdentity.'" not registered', authException::ERROR_NOT_REGISTERED);
			// autocreate:
			$user = new registeredUser();
			$user->save();
			$userOpenid = new userOpenid();
			$userOpenid->userId = $user->id;
			$userOpenid->openid = $this->_openidIdentity;
			$userOpenid->save();
		}else{
			list($user, $userOpenid) = $result;
		}
		/*if (!$user->password->equals($this->_password)){
			throw new authException('Invalid password', authException::ERROR_PASSWORD_INVALID);
		}*/
		$this->_user = $user;
		$this->_identityModel = $userOpenid;
		return true;
	}

	/**
	 * @return mixed a value that uniquely represents the identity
	 */
	public function getId(){
		return $this->_login;
	}
	/**
	 * @return string display name
	 */
	public function getName(){
		return $this->getId();
	}
}