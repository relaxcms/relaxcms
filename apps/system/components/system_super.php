<?php

/**
 * @file
 *
 * @brief 
 *  超级管理员
 *
 * Copyright (c), 2014, relaxcms.com
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class SystemSuperComponent extends CUIComponent
{
	function __construct($name, $options)
	{
		parent::__construct($name, $options);
	}
	
	function SystemSuperComponent($name, $options)
	{
		$this->__construct($name, $options);
	}
	
	public function show(&$ioparams=array())
	{	
		$this->enableJSCSS(array( 'crypto', 'encrypt'), true);
		
		//公key
		$pkey = md5(time());
		$this->assignSession('__aeskey', $pkey);
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, '$__aeskey='.$pkey);
		
		$this->assign('pkey', $pkey);		
		
			
		$userinfo = get_userinfo();
		$this->assign('newsupername', $userinfo['name']);		
	}
	
	
	protected function doChangeSuper(&$ioparams=array())
	{
		$userinfo = get_userinfo();
		$this->getParams($params);
		
		$password = $params['password'];
		$newpassword = $params['newpassword'];
		$newpassword2 = $params['newpassword2'];
		
		if (!$password) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no password!");
			return false;
		}
		
		if (!$newpassword ) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no newpassword!");
			return false;
		}
		
		if ($newpassword != $newpassword2) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "newpassword error!");
			return false;
		}
		
		//更新初始管理员	
		$m = Factory::GetAdmin();		
		$res = $m->changeSuper($params);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "change super failed!");
			return false;
		}
		
		return $res;
	}
	
	
	//编辑
	protected function changesuper(&$ioparams=array())
	{
		$res = $this->doChangeSuper($ioparams);
		showStatus($res?0:-1) ;
	}
}
