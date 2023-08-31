<?php

/**
 * @file
 *
 * @brief 
 *  自助修改密码
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class MyPasswordComponent extends CMyPasswordComponent
{
	function __construct($name, $options)
	{
		parent::__construct($name, $options);
	}
	
	function MyPasswordComponent($name, $options)
	{
		$this->__construct($name, $options);
	}
	
	
}
