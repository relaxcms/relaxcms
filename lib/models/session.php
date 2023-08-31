<?php

/**
 * @file
 *
 * @brief 
 * 
 * 会话模型
 *
 */

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class SessionModel extends CSessionModel
{
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);
	}
		
	public function SessionModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}
}