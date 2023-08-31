<?php
/**
 * @file
 *
 * @brief 
 * 
 * 模块参数管理
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class Module_paramsModel extends CModuleParamsModel
{
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);
	}
		
	public function Module_paramsModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}	
	
}
