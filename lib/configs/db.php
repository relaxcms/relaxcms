<?php

/**
 * @file
 *
 * @brief 
 * 
 *
 */

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class DbConfig extends CDBConfig
{
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);
	}
		
	public function DbConfig($name, $options=array())
	{
		$this->__construct($name, $options);
	}
}