<?php

/**
 * @file
 *
 * @brief 
 * 
 * 默认管理员组件
 *
 */

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class AdminComponent extends CDTComponent
{
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);
	}
		
	public function AdminComponent($name, $options=array())
	{
		$this->__construct($name, $options);
	}
}