<?php
/**
 * @file
 *
 * @brief 
 * 登录模块
 *
 */
class CLoginModule extends CModule
{
	function __construct($name, $attribs)
	{
		parent::__construct($name, $attribs);
	}
	
	function CLoginModule($name, $attribs)
	{
		$this->__construct($name, $attribs);
	}
		
	protected function show(&$ioparams=array())
	{		
		$this->assign('pkey', $this->_attribs['__aeskey']);
		
		$m = Factory::GetModel('oauth');
		
		$params = array('status'=>1,'auth'=>1);
		$udb = $m->selectForView($params, $ioparams);	

		$this->assign('thirdAccountLoginOptions', $udb);
		
		//验证码超时
		$cf = get_config();
		$seccodetimeout = isset($cf['seccodetimeout'])?$cf['seccodetimeout']:5;
		$this->assign('seccodetimeout', $seccodetimeout);
		
		return true;
	}	
}