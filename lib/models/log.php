<?php

/**
 * @file
 *
 * @brief 
 * 
 * 日志模型
 *
 */

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );
class LogModel extends CLogModel
{
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);		
	}
		
	public function LogModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}
}