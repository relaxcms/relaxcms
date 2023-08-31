<?php

/**
 * @file
 *
 * @brief 
 * 
 * License 模型
 *
 */

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );
class LicenseModel extends CLicenseModel
{
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);		
	}
		
	public function LicenseModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}
}