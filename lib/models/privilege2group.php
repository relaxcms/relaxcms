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
class Privilege2groupModel extends CNoLogModel
{
	
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);
	}
	
	public function Privilege2groupModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}
	
}