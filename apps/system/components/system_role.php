<?php

/**
 * @file
 *
 * @brief 
 *  用户角色管理
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class SystemRoleComponent extends CRoleComponent
{
	function __construct($name, $options)
	{
		parent::__construct($name, $options);		
	}
	
	function SystemRoleComponent($name, $options)
	{
		$this->__construct($name, $options);
	}
}