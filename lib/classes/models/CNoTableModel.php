<?php

/**
 * @file
 *
 * @brief 
 * 
 * �Ǳ���ģ��
 *
 */

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class CNoTableModel extends CDataModel
{
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);
	}
	
	public function CNoTableModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}	
}
