<?php

/**
 * @file
 *
 * @brief 
 * 
 *
 */

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class SystemConfig extends CSystemConfig
{
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);
	}
		
	public function SystemConfig($name, $options=array())
	{
		$this->__construct($name, $options);
	}
}