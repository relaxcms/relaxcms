<?php

defined( 'RMAGIC' ) or die( 'Restricted access' );

class CMainComponent extends CDTComponent
{
	function __construct($name, $option)
	{
		parent::__construct($name, $option);
	}
	
	function CMainComponent($name, $option)
	{
		$this->__construct($name, $option);
	}	


	protected function show(&$ioparams=array())
	{
		parent::show($ioparams);
		//当前用户
		$myinfo = get_userinfo();
		if ($myinfo) {			
			$avator =$myinfo['avatar'];
			$myinfo['avatar'] = $avator?( is_url($avator)?$avator:$ioparams['_dataroot']."/avatar/$avator"):$ioparams['_dstroot']."/img/avatar.png";			
		}
		
		$this->assign('myinfo', $myinfo);

		return true;
	}
}
