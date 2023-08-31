<?php
/**
 * @file
 *
 * @brief 
 *  登录基类
 *
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );
class CLogoutComponent extends CComponent
{
	function __construct($name, $options)
	{
		parent::__construct($name, $options);
	}
	
	protected function init(&$ioparams = array())
	{
		$ioparams['task'] = 'logout';			
	}
	
	public function logout(&$ioparams = array())
	{
		Factory::GetApp()->logout();		
		redirect($ioparams['_basename']);
	}
}