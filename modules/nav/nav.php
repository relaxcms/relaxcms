<?php
/**
 * @file
 *
 * @brief 
 *
 * Copyright (c), 2014, relaxcms.com
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class NavModule extends CNavModule
{
	function __construct($name, $attribs)
	{
		parent::__construct($name, $attribs);
	}
	
	function NavModule($name, $attribs)
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
		$tpl = isset($this->_attribs['tpl'])?$this->_attribs['tpl']:'nav';
		$split = isset($this->_attribs['split'])?$this->_attribs['split']:'';
		$class = isset($this->_attribs['class'])?$this->_attribs['class']:'';
		$flags = isset($this->_attribs['flags'])?intval($this->_attribs['flags']):3;
		$cid = isset($this->_attribs['cid'])?$this->_attribs['cid']:false;
		$tree = isset($this->_attribs['tree'])?true:0;
		
		$m = Factory::GetModel('catalog');
		$menu = $m->menu(0, $tree, $flags);
		
		$hormenubar = '';
		if ($tree) {
			$hormenubar = $this->buildHorMenuBar($menu, $ioparams);
		}
		
		$homepage = array(
			'active'=>'open',
			'class'=>'dropdown-toggle',
			'icon'=>'fa-home',
		);
		
		foreach($menu as $key=>&$v) {
			if ($v['id'] == $cid) {
				$v['active'] = 'open';
				$homepage['active'] = '';
			} else {
				$v['active'] = '';
			}
		}
		
		$this->set_var('homepage', $homepage);	
		$this->set_var('udb', $menu);	
		$this->set_var('split', $split);	
		$this->set_var('class', $class);	
		$this->set_var('hormenubar', $hormenubar);	
		
		$this->_template = $tpl;
				
		return true;
	}	
}

?>