<?php

/**
 * @file
 *
 * @brief 
 * 
 * 文件引用模型
 *
 */

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );
class CFile2modelModel extends CTableModel
{
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);		
	}
		
	public function CFile2modelModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}
	
	protected function _initFieldEx(&$f)
	{
		switch ($f['name']) {
			case 'fid':
			case 'modname':
			case 'mid':
			case 'num':
				$f['edit'] = false;
				break;
			default:
				break;
		}
		
		return true;
	}
	
	
	public function setFile2ModelByUrl($modname, $mid, $url)
	{
		$m = Factory::GetModel('file');
		$fileinfo = $m->getFileInfoByUrl($url);
		if ($fileinfo) {
			$fid = $fileinfo['id'];
			
			$_params = array();
			
			$_params['num'] = 1;
			$_params['fid'] = $fid;
			$_params['modname'] = $modname;
			$_params['mid'] = $mid;			
			$this->set($_params);
		}	
	}
	
	public function trigger($event, $finfo=array())
	{
		$params = array();
		$params['fid'] = $finfo['id'];
		$udb = $this->select($params);
		foreach ($udb as $key=>$v) {
			$modname = $v['modname'];
			$mid = $v['mid'];
			
			$m = Factory::GetModel($modname);
			$m->trigger($event, array('id'=>$mid));
		}
	}
}
