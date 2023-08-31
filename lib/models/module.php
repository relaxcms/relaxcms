<?php

/**
 * @file
 *
 * @brief 
 * 
 * 模块
 *
 */

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class ModuleModel extends CModuleModel
{
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);
	}
		
	public function ModuleModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}
}