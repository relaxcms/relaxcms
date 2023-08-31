<?php

defined('RPATH_BASE') or die();
class CModelModel extends CTableModel
{
	public function __construct($name, $options=null)
	{
		parent::__construct($name, $options);
	}
	
	public function CModelModel($name, $options=null)
	{
		$this->__construct($name, $options);
	}

	protected function _init_field(&$f)
	{
		switch ($f['name']) {
			case 'status':
				$f['input_type'] = 'onoff';
				break;
			case 'flags':
				$f['input_type'] = 'varmulticheckbox';
				$f['show'] = false;
				break;
			default:
				break;
		}
		return true;
	}
	
	
	public function setModel($id, $params, &$ioparams=array())
	{
		$modinfo = $this->get($id);
		if (!$modinfo) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "no mid '$mid'!");
			return false;
		}
		
		$modname = $modinfo['model'];
		$cid = $params['id'];
		$mid = 0;		
		$m1 = Factory::GetModel('model2content');
		$m2cinfo = $m1->getOne(array('cid'=>$cid));
		if ($m2cinfo) {
			$mid = $m2cinfo['mid'];		
		}
				
		$m = Factory::GetModel($modname);
		$params['id'] = $mid;
		$res = $m->set($params);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "set model '$modname' failed!");
			return false;
		}
		
		$mid = $params['id'];
		if (!$m2cinfo) {
			//¹ØÁª
			$_params = array();
			$_params['modname'] = $modname;
			$_params['mid'] = $mid;
			$_params['cid'] = $cid;			
			$res = $m1->set($_params);				
			if (!$res) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "set model2content failed!", $_params);
				return false;
			}
		}
		
		return $res;
				
	}
}