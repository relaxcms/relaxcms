<?php
/**
 * @file
 *
 * @brief 
 * DownloadModule 模块
 *
 */
class DownloadModule extends CContentModule
{
	function __construct($name, $attribs)
	{
		parent::__construct($name, $attribs);
	}
	
	function DownloadModule($name, $attribs)
	{
		$this->__construct($name, $attribs);
	}	

	public function show(&$ioparams=array()) 
	{
		$udb = parent::show($ioparams);
			$ioparams['detail'] = true;

		$_udb = array();
		foreach ($udb as $key => $v) {
			if (isset($v['modname'])) {
				$m = Factory::GetModel($v['modname']);
				$m->formatForView($v, $ioparams);

				$_udb[] = $v;
			}
			
		}
		$this->assign('_udb', $_udb);

	}
}