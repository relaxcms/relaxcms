<?php

/**
 * @file
 *
 * @brief 
 *  用户组
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class CGroupComponent extends CDTComponent
{
	function __construct($name, $options)
	{
		parent::__construct($name, $options);
	}
	
	function CGroupComponent($name, $options)
	{
		$this->__construct($name, $options);
	}
	
	protected function _init()
	{
		parent::_init();
		$this->_modname = 'group';
	}	
}

?>