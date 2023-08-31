<?php

/**
 * @file
 *
 * @brief 
 * 
 * 默变量模型
 *
 */

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class VarModel extends CVarModel
{
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);
	}
		
	public function VarModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}
}