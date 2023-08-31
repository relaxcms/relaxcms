<?php

/**
 * @file
 *
 * @brief 
 * 
 * 组管理类
 *
 */

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );
class GroupModel extends CGroupModel
{
	
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);
	}
	
	public function GroupModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}
}