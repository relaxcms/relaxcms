<?php
/**
 * @file
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class MainmenuModule extends CModule
{
	function __construct($name, $attribs)
	{
		parent::__construct($name, $attribs);
	}
	
	function MainmenuModule($name, $attribs)
	{
		$this->__construct($name, $attribs);
	}
	
	protected function show(&$ioparams = array())
	{
		$cid = (isset($this->_attribs['cid']))?$this->_attribs['cid']:0;
		$class = isset($this->_attribs['class'])?$this->_attribs['class']:'';
		$tpl = isset($this->_attribs['tpl'])?$this->_attribs['tpl']:'';
		
		$m = Factory::GetModel('catalog');
		$menu = $m->getMainmenu(4);


		
		
		$homepage = array(
			'active'=>'open active',
			'class'=>'dropdown-toggle',
			'icon'=>'fa-home',
		);
		
		foreach($menu as $key=>&$v) {
			$m->formatForView($v, $ioparams);

			if ($v['id'] == $cid) {
				$v['active'] = 'open active';
				$homepage['active'] = '';
			} else {
				$v['active'] = '';
			}
		}
		
		$this->set_var('homepage', $homepage);	
		$this->set_var('udb', $menu);	

		if ($tpl)
			$this->_template = $tpl;
				
		return true;
	}	
}