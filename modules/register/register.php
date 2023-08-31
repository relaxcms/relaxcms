<?php
/**
 * @file
 *
 * @brief 
 * 登录模块
 *
 */
class RegisterModule extends CLoginModule
{
	function __construct($name, $attribs)
	{
		parent::__construct($name, $attribs);
	}
	
	function RegisterModule($name, $attribs)
	{
		$this->__construct($name, $attribs);
	}	
	
	protected function show(&$ioparams=array())
	{		
		$this->assign('pkey', $this->_attribs['__aeskey']);
		
		//验证码超时
		$cf = get_config();
		$seccodetimeout = isset($cf['seccodetimeout'])?$cf['seccodetimeout']:5;
		$this->assign('seccodetimeout', $seccodetimeout);
		
		$params = $_REQUEST;
		
		$this->assign('params', $params);
	}	
}