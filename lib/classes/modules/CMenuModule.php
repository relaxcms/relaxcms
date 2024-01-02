<?php

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class CMenuModule extends CModule
{
	function __construct($name, $attribs)
	{
		parent::__construct($name, $attribs);
		$this->_attribs['task'] = 'show';
	}
	
	function CMenuModule($name, $attribs)
	{
		$this->__construct($name, $attribs);
	}

	protected function show(&$ioparams = array())
	{
		$app = Factory::GetApp();
		$activeComponent = $ioparams['component'];
		$menus = $app->getCurrentMenuTree($activeComponent);
		$naviId = $activeComponent;
		if ($naviId == 'main') {
			$naviId = '';
		}
		
		
		$_menus = array();
		foreach ($menus as $key=>$v) {			
			if ($v['childen']) {
				$_menus[$key] = $v;
			}
		}

		$this->assign("menus", $_menus);
		$this->assign('naviId', $naviId);
		
				
		return $_menus;
	}	
}