<?php
/**
 * @file
 *
 * @brief 
 * 前端首页
 *
 *
 * Copyright (c), 2014, relaxcms.com
 */

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class MainComponent extends CFrontComponent
{
	function __construct($name, $options=null)
	{
		parent::__construct($name, $options);
	}
	
	function MainComponent($name, $options=null)
	{
		$this->__construct($name, $options);	
	}	
	
	protected function show(&$ioparams=array())
	{
		parent::show($ioparams);

		$my = Factory::GetModel('my');
		$myinfo = $my->myInfo();
		$walletinfo = $my->myWalletInfo();

		if ($myinfo) {			
			$avator =$myinfo['avatar'];
			$myinfo['avatar'] = $avator?( is_url($avator)?$avator:$ioparams['_dataroot']."/avatar/$avator"):$ioparams['_dstroot']."/img/avatar.png";			
		}
		
		$this->assign('myinfo', $myinfo);
		$this->assign('walletinfo', $walletinfo);

		$this->resetTpl();
		return true;
	}
}