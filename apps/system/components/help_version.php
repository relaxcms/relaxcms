<?php

/**
 * @file
 *
 * @brief 
 * ÆðÊ¼Ò³
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );


class HelpVersionComponent extends CUIComponent
{
	function __construct($name, $options=null)
	{
		parent::__construct($name, $options);
	}
	
	function HelpVersionComponent($name, $options=null)
	{
		$this->__construct($name, $options);
	}

	
	public function show(&$ioparams=array())
	{		
		$changelog= s_read(RPATH_DOCUMENT.DS.'ChangeLog.txt');
		
		$changelog = str_replace("\n", "<br>", $changelog);		
		$this->assign("changelog", $changelog);		
		$this->assign('sys_product_id', get_product_id());
		$this->assign('sys_product_version', get_product_version());
		
	
	}
}