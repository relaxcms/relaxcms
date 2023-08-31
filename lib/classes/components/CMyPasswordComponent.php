<?php
/**
 * @file
 *
 * @brief 
 *  个人密码管理
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );
class CMyPasswordComponent extends CUIComponent
{
	function __construct($name, $options)
	{
		parent::__construct($name, $options);
	}
	
	function CMyPasswordComponent($name, $options)
	{
		$this->__construct($name, $options);
	}
	
	public function show(&$ioparams = array())
	{
		$this->enableJSCSS(array( 'crypto', 'encrypt'), true);
		
		//公key
		$pkey = md5(time());
		$this->assignSession('__aeskey', $pkey);
		
		$this->assign('pkey', $pkey);		
		
	}
	
	protected function doPassword(&$ioparams=array())
	{
		$userinfo = get_userinfo();
				
		$params = array();
		$this->getParams($params);
		$password = $params['password'];
		$newpassword = $params['newpassword'];
		$newpassword2 = $params['newpassword2'];
		
		if (!$password) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "str_user_password_not_empty");
			return false;
		}
		
		if (!$newpassword ) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "str_user_newpassword_not_empty");
			return false;
		}
		
		if ($newpassword != $newpassword2) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "str_user_newpassword_error");
			return false;
		}
		
		$m = Factory::GetUser();
		$res = $m->changePassword($userinfo['id'], $password, $newpassword2);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "change user '{$userinfo['name']}' failed!");
			return false;
		}
		
		return $res;
	}
	
	protected function password(&$ioparams=array())
	{
		$res = $this->doPassword($ioparams);
		showStatus($res?0:-1) ;
		return $res;
	}
}