<?php
/**
 * @file
 *
 * @brief 
 * 文件视图
 *
 */
class FileviewModule extends CListviewModule
{
	function __construct($name, $attribs)
	{
		parent::__construct($name, $attribs);
	}
	
	function FileviewModule($name, $attribs)
	{
		$this->__construct($name, $attribs);
	}
	
	protected function getModel()
	{
		return isset($this->_attribs['modname'])?$this->_attribs['modname']:'file';
	}

	public function show(&$ioparams = array())
	{
		$res = parent::show($ioparams);
		
		//最大上传限制
		$uploadmaxsize = get_upload_max_filesize();
		$uploadmaxsize -= 4*1024*1024; //减小4M
		$this->assign('uploadmaxsize', $uploadmaxsize);
		
	}
}
