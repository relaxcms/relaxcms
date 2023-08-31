<?php

/**
 * @file
 *
 * @brief 
 *  操作日志
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class CLogComponent extends CDTComponent
{
	function __construct($name, $options)
	{
		parent::__construct($name, $options);
	}
	
	function CLogComponent($name, $options)
	{
		$this->__construct($name, $options);
	}
	
	protected function _init()
	{
		$this->_modname = 'log';
		$this->_default_vmask = 4;
	}

	protected function show(&$ioparams=array())
	{
		$this->_tmi_tools = array();
		
		parent::show($ioparams);
	}

}
