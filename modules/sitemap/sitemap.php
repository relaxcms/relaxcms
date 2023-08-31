<?php
/**
 * @file
 *
 * @brief 
 * 站点地图模块
 *
 */
class SitemapModule extends CMenuModule
{
	function __construct($name, $attribs)
	{
		parent::__construct($name, $attribs);
	}
	
	function SitemapModule($name, $attribs)
	{
		$this->__construct($name, $attribs);
	}	
	
	protected function show(&$ioparams=array())
	{
		$app = Factory::GetApp();
		$activeComponent = $ioparams['component'];
		$menus = $app->getCurrentMenuTree($activeComponent);
		
		
		$mdb = is_array($menus)?$menus:array();
		
		$sitemap = array();
		
		$this->assign('mdb', $mdb);
		$this->assign('sitemap', $sitemap);
	}
}