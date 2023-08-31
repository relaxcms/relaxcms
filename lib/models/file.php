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

class FileModel extends CFileModel
{
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);
	}
		
	public function FileModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}
}