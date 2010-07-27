<?php
class loginUserIdentity extends userIdentityPrototype{
	protected $_login = '';
	protected $_password = ''; // @todo convert to hash, ex: md5($password)
	public function __construct($login, $password){
		$this->_login = $login;
		$this->_password = $password;
	}
	/**
	 * @return boolean whether authentication succeeds
	 */
	public function authenticate(){
		$users = user::getCollection(); //modelCollection::getInstance('registeredUser');
		$logins = modelCollection::getInstance('userLogin');
		$result = $users->select($logins, $logins->login->is($this->_login))->fetch();
		if (!$result){
			throw new authException('Login not registered', authException::ERROR_USERNAME_INVALID);
		}
		list($user, $userLogin) = $result;
		if (!$user->password->equals($this->_password)){
			throw new authException('Invalid password', authException::ERROR_PASSWORD_INVALID);
		}
		$this->_user = $user;
		$this->_identityModels['login'][$this->_login] = $userLogin;
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
	public function register() {
		$userLogin = new userLogin();
		$userLogin->userId = $this->_user->id;
		$userLogin->login = $this->_login;
		$userLogin->save();
	}
}