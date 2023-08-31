<?php
/**
 * @file
 *
 * @brief 
 * 内容页插件
 *
 * Copyright (c), 2014, relaxcms.com
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class ContentModule extends CModule
{
	function __construct($name, $attribs)
	{
		parent::__construct($name, $attribs);
	}
	
	function ContentModule($name, $attribs)
	{
		$this->__construct($name, $attribs);
	}
	
	protected function show(&$ioparams=array())
	{
		$tid = $this->_attribs['tid'];
		$cid = $this->_attribs['cid'];
		
		$tpl = isset($this->_attribs['tpl'])?$this->_attribs['tpl']:'content';
		$miw = isset($this->_attribs['miw'])?$this->_attribs['miw']:'650';
		$this->_attribs['miw'] = $miw;
		
		$sc = isset($this->_attribs['sc'])?$this->_attribs['sc']:'';
		$sh = isset($this->_attribs['sh'])?$this->_attribs['sh']:'';
		$sr = isset($this->_attribs['sr'])?$this->_attribs['sr']:'';
		
		$ha = isset($this->_attribs['ha'])?$this->_attribs['ha']:false;
		
		$m = Factory::GetModel('content');
		$view = $m->getForFrontend($tid, $_fields, $ioparams);
		$view['_ts'] = tformat($view['ts'], 'Y-m-d H:i');
		$view['share'] = is_var_mask(6, $view['status'])?true:false;
		
		$m2 = Factory::GetModel('catalog');		
		$scf = Factory::GetSiteConfiguration();
		
		$this->set_var('view', $view);
		
		$this->set_var('scf', $scf);
		$this->set_var('ha', $ha);
		$this->set_var('sc', $sc);
		$this->set_var('sh', $sh);
		$this->set_var('sr', $sr);
		
		$position = $m2->nav($cid, $ioparams);
		$this->set_var('position', $position);
		
		
		
		$this->_template = $tpl;
		return true;
	}	
}
