<?php

class emailUserIdentity extends userIdentityPrototype{
    protected $_email = '';
    protected $_password = ''; // @todo convert to hash, ex: md5($password)
    protected $_isRegistered = null;
    public function isRegistered(){
        if ($this->_isRegistered === null){
            $users = user::getCollection(); //modelCollection::getInstance('registeredUser');
            $emails = modelCollection::getInstance('userEmail');
            $result = $users->select($emails, $emails->email->is($this->_email))->fetch();
            if ($result){
                $this->_isRegistered = true;
            }else{
                $this->_isRegistered = false;
            }
        }
        return $this->_isRegistered;
    }
    public function __construct($email, $password){
        $this->_email = $email;
        $this->_password = $password;
    }
    public function getModel(){
        $emails = modelCollection::getInstance('userEmail');
        return $emails->select($emails->email->is($this->_email))->fetch();
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
        $this->_isRegistered = true;
        if (!$user->password->equals($this->_password)){
            throw new authException('Invalid password ("'.$user->password->getInternalValue().'" != "'.$user->password->getHash($this->_password).'")', authException::ERROR_PASSWORD_INVALID);
        }
        $this->_user = $user;
        $this->_identityModels['email'][$this->_email] = $userEmail;
    }
    /**
     * @return boolean whether authentication succeeds
     */
    public function exists(){
        $result = $this->getModel();
        if (!$result){
            return false;
        }
        return true;
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
    public function register(){
        $userEmail = new userEmail();
        $userEmail->userId = $this->_user->id;
        $userEmail->email = $this->_email;
        $userEmail->save();
    }
}