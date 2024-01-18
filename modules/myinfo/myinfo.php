<?php
/**
 * @file
 *
 * @brief 
 * 个人信息模块
 *
 */
class MyinfoModule extends CModule
{
	function __construct($name, $attribs)
	{
		parent::__construct($name, $attribs);
		$this->_attribs['task'] = 'show';
	}
	
	function MyinfoModule($name, $attribs)
	{
		$this->__construct($name, $attribs);
	}
		
	protected function show(&$ioparams=array())
	{
		$myinfo = get_userinfo();
		if ($myinfo) {			
			$avator =$myinfo['avatar'];
			$myinfo['avatar'] = $avator?( is_url($avator)?$avator:$ioparams['_dataroot']."/avatar/$avator"):$ioparams['_dstroot']."/img/avatar.png";			
		}
		
		$this->assign('myinfo', $myinfo);

	}	
}