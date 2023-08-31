<?php

/**
 * @file
 *
 * @brief 
 * 
 * 用户模型
 *
 */

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class UserModel extends CUserModel
{
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);
	}
		
	public function UserModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}
}