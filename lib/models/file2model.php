<?php

/**
 * @file
 *
 * @brief 
 * 
 * 默认文件模型
 *
 */

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class File2modelModel extends CFile2modelModel
{
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);
	}
		
	public function File2modelModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}
}