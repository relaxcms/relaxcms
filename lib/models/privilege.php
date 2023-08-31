<?php

/**
 * @file
 *
 * @brief 
 * 
 * 权限管理类
 *
 */

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );
class PrivilegeModel extends CPrivilegeModel
{
	
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);
	}
	
	public function PrivilegeModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}
}