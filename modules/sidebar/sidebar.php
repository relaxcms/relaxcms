<?php
/**
 * @file
 *
 * @brief 
 * 菜单模块
 *
 */
class SidebarModule extends CMenuModule
{
	function __construct($name, $attribs)
	{
		parent::__construct($name, $attribs);
	}
	
	function SidebarModule($name, $attribs)
	{
		$this->__construct($name, $attribs);
	}	
	
	protected function show(&$ioparams = array())
	{
		//$ioparams['use_default_component'] = true;
		$menus = parent::show($ioparams);

		//默认展开菜单
		if (isset($ioparams['use_default_component']) && $ioparams['use_default_component']) {
			//找出默认展开菜单
			foreach ($menus as $key => &$v) {
				if ($v['open']) {
					$v['active'] = true;
				} else if ($v['active']) {
					$v['active'] = false;
				}
			}

			$this->assign('menus', $menus);
		}		
	}
	
	
}