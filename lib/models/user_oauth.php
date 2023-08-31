<?php

/**
 * @file
 *
 * @brief 
 * 
 * 第三方用户模型
 *
 */

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class User_oauthModel extends CTableModel
{
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);
	}
		
	public function User_oauthModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}
	
	public function autoRegisterOAuth($params)
	{
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "IN");
		
		//生成user
		$m = Factory::GetModel('user');
		
		$_params = $params;
		$_params['nickname'] = isset($params['nickname'])?$params['nickname']:$params['name'];
		
		//自动注册一个本地用户
		$res = $m->autoRegister($_params);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "auto register failed!");
			return false;
		}
		$uid = $res;
				
		$params['uid'] = $uid;
		$res = $this->set($params);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "set user oauth failed!", $params);
			return false;
		}
		
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "OUT");
		return $params;
	}
}