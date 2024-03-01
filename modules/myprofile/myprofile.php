<?php
/**
 * @file
 *
 * @brief 
 * 语言栏模块
 *
 */
class MyprofileModule extends CModule
{
	function __construct($name, $attribs)
	{
		parent::__construct($name, $attribs);
		$this->_attribs['task'] = 'show';
	}
	
	function MyprofileModule($name, $attribs)
	{
		$this->__construct($name, $attribs);
	}
		
	protected function show(&$ioparams=array())
	{
		//当前用户
		$_userinfo = get_userinfo();
		if ($_userinfo) {			
			$avator =$_userinfo['avatar'];
			$_userinfo['avatar'] = $avator?( is_url($avator)?$avator:$ioparams['_dataroot']."/avatar/$avator"):$ioparams['_dstroot']."/img/avatar.png";			
		}

		$has_my_info = hasPrivilegeOf('my_info');
		$has_my_password = hasPrivilegeOf('my_password');
		
		$this->assign('_userinfo', $_userinfo);
		$this->assign('has_my_info', $has_my_info);
		$this->assign('has_my_password', $has_my_password);


	}	
}