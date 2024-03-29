<?php

/**
 * @file
 *
 * @brief 
 * 
 * my 模型
 *
 */

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class MyModel extends CUserModel
{
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);
	}
		
	public function MyModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}

	public function myWalletInfo()
	{
		return $this->getWalletInfo(get_uid());
	}
	
	public function myInfo()
	{
		//当前用户
		$myinfo = get_userinfo();
		if (!$myinfo) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "no login!");
			return false;
		}

		$myinfo['name'] = s_hidestr($myinfo['name'], 4,4);
		return $myinfo;
	}



}