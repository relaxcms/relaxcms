<?php

/**
 * @file
 *
 * @brief 
 * 
 * 角色管理类
 *
 */

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );
class RoleModel extends CRoleModel
{
	
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);
	}
	
	public function RoleModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}
}