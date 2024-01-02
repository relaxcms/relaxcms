<?php

/***
 * 
 * @file
 *
 * @brief 
 *  
 * */

defined( 'RMAGIC' ) or die( 'Restricted access' );

class CTreeDTComponent extends CDTFileComponent
{
	function __construct($name, $options=null)
	{
		parent::__construct($name, $options);
	}
	
	function CTreeDTComponent($name, $options=null)
	{
		$this->__construct($name, $options);
	}
	
	protected function enableTreeview()
	{
		$this->assign('treeview', 1);
	}
	
	protected function show(&$ioparams=array()) 
	{
		$res = parent::show($ioparams);
		$this->enableTreeview();	
		return $res;	
	}
	
	protected function initParamsForAdd(&$params, &$ioparams=array())
	{
		if ($this->_id > 0)
			$params['pid'] = $this->_id;
	}
	
}