<?php

/**
 * @file
 *
 * @brief 
 * 
 * 表类模型
 *
 */

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class CTableModel extends CModel
{
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);
	}
	
	public function CTableModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}	
}
