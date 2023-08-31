<?php
/**
 * @file
 *
 * @brief 
 * 前端首页
 *
 *
 * Copyright (c), 2014, relaxcms.com
 */

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class MainComponent extends CMainComponent
{
	function __construct($name, $options=null)
	{
		parent::__construct($name, $options);
	}
	
	function MainComponent($name, $options=null)
	{
		$this->__construct($name, $options);	
	}	
	
}