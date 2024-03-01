<?php
/**
 * @file
 *
 * @brief 
 * 选文件
 *
 */
class SelectfileModule extends CListviewModule
{
	function __construct($name, $attribs)
	{
		parent::__construct($name, $attribs);
	}
	
	function SelectfileModule($name, $attribs)
	{
		$this->__construct($name, $attribs);
	}
	
	protected function getModel()
	{
		return isset($this->_attribs['modname'])?$this->_attribs['modname']:'file';
	}
	
	public function show(&$ioparams = array())
	{
		parent::show($ioparams);
		
		$cuid = isset($this->_attribs['cuid'])?intval($this->_attribs['cuid']):0;	
		$type = isset($this->_attribs['type'])?intval($this->_attribs['type']):'';	
		if ($type < 0)
			$type = '';
		$this->assign('nosidebar',"nosidebar");	
		$this->assign('type',$type);
	}
}
