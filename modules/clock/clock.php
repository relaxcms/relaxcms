<?php
/**
 * @file
 *
 * @brief 
 *
 */
class ClockModule extends CModule
{
	function __construct($name, $attribs)
	{
		parent::__construct($name, $attribs);
	}
	
	function ClockModule($name, $attribs)
	{
		$this->__construct($name, $attribs);
	}
			
	protected function show(&$ioparams = array())
	{	
		$cf = get_config();
		$this->assign('system_datatime', tformat_cstdatetime(time()));
		$ajaxsystime = isset($cf['ajaxsystime'])?$cf['ajaxsystime']:0;
		$this->assign('ajaxsystime', $ajaxsystime);
		
		return true;		
	}
	
}