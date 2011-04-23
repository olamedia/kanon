<?php
class authException extends Exception{
	const ERROR_USERNAME_INVALID = 1;
	const ERROR_EMAIL_INVALID = 2;
	const ERROR_PASSWORD_INVALID = 3;
	const ERROR_NOT_REGISTERED = 4;
}