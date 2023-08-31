<?php

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class CStorageModel extends CTableModel
{
	protected $_storages = array();

	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);
	}
		
	public function CStorageModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}
	
	protected function _initFeildEx(&$f)
	{
		switch ($f['name']) {
			case 'status':
			case 'stype':
				$f['input_type'] = 'selector';
				break;
			case 'sid':
				$f['input_type'] = 'model';
				$f['model'] = 'server';
				break;
			case 'ts':
			case 'username':
				$f['show'] = false;				
				break;
			case 'password':
				$f['input_type'] = "password";
				$f['show'] = false;
				$f['nohide'] = true;
				break;
			case 'total':
			case 'used':
				$f['input_type'] = "SIZE";
				break;
			case 'playpath':
				$f['show'] = false;
				$f['edit'] = false;
				break;			
			default:
				break;
		}
		
		return true;
	}
	
	public function formatForView(&$row, &$ioparams=array())
	{
		$status = $row['status'];
		$res =  parent::formatForView($row, $ioparams);
		//status
		$row['status'] = $this->formatLabelColorForView($status, $row['status']);
		//webpath
		$row['webpath'] = "<a href='".$row['webpath']."' target=_blank >".$row['webpath']."</a>";
	}
	
	
	protected function initLocalStorage()
	{
		$cf = get_config();
		
		$params = Factory::GetParams();
		$webroot = $params['_webroot'];
		
		$datadir = (isset($cf['datadir']) && is_dir($cf['datadir']))?$cf['datadir']:RPATH_DATA;
		$webpath = (isset($cf['datauri']) && $cf['datauri'])?$cf['datauri']:$webroot.'/data';
		
		//默认本地
		$mountdir = $datadir;
		if (!is_dir($mountdir))
			mkdir($mountdir);

		$mountdir = str_replace(DS, '/', $mountdir);
				
		$storageinfo = array(
				'id' => 1,
				'name' => 'default',
				'oid' => 0,
				'stype'=> 1,
				'sid'=> 1,
				'mountdir'=> $mountdir,
				'webpath'=> $webpath
				);
		$storageinfo['total'] = disk_total_space($mountdir);
		$storageinfo['free'] = disk_free_space($mountdir);
		$storageinfo['used'] = $storageinfo['total'] - $storageinfo['free'];

		$res = $this->add($storageinfo);

		return $storageinfo;
	}
	
	
	protected function getServerInfo(&$params)
	{
		$m = Factory::GetModel('server');
		$serverinfo = $m->get( $params['sid']);
		if (!$serverinfo) {
			$params['vodrooturl'] = $params['webpath'];
			$params['lanvodrooturl'] = $params['webpath'];
		} else {
			$params['vodrooturl'] = $serverinfo['vodrooturl'].$params['webpath'];
			$params['lanvodrooturl'] = $serverinfo['lanvodrooturl'].$params['webpath'];
		}
	}
	
	public function get($id)
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "IN ", $id);
		
		if (!$id)
			$id = 1;
		$res = parent::get($id) ;
		if (!$res) {
			$this->initLocalStorage();
			$res = parent::get($id) ;
		}
		
		if ($res) {
			$res['downloadpath'] = '/d'.substr($res['webpath'], 1); //用于下载
			$this->getServerInfo($res);	
		}	
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "OUT...");
		
		return $res;
	}	
	
	public function getStorageDir($id)
	{
		$storageinfo = $this->get($id);
		if (!$storageinfo)
			return false;

		return $storageinfo['mountdir'];
	}
	
	public function getStorageTotalSpace($id)
	{
		$storageinfo = $this->get($id);
		if (!$storageinfo)
			return false;

		return $storageinfo['total'];
	}
		
	public function getStorageFreeSpace($id)
	{
		$storageinfo = $this->get($id);
		if (!$storageinfo)
			return false;

		return $storageinfo['free'];
	}
	
	
	public function getStorageInfo($sid, $uid=0)
	{
		//查询用户所在单位分配置的空间
		$dispatch_total = 0;
		$used_total = 0;
		$max_freespace = 0;
		$max_freespace_sid = 1;
		$max_freespace_oid = 0;
		$no_any_org = false;
		
		if ($uid > 0) {
			$sql = "select * from cms_user2org where uid=$uid";
			$res = $this->_db->get_one($sql);
			if ($res) {
				$oid = $res['oid'];				
				$sql = "select * from cms_storage_dispatch where oid=$oid";
				$udb = $this->_db->select($sql);
				//rlog($udb);
				
				//可用空间最大的存储为默认存储
				foreach ($udb as $v) { //空闲空间最大的
					$dispatch_total += $v['dispatch'];
					$used_total += $v['used'];					
					$v['free'] = $v['dispatch'] - $v['used'];		
					if ($max_freespace < $v['free']  ) {
						$max_freespace = $v['free'] ;
						$max_freespace_sid = $v['sid'];
						$oid = $v['oid'];
					}
				}
			} else { //未加入组织，未限制空间，使用默认本地空间
				$no_any_org = true;
				$max_freespace_sid = 1;
			}
		}
		if ($sid == 0)
			$sid = $max_freespace_sid;
				
		$storageinfo = $this->get($sid);
		if (!$storageinfo) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no storage '$sid'");
			return false;
		}
		
		
		$basepath = $uid.'/'.tformat(0, 'Ym');
		$basedir = $storageinfo['mountdir'].DS.$basepath; //eg:1/202007
		if (!is_dir($basedir))
			s_mkdir($basedir);		
		
		$storageinfo['basedir'] = $basedir;
		$storageinfo['basepath'] = $basepath;
		
		if ($uid > 0 && !$no_any_org ) {
			
			$storageinfo['dispatch'] = $dispatch_total;
			$storageinfo['used'] = $used_total;
			$storageinfo['free'] = $max_freespace;
			$storageinfo['oid'] = $oid;
			
		} else { //默认
				
			$storageinfo['dispatch'] = $storageinfo['total'];				
			$storageinfo['free'] = $storageinfo['total'] - $storageinfo['used'];	
			$storageinfo['oid'] = 0;			
		}
		
		return $storageinfo;
	}
	
	
	/**
	 * 更新自动挂载脚本或批处理
	 * 
	 * 注：WINDOWS批处理暂不支持（测试环境）
	 *
	 * @param mixed $params This is a description
	 * @return mixed This is the return value description
	 *
	 */

	protected function updateStorageAutomount()
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "IN");
		
		$udb = $this->select(array('status'=>1));
		if (!$udb) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "no any storage!");
			return false;		
		}
		$cf = get_config();	
		
		$homevardir = $cf['homedir'].DS.'var';
		$storagecfgdir = $homevardir.DS.'conf'.DS.'storage';
		if (!is_dir($storagecfgdir))
			mkdir($storagecfgdir);
			
		$automount = $storagecfgdir.DS."automount.sh";
		$data = "#!/bin/sh\n";
		$data .= "LOGFILE=/dev/null\n";
		$data .= "\n";
		
		foreach ($udb as $key=>$v) {
			$stype = $v['stype'];
			$mountdir = $v['mountdir'];
			$spath = $v['spath']; //eg : //192.168.10.238/a
			$username = $v['username'];
			$password = $v['password'];
			
			switch($stype) {
				case 1:   // LOCAL PATH
					break;
				case 2:   //SMB
					$data .= "mount -t cifs -o username=$username,password=$password $spath $mountdir\n";
					break;
				case 3:  //WEBDAV
					$data .= "mount -t davfs -o rw,uid=crab,gid=root $spath $mountdir << EOF >> \$LOGFILE 2>&1\n$username\n$password\nEOF\n";
					break;
				default:  
					break;
			}
		}		
		$data .= "\n";
		$res = s_write($automount, $data);
			
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "OUT");
		return $res > 0;
	}
	
	protected function setLocationForPlaypath($sinfo, $restart=true)
	{
		$id = $sinfo['id'];
		
		$mountdir = $sinfo['mountdir'];
		$webpath = $sinfo['webpath'];
		if (is_dir(RPATH_ROOT.$webpath)) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "webpath '$webpath' is subdir of '".RPATH_ROOT."'!");
			return false;
		}
		
		if (!is_dir($mountdir)) 
			s_mkdir($mountdir);
		
		//创建location or alias 
		/*
		LoadModule h264_streaming_module modules/mod_mp4.so 
		
		Alias /avod /opt/crab/var/webdav/a
		*/
		$name = substr($webpath, 1); 
		if (!is_name($name)) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "invalid webpath '$webpath'!");
			return false;
		}
		
		$downloadwebpath = '/d'.substr($webpath,1);	//download location
		
		$data = "LoadModule h264_streaming_module modules/mod_h264_streaming.so\n";
		$data .= "Alias $webpath $mountdir\n";
		$data .= "\n\n";
		$data .= "Alias $downloadwebpath $mountdir\n";
		$data .= "\n\n";
		$data .= "<Location $webpath>\n";
		$data .= "AddHandler h264-streaming.extensions .mp4\n";
		$data .= "</Location>\n";
		$data .= "\n\n";
		
		$data .= "<Directory $mountdir>\n";
		$data .= "Options +Indexes \n";
		$data .= "IndexOptions FancyIndexing\n";
		$data .= "AddDefaultCharset UTF-8\n";
		$data .= "Order allow,deny\n";
		$data .= "Allow from all\n";
		$data .= "</Directory>\n";
		$data .= "\n";
		
		
		//cfg
		$cf = get_config();
		$sscfgdir = $cf['homedir'].DS.'var'.DS.'conf'.DS.'storage';
		if (!is_dir($sscfgdir))
			s_mkdir($sscfgdir);
		
		$fname = 'sd'.$id;
						
		$cfgfile = $sscfgdir.DS.$fname.'.conf';
		@s_write($cfgfile, $data);
		
		//重启 server
		if ($restart)
			restart_webserver();
		
		return true;
	}
		
	public function cacheStorage()
	{
		$udb = $this->select(array('status'=>1));
		if (!$udb) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "no any storage!");
			return false;		
		}
		
		$nr = count($udb);
		$idx = 0;
		foreach ($udb as $key=>$v) {
			$res = $this->setLocationForPlaypath($v, $idx++ == $nr-1);
		}		
		return $res;				
	}
	
	
	
	protected function setStorageExtInfo($params, &$ioparams=array())
	{
		$res = false;
		
		switch($params['stype']) {
			case 1:   // LOCAL PATH //本地WEB服务器，作为流媒体点播服务器
				
			case 2:   //SMB
			case 3:  //WEBDAV				
				$res = $this->setLocationForPlaypath($params);
				break;
			default:  				
				break;
		}
		
		if (!isset($ioparams['noupdate']))
			$res = $this->updateStorageAutomount();
		
		return $res;
	}
	
	public function updateAutoMountConfig()
	{
		return $this->updateStorageAutomount();
	}
		
	protected function checkParams(&$params, &$ioparams=array())
	{
		$res = parent::checkParams($params, $ioparams);
		if (!$res)
			return $res;
		
		//本地目录状态总是'正常'
		if ($params['stype'] == 1)
			$params['status'] = 1;
		
		//检查WEB
		$params['mountdir'] = trim($params['mountdir']);
		
		//检查
		$webpath = trim($params['webpath']);
		if (!$webpath) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no webpath !");
			return false;
		}
		
		if (!is_uripath($webpath)) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "invalid webpath '$webpath'!");
			return false;
		}
		
		$params['webpath'] = $webpath;
		
		//检查PASSWORD
		/*$password = trim($params['password']);
		if (!$password) {//
			$res = parent::get($params['id']);
			if ($res) {
				$params['password'] = $res['password'];
			}
		}*/
		
		return true;
	}
	
	public function set(&$params, &$ioparams=array())
	{
		$res = parent::set($params, $ioparams);
		if ($res) {
			$this->setStorageExtInfo($params, $ioparams);
		}
		
		return $res;
	}
	
	public function del($id)
	{
		$oldinfo = $this->get($id);
		if (!$oldinfo) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no storage id '$id'");
			return false;
		}
		if ($oldinfo['used'] > 0) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "storage '$id' is busy!");
			return false;
		}
						
		$res = parent::del($id);
		if ($res) {
			$this->updateStorageAutomount();
		}
		
		return $res;
	}
	
	
	protected function checkLocalStorageSpace($storageinfo)
	{
		$deafult_files_data_dir = $storageinfo['mountdir'];
		$total = disk_total_space($deafult_files_data_dir);
		$free = disk_free_space($deafult_files_data_dir);
		$used = $total - $free;
		
		$id = $storageinfo['id'];
		
		$sql = "update cms_storage set used=$used where id=$id";
		$res = $this->_db->exec($sql);		
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "update local storage used failed! sql=$sql");
		}
	}
	
	
	public function checkStorageStatusSingle($params)
	{
		if ($params['stype'] == 1) {//本地不检
			//check space
			//$this->checkLocalStorageSpace($params);
			return false;
		}
		
		$url = $params['spath'].'/index.html';
		$res = http_request($url, $httpCode);
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "check $url, httpCode=$httpCode, res=".$res);
		
		if ($httpCode == 401 
			//|| $httpCode == 404 
			|| $httpCode == 200) {
				
				//更新为: '正常'
			$sql = 'update cms_storage set status=1 where id='.$params['id'];
		} else {
			$sql = 'update cms_storage set status=2 where id='.$params['id'];
		}
		
		$this->_db->exec($sql);
	}
	
	
	public function checkStorageStatus()
	{
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN");
		$udb = $this->select();
		foreach ($udb as $key=>$v) {
			//$this->checkStorageStatusSingle($v);
		}
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "OUT");
	}
	
	
}

