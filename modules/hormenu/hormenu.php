<?php
/**
 * @file
 *
 * @brief 
 *
 * Copyright (c), 2014, relaxcms.com
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class HormenuModule extends CNavModule
{
	function __construct($name, $attribs)
	{
		parent::__construct($name, $attribs);
	}
	
	function HormenuModule($name, $attribs)
	{
		$this->__construct($name, $attribs);
	}
	
	
	protected function buildSubHorMenu($submenu, $ioparams)
	{
		$_hormenubar = "<ul class='dropdown-menu '>";
		foreach ($submenu as $key=>$v) {
			$url = ($v['linkurl'])?$v['linkurl']:$ioparams['_webroot'].'/list/'.$v['id'];
			
			$hasSubmenu = $v['submenu']?true:false;
			
			$class=$hasSubmenu?'dropdown-submenu':'';
			$_hormenubar .= "<li class='$class'>";
			$_hormenubar .= "<a href='$url'>$v[name]</a>";
			
			if ($hasSubmenu) {
				$_hormenubar .= $this->buildSubHorMenu($v['submenu'], $ioparams);
			}
			
			$_hormenubar .= "</li>";
		}
		
		$_hormenubar .= "</ul>";
		
		return $_hormenubar;		
	}
	
	
	
	protected function buildHorMenuBar($menu, $ioparams)
	{
		$_hormenubar = '';
		foreach ($menu as $key=>$v) {
			$url = ($v['linkurl'])?$v['linkurl']:$ioparams['_webroot'].'/list/'.$v['id'];
			$_hormenubar .= "<li aria-haspopup='true' class='menu-dropdown mega-menu-dropdown  '>";
			$_hormenubar .= "<a href='$url'>$v[name]</a>";
			if ($v['submenu']) {
				$_hormenubar .= $this->buildSubHorMenu($v['submenu'], $ioparams);
			}
			
			$_hormenubar .= "</li>";
		}		
		
		return $_hormenubar;
	}	
		
	protected function show(&$ioparams = array())
	{
		$m = Factory::GetModel('catalog');
		$menu = $m->menu(0, true, 0);
		
		
		$hormenubar = $this->buildHorMenuBar($menu, $ioparams);
		
		
		$this->set_var('hormenubar', $hormenubar);	
		
		return true;
	}	
}

?>