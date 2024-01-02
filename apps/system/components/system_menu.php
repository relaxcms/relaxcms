<?php

/**
 * @file
 *
 * @brief 
 *  菜单定义
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class SystemMenuComponent extends CDTComponent
{
	function __construct($name, $options=array())
	{
		parent::__construct($name, $options);
	}
	
	function SystemMenuComponent($name, $options=array())
	{
		$this->__construct($name, $options);
	}
	
	protected function show(&$ioparams=array())
	{
		$m = Factory::GetModel($this->_modname);		
		if ($this->_sbt) {
			$this->getParams($params);
			$res = $m->set($params);		
			showStatus($res?0:-1);
		}

		$this->enableJSCSS('treegrid');

		$app = Factory::GetApp();
		$menus = $app->getCurrentMenuTree();
		foreach ($menus as $key => &$v) {
			setchecked('open', $v);
		}

		$this->assign('menus', $menus);
		
		return $res;
	}

	protected function reset(&$ioparams=array())
	{
		$m = Factory::GetModel($this->_modname);
		$res = $m->reset();	
		showStatus($res?0:-1);
	}	
}