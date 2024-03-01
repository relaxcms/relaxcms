<?php
/**
 * @file
 *
 * @brief 
 *  用户管理组件
 *
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );
class CUserComponent extends CDTComponent
{
	function __construct($name, $options)
	{
		parent::__construct($name, $options);
	}
	
	function CUserComponent($name, $options)
	{
		$this->__construct($name, $options);
	}
	
	protected function resetpassword(&$ioparams=array())
	{
		$m = Factory::GetModel('user');
		$res = $m->resetPassword($this->_id, $ioparams);
		showStatus($res?0:-1);
	}
	
}