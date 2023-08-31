<?php
/**
 * @file
 *
 * @brief 
 * 选文件
 *
 */
class SelectfileModule extends CModule
{
	function __construct($name, $attribs)
	{
		parent::__construct($name, $attribs);
	}
	
	function SelectfileModule($name, $attribs)
	{
		$this->__construct($name, $attribs);
	}
	

	protected function show(&$ioparams = array())
	{
		$cuid = isset($this->_attribs['cuid'])?intval($this->_attribs['cuid']):0;	
		$type = isset($this->_attribs['type'])?intval($this->_attribs['type']):-1;	
		$this->assign('nosidebar',"nosidebar");	
		$this->assign('type',$type);	
	}
}
