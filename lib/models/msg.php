<?php

/**
 * @file
 *
 * @brief 
 * 
 * 默认文件模型
 *
 */

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class MsgModel extends CMsgModel
{
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);
	}
		
	public function MsgModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}
}