<?php
/**
 * @file
 *
 * @brief 
 *  个人组件
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );
class CMyDTComponent extends CDTFileComponent
{
	function __construct($name, $options)
	{
		parent::__construct($name, $options);
	}
	
	function CMyDTComponent($name, $options)
	{
		$this->__construct($name, $options);
	}
	
	protected function _init()
	{
		$this->disableMenuItemAll();
	}
	
}