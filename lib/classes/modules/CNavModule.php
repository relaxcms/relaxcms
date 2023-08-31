<?php

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class CNavModule extends CModule
{
	function __construct($name, $attribs)
	{
		parent::__construct($name, $attribs);
		$this->_attribs['task'] = 'show';
	}
	
	function CNavModule($name, $attribs)
	{
		$this->__construct($name, $attribs);
	}

	protected function show(&$ioparams = array())
	{
		$flags = isset($this->_attribs['flags'])?intval($this->_attribs['flags']):0;
		$cid = isset($this->_attribs['cid'])?$this->_attribs['cid']:0;
		$tree = isset($this->_attribs['tree'])?true:0;
		
		$m = Factory::GetModel('catalog');
		$menu = $m->menu(0, $tree, $flags);
		
		foreach($menu as $key=>&$v) {
			if ($v['id'] == $cid) {
				$v['active'] = 'open';
			} else {
				$v['active'] = '';
			}
		}
		
		$this->set_var('udb', $menu);	
		return true;
	}	
}