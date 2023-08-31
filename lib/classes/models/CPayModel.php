<?php

/**
 * @file
 *
 * @brief 
 *
 * 支付模型
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class CPayModel  extends CModel
{
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);
	}
	
	public function CPayModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}

	//订单支付成功
	public function payNotifyModel($orderinfo)
	{
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, 'TODO...');
		return false;
	}

	//退单退费
	public function unpayNotifyModel($orderinfo)
	{
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, 'TODO...');
		return false;
	}

}