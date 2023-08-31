<?php

/**
 * @file
 *
 * @brief 
 * 
 * 模块
 *
 */

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class CModuleModel extends CTableModel
{
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);
	}
		
	public function CModuleModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}
	
	
	protected function _init_field(&$f)
	{
		switch ($f['name']) {
			case 'isdir':
				$f['input_type'] = "yesno";
				break;
			case 'status':
				$f['input_type'] = "onoff";
				break;
			case 'ctype':
			case 'name':
			case 'type':
			case 'mid':
			case 'content':
				$f['edit'] = false;
				break;
			default:
				break;
		}
		return true;
	}
	
	
	public function set(&$params, &$ioparams=array())
	{
		$res = parent::set($params);
		if ($res) {
			//查询tplfile
			$m2 = Factory::GetModel('module2tplfile');
			$tdb = $m2->select(array('mid'=>$params['id']));
			foreach ($tdb as $key=>$v) {
				if (is_file($v['tplfile']))
					touch($v['tplfile']);
			}			
		}
		return $res;	
	}
	
	public function setModuleParams($mid, $params)
	{
		

		$params['mid'] = $mid;
		$m = Factory::GetModel('module_params');
		$res = $m->set($params);
		if ($res) {
			//查询tplfile
			$m2 = Factory::GetModel('module2tplfile');
			$tdb = $m2->select(array('mid'=>$mid));
			foreach ($tdb as $key=>$v) {
				if (is_file($v['tplfile']))
					touch($v['tplfile']);//变更时间截
			}


		}
		return $res;	
	}
	
	
	
}