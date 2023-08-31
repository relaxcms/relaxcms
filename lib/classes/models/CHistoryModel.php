<?php

/**
 * @file
 *
 * @brief 
 * 
 * 访问历史模型
 *
 */

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );
class CHistoryModel extends CTableModel
{
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);		
	}
		
	public function CHistoryModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}
	
	public function getHistory()
	{
		$userinfo = get_userinfo();
		$uid = $userinfo['id'];				
		if (!$uid) {
			return false;	
		}
				
		$params = array('uid'=>$uid, 'order'=>'ts', 'dir'=>'desc');		
		$udb =  $this->select($params);
		
		return $udb;
	}
	
	public function setHistory($ioparams)
	{
		$userinfo = get_userinfo();
		$uid = $userinfo['id'];				
		if (!$uid) {
			return false;	
		}
		
		$cname = $ioparams['component'];
		$tname =  $ioparams['task'];	
						
		$ts = time();
		$res = $this->getOne(array('uid'=>$uid, 'cname'=>$cname));
		if ($res) {			
			$res['ts'] = $ts;			
			$this->update($res);
		} else {
			$nr = $this->getTotal(array('uid'=>$uid));
			if ($nr >= 5) {
				$res = $this->getOne(array('uid'=>$uid), array('order'=>'ts', 'dir'=>'asc'));
				$this->del($res['id']);
			}			
			
			$params = array();
			$params['uid'] = $uid;
			$params['cname'] = $cname;
			$params['tname'] = $tname;
			$params['ts'] = $ts;			
			$this->set($params);
		}
		
		return true;
	}
	
	protected function writeLog($level, $action, $status, $oldParams=array(), $newParams=array(), $mid=0)
	{
		return false;
	}
}