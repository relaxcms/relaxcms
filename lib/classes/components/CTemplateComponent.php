<?php

/**
 * @file
 *
 * @brief 
 *  消息
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class CTemplateComponent extends CTreeDTComponent
{
	function __construct($name, $options)
	{
		parent::__construct($name, $options);
	}
	
	function CTemplateComponent($name, $options)
	{
		$this->__construct($name, $options);
	}
	
	protected function _init()
	{
		$this->_default_vmask = 5;
		$this->_default_viewtype = 1;
		$this->enableMenuItem('add,edit,del', false);
		
		$this->addMenuItem(array(
			'name'=>'install',
			'icon'=>'fa fa-cogs',
			'title'=>'安装',
			'action'=>'submit',
			'sort'=>10,
			'enable'=>true,
			'class'=>'btn-danger',
			'msg'=>'确认安装吗？',
			'tmask'=>array('show'),
			));
	}
	
	
	protected function install(&$ioparams=array())
	{
		$m = $this->getModel();		
		$res = $m->setup($this->_id, $ioparams);		
		showStatus($res?0:-1);
	}
	
	//detail
	protected function detail(&$ioparams=array())
	{
		$this->setActiveTab(3);
		$res = parent::detail($ioparams);
		$this->setTpl('site_template_detail');
	}
}
