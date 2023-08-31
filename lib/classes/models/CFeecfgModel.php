<?php
/**
 * @file
 *
 * @brief 
 * 
 * 费类配置基类
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class CFeecfgModel extends CModel
{
	protected $_basecno = "9999";
	
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);
	}
		
	public function CFeecfgModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}
		
	protected function _init_field(&$f)
	{
		switch ($f['name']) {
			case 'status':
				$f['input_type'] = "selector";	
				break;
			case 'cid':
				$f['input_type'] = "model";	
				$f['model'] = "fm_catalog";
				$f['edit'] = false;
				break;
			default:
				break;
		}
		
		return true;
	}
	
	
	protected function newID(&$params=array())
	{
		$id = 1;
		$m = Factory::GetModel('fm_catalog');
		while(1) {
			$newcno = sprintf("%s%02d", $this->_basecno, $id);
			$cinfo = $m->getOne(array('cno'=>$newcno));
			if (!$cinfo) 
				break;
			$id ++ ;
		}		
		$params['cno'] = $newcno;
		$params[$this->_pkey] = $id;	
		return $id;
	}
	
	
	protected function setFeeType($pm_cid, $fpinfo, $params)
	{
		$feecfg_id = $fpinfo['feecfg_id'];
		$m = Factory::GetModel('pm_feecfg');
		$fcfginfo = $m->get($feecfg_id);
		if (!$fcfginfo) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no feecfg id '$feecfg_id'!");
			return false;
		}
		
		$name = $fcfginfo['name'];
		$sp_cid = $fcfginfo['sp_cid'];
		$pp_cid = $fcfginfo['pp_cid'];
		
		//$cid1, $cid2, 
		$m1 = Factory::GetModel('fm_catalog');
		$m2 = Factory::GetModel('fm_feetype');
		
		//提计
		$pcinfo = $m1->get($sp_cid);
		$cid2 = $this->setCatalogByBaseCNO($pcinfo['cno'], $params);
		$tinfo = $m2->get(4);
		
		$cinfo = $m1->get($cid2);
		$name = "提计".$cinfo['name'];		
		$_params = array();		
		$_params['name'] = $name;
		$_params['pid'] = $tinfo['id'];
		$_params['type'] = $tinfo['type'];				
		$_params['feetype2catalog'] = array(
				array('cd'=>1, 'cid'=>$pm_cid),
				array('cd'=>-1, 'cid'=>$cid2),
				);		
		$res = $m2->set($_params);
		
		
		//支付
		$name = "支付".$cinfo['name'];
		$tinfo = $m2->get(2);
		$_params = array();		
		$_params['name'] = $name;
		$_params['pid'] = $tinfo['id'];
		$_params['type'] = $tinfo['type'];		
		$_params['pattern'] = $fpinfo['pattern'];		
		$_params['feetype2catalog'] = array(
				array('cd'=>1, 'cid'=>$cid2),
				array('cd'=>-1, 'cid'=>$pp_cid),
				);		
		$res = $m2->set($_params);		
		
		
		return $res;
	}
	
	protected function setCatalog(&$params)
	{
		$id = $params['id'];
		
		$feecfginfo = $this->get($id);
		$cno = $feecfginfo['cno'];
		
		$m = Factory::GetModel('fm_catalog');
		$cinfo = $m->getOne(array('cno'=>$cno));
		if ($cinfo) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "cno '$cno' exists!");
			return true;
		}
		
		
		$basecno = $this->_basecno;
		$binfo = $m->getOne(array('cno'=>$basecno));
		if (!$binfo) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "no base cno '$basecno' exists!");
			return true;
		}
		
		$_params = array();
		$_params['name'] = $params['name'];
		$_params['cno'] = $cno;
		
		$_params['pid'] = $binfo['id'];
		$_params['flags'] = $binfo['flags']&~1;
		$_params['type'] = $binfo['type'];
		$_params['cd'] = $binfo['cd'];
		
		$res = $m->set($_params);		
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "set RD catalog failed!", $_params);
			return false;
		}
		
		return $res;	
	}

	
	public function set(&$params, &$ioparams=array())
	{
		$res = parent::set($params, $ioparams);
		if ($res) {
			$res = $this->setCatalog($params);
		}
		return $res;						
	}
	
	
	protected function delCatalog($params)
	{
		//删除
		$m = Factory::GetModel('fm_catalog');
		$cinfo = $m->getOne(array('cno'=>$params['cno']));
		if (!$cinfo) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "no cno '$cno'!");
			return false;
		}
		
		$res = $m->del($cinfo['id']);
		
		return $res;
	}
	
	public function del($id)
	{
		$res = parent::del($id);
		if ($res) {
			$res = $this->delCatalog($res);
		}
		return $res;						
	}
	
}
