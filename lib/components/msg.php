<?php

/**
 * @file
 *
 * @brief 
 * 
 * 消息管理组件
 *
 */

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class MsgComponent extends CMsgComponent
{
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);
	}
		
	public function MsgComponent($name, $options=array())
	{
		$this->__construct($name, $options);
	}
}