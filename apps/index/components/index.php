<?php
/**
 * @file
 *
 * @brief 
 * วฐถหสืาณ
 *
 *
 * Copyright (c), 2014, relaxcms.com
 */

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class IndexComponent extends CFrontComponent
{
	function __construct($name, $options=null)
	{
		parent::__construct($name, $options);
	}
	
	function IndexComponent($name, $options=null)
	{
		$this->__construct($name, $options);	
	}
		
	
	public function show(&$ioparams=array())
	{
		$scf = Factory::GetSiteConfiguration();
		$this->assign("metakeyword", $scf['metakeyword']);
		$this->assign("_catalog_title", '');
		$this->assign("_content_title", '');
		$this->assign("_keyword", '');
		
	}
	
}

?>