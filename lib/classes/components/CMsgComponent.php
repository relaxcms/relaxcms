<?php

/**
 * @file
 *
 * @brief 
 *  消息
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class CMsgComponent extends CDTComponent
{
	function __construct($name, $options)
	{
		parent::__construct($name, $options);
	}
	
	function CMsgComponent($name, $options)
	{
		$this->__construct($name, $options);
	}
	
	protected function _init()
	{
		$this->_modname = 'msg';
		$this->_default_vmask = 4;
	}
}
