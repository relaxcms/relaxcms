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

		$rows = parent::show($ioparams);

		//photos
		$udb = array();
		$photos = isset($this->_attribs['photos'])?$this->_attribs['photos']:'';
		if ($photos) {
			$pdb = explode(',', $photos);
			foreach ($pdb as $key => $v) {
				$item = array();
				//$item['name'] = $v;
				$item['photo'] = $v;
				$udb[] = $item;
			}
		}

		//格式化content
		$idx = 0;
		foreach ($rows as $key => &$v) {
			if (isset($v['modname'])) {//扩展模型，如: apm_version 下载
				$m = Factory::GetModel($v['modname']);
				$res = $m->formatForModContent($v, $ioparams);
				if ($res)
					$v['content'] = $res;
			}
			if (empty($v['photo'])) {
				$v['photo'] = $udb[$idx++]['photo'];
			}
		}

		if (!$rows && $udb) {
			$rows = $udb;
		}

		$this->assign('rows', $rows);

	}
}