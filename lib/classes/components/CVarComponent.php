<?php
/**
 * @file
 *
 * @brief 
 *  变量管理
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );
class CVarComponent extends CTreeDTComponent
{
	function __construct($name, $options)
	{
		parent::__construct($name, $options);
	}
	
	function CVarComponent($name, $options)
	{
		$this->__construct($name, $options);
	}
	
	protected function _init()
	{
		$this->_modname = 'var';
		$this->_default_vmask = 4;		
	}
	
	
	protected function cache(&$ioparams=array())
	{
		$m = Factory::GetModel('var');
		$res = $m->cache();
		showStatus($res?0:-1);
	}
	
}