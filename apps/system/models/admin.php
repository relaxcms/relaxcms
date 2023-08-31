<?php

/**
 * @file
 *
 * @brief 
 * 
 * 管理员模型
 *
 */

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class AdminModel extends CAdminModel
{
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);
	}
		
	public function AdminModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}
}