<?php
/**
 * @file
 *
 * @brief 
 * BannerModule 模块
 *
 */
class BannerModule extends CContentModule
{
	function __construct($name, $attribs)
	{
		parent::__construct($name, $attribs);
	}
	
	function BannerModule($name, $attribs)
	{
		$this->__construct($name, $attribs);
	}	

	public function show(&$ioparams=array()) 
	{
		$udb = parent::show($ioparams);

		//格式化content

		foreach ($udb as $key => &$v) {
			if (isset($v['modname'])) {//扩展模型，如: apm_version 下载
				$m = Factory::GetModel($v['modname']);
				$res = $m->formatForModContent($v, $ioparams);
				if ($res)
					$v['content'] = $res;
			}
		}

		$this->assign('rows', $udb);

	}
}