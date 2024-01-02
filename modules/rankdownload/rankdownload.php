<?php
/**
 * @file
 *
 * @brief 
 * DownloadModule 模块
 *
 */
class RankdownloadModule extends CContentModule
{
	function __construct($name, $attribs)
	{
		parent::__construct($name, $attribs);
	}
	
	function RankdownloadModule($name, $attribs)
	{
		$this->__construct($name, $attribs);
	}	

	protected function getList($params, $num, $ioparams)
	{
		$m =  Factory::GetModel('content');
		$udb = $m->getList($params, $num, $ioparams); 
		
		return $udb;
	}

}