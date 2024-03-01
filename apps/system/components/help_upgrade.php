<?php

/**
 * @file
 *
 * @brief 
 *   升级
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class HelpUpgradeComponent extends CUpgradeComponent
{
	protected $_packagefile;
	public function __construct($name, $options)
	{
		parent::__construct($name, $options);
		$this->_packagefile = RPATH_CACHE.DS.'update.lz';
	}
	
	public function HelpUpgradeComponent($name, $options)
	{
		$this->__construct($name, $options);
	}
	
	protected function show(&$ioparams = array())
	{
		parent::show($ioparams);
		$this->initActiveTab(2);
		
		$this->assign('sys_product_version', get_product_version());
		
		
		$cf = get_config();
		$this->assign('sys_updateapi', $cf['updateapi']); 
	}
	
		
	private function updateRemoteVersion($vinfo)
	{
		//url
		$url = $vinfo['url'];
		$data = curlGET($url);
		if (!$data) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call curlGET from '$url' failed!");
			return false;
		}
		$dir = RPATH_CACHE.DS."upgrade";
		if (!is_dir($dir))
			mkdir($dir);
		$pfile = $dir.DS."update.lz";
		
		$res = s_write($pfile, $data);
		
		$up = Factory::GetUpgrade();		
		$res = $up->upgrade($pfile);
		if (!$data) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call upgrade failed!");
			return false;
		}
		
		return $res;
	}
	
	
	/**
	 * checkRemoteVersion 检查远程是否有可升级版本
	 *
	 * @param mixed $ioparams This is a description
	 * @return mixed This is the return value description
	 *
	 */
	protected function checkRemoteVersion(&$ioparams=array())
	{
		$update = $this->requestInt('update');
		//version
		$cf = get_config();
		$verapiurl = $cf['updateapi'].'/getLastVersion';
		//get_guid()
		
		$params = get_sysinfo();
						
		//product_id
		$res = requestSAPI($verapiurl, array('params'=>$params));
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, $res);
		$data = array();
		if ($res) {
			$res2 = CJson::decode($res);
			$data = $res2['data'];
			if ($data) {
				if ($update) { //升级
					$res = $this->updateRemoteVersion($data);
					$data['status'] = $res?2:-1;
				} 			
			} else {
				$res = false;
			}
		}
		
		showStatus($res?0:-1, $data);
	}
	
	protected function upgradeTable($oldTable, $modname)
	{
		$m = Factory::GetModel($modname);
		
		$db5 = Factory::GetDBO('', array('dbname'=>'rcdb5'));
		$db5->db_select('rcdb5');
		$udb = $db5->select($oldTable);
		
		$m->reconnect();
		$m->truncate();
		$res = true;
		foreach ($udb as $key=>$v) {
			$res = $m->setForce($v);
			if (!$res) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "set $modname failed!", $v);
				break;
			}
		}
		return $res;
	}
	
	
	protected function upgradeFrom5to7()
	{
		$res = $this->upgradeTable('cms_project', 'pm_project');
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "upgrade cms_project failed!");	
			return false;
		}
		/*
		$res = $this->upgradeTable('cms_project_group', 'pm_group');
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "upgrade cms_project_group failed!");	
			return false;
		}
		
		$res = $this->upgradeTable('cms_project_member2group', 'pm_member2group');
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "upgrade cms_project_member2group failed!");	
			return false;
		}
		
		$res = $this->upgradeTable('cms_project_privilege', 'pm_privilege');
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "upgrade cms_project_privilege failed!");	
			return false;
		}*/
		
		$res = $this->upgradeTable('cms_vhost', 'vh_host');
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "upgrade cms_vhost failed!");	
			return false;
		}
		
		return $res;
	}
	
	//update rc from 5to7
	protected function update(&$ioparams=array())
	{
		$res = $this->upgradeFrom5to7();
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "upgrade failed!");			
		}		
		showStatus($res?0:-1);
	}
	
	
}