<?php

/**
 * @file
 *
 * @brief 
 * 
 * 会话表管理
 *
 */

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );
class CSessionModel extends CTableModel
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