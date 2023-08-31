<?php

/**
 * @file
 *
 * @brief 
 * 
 * 默认用户模型
 *
 */

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class AdminModel extends CAdminModel
{
	protected $_roledb = array();
	
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);
	}
		
	public function AdminModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}
}