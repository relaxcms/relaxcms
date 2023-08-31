<?php

/**
 * @file
 *
 * @brief 
 *  系统配置
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class CParamsComponent extends CUIComponent
{
	function __construct($name, $options)
	{
		parent::__construct($name, $options);
	}
	
	function CParamsComponent($name, $options)
	{
		$this->__construct($name, $options);
	}
		
	protected function show(&$ioparams = array())
	{
		$m = Factory::GetModel($this->_modname);		
		if ($this->_sbt) {
			$this->getParams($params);
			$res = $m->set($params);		
			showStatus($res?0:-1);
		}
		
		$params = $m->get(0);				
		$fields =$m->getFieldsforInput($params, $ioparams);
		
		$this->assign('fields', $fields);		
		$this->setTpl('params');
		
		return $params;
	}
	
}

?>