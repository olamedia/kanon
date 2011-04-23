<?php
interface IUserIdentity{
	/**
	 * @return boolean whether authentication succeeds
	 */
	public function authenticate();
	/**
	 * @return boolean whether the identity is valid
	 */
	public function isAuthenticated();
	/**
	 * @return mixed a value that uniquely represents the identity
	 */
	public function getId();
	/**
	 * @return string display name
	 */
	public function getName();
}