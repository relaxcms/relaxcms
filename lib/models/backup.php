<?php

/**
 * @file
 *
 * @brief 
 * 
 * 备份
 *
 */

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );
class BackupModel extends CBackupModel
{
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);
	}
	
	public function BackupModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}
}