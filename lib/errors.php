<?php

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

define( 'RC_E_OK',				0); 
define( 'RC_E_FAILED', 			-1); 
define( 'RC_E_BUSY', 			-2); 
define( 'RC_E_NODATA', 			-3); 
define( 'RC_E_INVALID_PASSWORD',	-10001); 
define( 'RC_E_INVALID_SBT', 		-10002); 
define( 'RC_E_INVALID_CAPTCHA',		-10003); 
define( 'RC_E_INVALID_IP',			-10004); 
define( 'RC_E_INVALID_USER',		-10005); 
define( 'RC_E_LOGIN_LOCKED',		-10006); 
define( 'RC_E_LOGIN_FORBIDDEN',		-10007); 

define( 'RC_E_USERNAME_INVALID',	-10008); 
define( 'RC_E_USERNAME_EXISTS',		-10009); 
define( 'RC_E_SECCODE_INVALID',		-10010); 
define( 'RC_E_ACCOUNT_INVALID',		-10011); 


function get_error_string($errno, $defmsg='')
{
	$sk = 'status_'.$errno;
	$res =  i18n($sk, $defmsg);
	
	//rlog($res);
	
	return $res;
}