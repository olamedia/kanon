<?php
$name = 'auth';
$title = 'Authentication & Authorization';
$description = 'Authentication & Authorization';
$autoload = array(
'authException' => 'authException.php',
'user' => 'classes/user.php',
'registeredUser' => 'models/registeredUser.php',
'userIdentityPrototype' => 'classes/userIdentityPrototype.php',

'openidUserIdentity' => 'openidIdentity/openidUserIdentity.php',
'userOpenid' => 'openidIdentity/userOpenid.php',
'LightOpenID' => 'openidIdentity/vendor/LightOpenID.php',

'loginUserIdentity' => 'loginIdentity/loginUserIdentity.php',
'userLogin' => 'loginIdentity/userLogin.php',

'emailUserIdentity' => 'emailIdentity/emailUserIdentity.php',
'userEmail' => 'emailIdentity/userEmail.php',
);

modelStorage::getInstance($name)
	->registerCollection('userLogin', 'user_login')
	->registerCollection('userEmail', 'user_email')
	->registerCollection('userOpenid', 'user_openid')
	->registerCollection('registeredUser', 'user')
	;
