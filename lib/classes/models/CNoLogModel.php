<?php

/**
 * @file
 *
 * @brief 
 * 
 * 不记录日志模型
 *
 */

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class CNoLogModel extends CModel
{
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);
	}
	
	public function CNoLogModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}	
	
	protected function writeLog($level, $action, $status, $oldParams=array(), $newParams=array(), $mid=0)
	{
		return false;
	}
}
