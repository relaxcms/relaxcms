<?php

/**
 * @file
 *
 * @brief 
 * 
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class OtherOAuth extends COAuth
{
	
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);
	}
	
	public function OtherOAuth($name, $options=array())
	{
		$this->__construct($name, $options);
	}
}