<?php

/**
 * @file
 *
 * @brief 
 * About us
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );


class HelpAboutComponent extends CUIComponent
{
	function __construct($name, $options=null)
	{
		parent::__construct($name, $options);
	}
	
	function HelpAboutComponent($name, $options=null)
	{
		$this->__construct($name, $options);
	}
	
	public function show(&$ioparams=array())
	{
		//welcome
		$aboutus = str_replace("\n", "<p>", s_read(RPATH_DOCUMENT.DS.'aboutus.txt'));
		
		
		$this->assign('aboutus', $aboutus);
	}
}