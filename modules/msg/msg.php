<?php
/**
 * @file
 *
 * @brief 
 * Msg 模块
 *
 */
class MsgModule extends CModule
{
	function __construct($name, $attribs)
	{
		parent::__construct($name, $attribs);
		$this->_attribs['task'] = 'show';
	}
	
	function MsgModule($name, $attribs)
	{
		$this->__construct($name, $attribs);
	}
	
	protected function show(&$ioparams=array())
	{
		$m = Factory::GetModel('msg');		
		
		$udb = $m->getMyList(array('status'=>0), $ioparams);
		
		$total = $ioparams['total'];
		
		
		$this->assign('udb', $udb);
		$this->assign('total', $total);
		
		return true;
	}	
}