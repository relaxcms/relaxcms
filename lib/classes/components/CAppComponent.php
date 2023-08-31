<?php

/**
 * @file
 *
 * @brief 
 *  基本应用管理类,实现应用安装,卸载
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class CAppComponent extends CDTFileComponent
{
	protected $_cacheappfile = null;
	
	function __construct($name, $options=null)
	{
		parent::__construct($name, $options);
	}
	
	function CAppComponent($name, $options=null)
	{
		$this->__construct($name, $options);
	}
	
	
	protected function show(&$ioparams=array())
	{
		parent::show($ioparams);
		$this->resetTpl();		
	}		
}