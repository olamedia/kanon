<?php
class emailUserIdentity extends userIdentityPrototype{
	protected $_email = '';
	protected $_password = ''; // @todo convert to hash, ex: md5($password)
	public function __construct($email, $password){
		$this->_email = $email;
		$this->_password = $password;
	}
	/**
	 * @return boolean whether authentication succeeds
	 */
	public function authenticate(){
		$users = user::getCollection(); //modelCollection::getInstance('registeredUser');
		$emails = modelCollection::getInstance('userEmail');
		$result = $users->select($emails, $emails->email->is($this->_email))->fetch();
		if (!$result){
			throw new authException('Email not registered', authException::ERROR_EMAIL_INVALID);
		}
		list($user, $userEmail) = $result;
		if (!$user->password->equals($this->_password)){
			throw new authException('Invalid password', authException::ERROR_PASSWORD_INVALID);
		}
		$this->_user = $user;
		$this->_identityModel = $userEmail;
	}
	
	/**
	 * @return mixed a value that uniquely represents the identity
	 */
	public function getId(){
		return $this->_email;
	}
	/**
	 * @return string display name
	 */
	public function getName(){
		return $this->getId();
	}
}