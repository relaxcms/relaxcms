<?php

/**
 * @file
 *
 * @brief 
 *  操作日志
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class SystemLogComponent extends CLogComponent
{
	function __construct($name, $options)
	{
		parent::__construct($name, $options);
	}
	
	function SystemLogComponent($name, $options)
	{
		$this->__construct($name, $options);
	}
	
	protected function detail(&$ioparams=array())
	{
		$this->enableMenuItem('edit', false);
		parent::detail($ioparams);
	}
}
