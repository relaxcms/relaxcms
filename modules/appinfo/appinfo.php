<?php
/**
 * @file
 *
 * @brief 
 * AppinfoModule 模块
 *
 */
class AppinfoModule extends CContentModule
{
	function __construct($name, $attribs)
	{
		parent::__construct($name, $attribs);
	}
	
	function AppinfoModule($name, $attribs)
	{
		$this->__construct($name, $attribs);
	}	

	public function show(&$ioparams=array()) 
	{
		$udb = parent::show($ioparams);

		foreach ($udb as $key => &$v) {
			if (isset($v['modname'])) {
				$m = Factory::GetModel($v['modname']);
				$m->formatForView($v, $ioparams);
			}
			
		}
		$this->assign('rows', $udb);

	}
}