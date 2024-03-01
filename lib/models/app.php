<?php

/**
 * @file
 *
 * @brief 
 * 
 * App管理类
 *
 */

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );
class AppModel extends CAppModel
{
	protected $_cacheappfile = null;
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);
		$this->_default_sort_field_name = 'name';
		$this->_default_sort_field_mode = 'asc';
		$this->_cacheappfile = RPATH_CACHE.DS.$name.'_applist.cache';
	}
	
	public function AppModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}
	
	/*protected function _initFieldEx(&$f)
	{
		parent::_initFieldEx($f);
		
		switch ($f['name']) {
			case 'remote':
			case 'local':
				$f['searchable'] = 2;	
				break;
			default:
				break;
		}
	}*/
	
	protected function _initActions()
	{
		parent::_initActions();
		$this->enableAction('edit', false);
	}
	
	public function formatForView(&$row, &$ioparams = array())
	{
		$res = parent::formatForView($row, $ioparams);
		
		$id = $row['id'];
		$_base = $ioparams['_base'];
		
		
		$current_version = $row['version'];
		
		$hasNewVersion = compareAppVersion($row['remote_version'], $current_version) > 0? true:false;
		
		//extinfo
		$extinfo = "<span class='pull-right'>";
		if ($row['local'] != 1) { //不在本地，要下载
			$extinfo .= "<a href='$_base/installFromRemote?id=$id' class='btn btn-sm blue installFromRemote' data-app='$id'>远程安装</a> ";
		} elseif ($row['installed'] != 1) { //安装要
			$extinfo .= "<a href='$_base/install?id=$id' class='btn btn-sm blue install'>安装</a> ";
		} else {
			if ($hasNewVersion) 
				$extinfo .= "<a href='$_base/upgradeFromRemote?id=$id' class='btn btn-sm blue upgradeFromRemote'  data-app='$id'>升级</a> ";
				
			$extinfo .= "<a href='$_base/uninstall?id=$id' class='btn btn-sm red uninstall'>卸载</a> ";
			$extinfo .= " <a href='$_base/uninstallall?id=$id' class='btn btn-sm red uninstall'>完全卸载</a>";
		}
		$extinfo .= "</span>";
		
		
		//$row['url'] = $_base."/detail?id=$id";
		
		//url
		$row['_extinfo'] = $extinfo;
		
		//name
		$row['_name'] = $row['name'];


		
		
		return $res;
	}

	protected function getActions($row=array(), &$ioparams=array())
	{
		$actions = $this->_default_actions;
		

		if ($row['remote'] != 1) {
			$actions['del'] = false;
		}




		//远程安装
		if ($row['remote'] == 1 && $row['local'] != 1 ) {
			$action = array(
			'name'=>'installFromRemote',
			'icon'=>'fa fa-install',
			'title'=>'远程安装',
			'class'=>'btn-primary',
			'action'=>'button',
			'msg'=>'确定远程安装吗？',
			'enable'=>true,
			);
			$actions[$action['name']] = $action;
		}

		//在本未安装
		if ($row['installed'] != 1 && $row['local'] == 1 ) {
			$action = array(
			'name'=>'install',
			'icon'=>'fa fa-install',
			'title'=>'安装',
			'class'=>'btn-primary',
			'action'=>'button',
			'msg'=>'确定安装吗？',
			'enable'=>true,
			);
			$actions[$action['name']] = $action;
		}


		//已安装： 卸载、完全卸载
		if ($row['installed'] == 1 ) {
			$hasNewVersion = compareAppVersion($row['remote_version'], $row['version']) > 0? true:false;
		
			if ($hasNewVersion) {
				$action = array(
				'name'=>'upgradeFromRemote',
				'icon'=>'fa fa-install',
				'title'=>'升级',
				'class'=>'btn-primary',
				'action'=>'button',
				'msg'=>'确定升级吗？',
				'enable'=>true,
				);
				$actions[$action['name']] = $action;
			}
			

			$action = array(
			'name'=>'uninstall',
			'icon'=>'fa fa-install',
			'title'=>'卸载',
			'class'=>'btn-danger',
			'action'=>'button',
			'msg'=>'卸载应用，保留安装文件与数据，确定卸载吗？',
			'enable'=>true,
			);
			$actions[$action['name']] = $action;


			$action = array(
			'name'=>'uninstallall',
			'icon'=>'fa fa-install',
			'title'=>'完全卸载',
			'class'=>'btn-danger',
			'action'=>'button',
			'msg'=>'完全卸载将删除运行数据，删除后无法恢复，确定完全卸载吗？',
			'enable'=>true,
			);
			$actions[$action['name']] = $action;
		}

		
		

		return $actions;
	}

		
	public function set(&$params, &$ioparams=array())
	{
		$need_update = true;
		$res = $this->getOne(array('name'=>$params['name'], 'type'=>$params['type']));
		if ($res) {
			$params['id'] = $res['id'];
			$remote = $res['remote'];
			if ($remote && $params['local'] == 1) { //本地更新
				unset($params['description']);
				unset($params['title']);
			} else if ($params['remote'] == 1) {
				unset($params['ts']);
				unset($params['ctime']);
			}
			
			//检查是否要更新	
			$nr = 0;		
			foreach ($params as $key=>$v) {
				if (isset($res[$key]) && $v != $res[$key]) {
					$nr ++;
					rlog(RC_LOG_DEBUG, __FUNCTION__, "################ nr=".$nr.", key=$key, v=$v, old=".$res[$key]);
					
				}
			}
			$need_update = $nr > 0?true:false;
		}
		
		
		$res = $need_update? parent::set($params, $ioparams):false;
			
		return $res;
	}
	
	
	
	protected function setLocalApp($params, $type=AT_RCAPP)
	{
		$params['local'] = 1;
		$params['type'] = $type;		
		
		$res = $this->set($params);
		
		return $res;
	}
	
	protected function setRemoteApp($params)
	{
		$params['remote'] = 1;
		//$params['type'] = AT_RCAPP;
		$res = $this->set($params);
		
		return $res;
	}
	
	
	
	
	protected function loadRemoteAppOne($appinfo)
	{
		//查询
		$old = $this->getOne(array('name'=>$appinfo['name']));
		
		$params = $appinfo;
			
		if ($old) {
			$params['id'] = $old['id'];				
		} else {
			$params['id'] = 0;
		}
		
		//更新 remote_version
		$params['remote_version'] = $appinfo['last_version'];
		$params['remote_download_url'] = $appinfo['last_version_download_url'];
		
		$res = $this->setRemoteApp($params);
				
		return $res;
	}

	
	protected function loadRemoteApp($type=0)
	{
		//_cacheappfile
		$ts = time();
		$mt = file_exists($this->_cacheappfile)?filemtime($this->_cacheappfile):0;
		if ($mt+60 < $ts) { //缓存3分钟
			$cf = get_config();
			$updatetype = intval($cf['updatetype']);
			if ($updatetype !== 1)
				return false;
				
			$apiurl = $cf['updateapi'].'/getRCAppList';				
			$params = get_sysinfo();		
			$params['type'] = $type;
			$data = requestSAPI($apiurl, array('params'=>$params));		
			//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, 'data='.$data);
				
		} else {
			$sec = $ts - $mt;
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "waiting sync after $sec s!");
			return true;
		}
				
		$udb = array();
		if ($data) {
			$res2 = CJson::decode($data);
			if ($res2) {
				$udb = $res2['data'];			
			} else {
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "invalid data '$data'!apiurl=$apiurl", $params);
			}
		} 
		if (!$udb) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "no udb!", $udb, $data);
			return false;
		}
		
		s_write($this->_cacheappfile, $data);	
		
		//同步远程APP	
		$res = false;	
		$rdb = array();
		foreach ($udb as $key=>$v) {
			$res = $this->loadRemoteAppOne($v);
			if (!$res)
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "setRemoteAppOne failed!");
			
			$rdb[$v['name']] = $v;
		}
		
		//检查是否需要删除本地的记录
		$cdb = $this->gets(array('remote'=>1));
		foreach ($cdb as $key=>$v) {
			$appname = $v['name'];
			if (!isset($rdb[$appname]) && $v['local'] != 1) {
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "remove $appname !");
				$this->del($v['id']);
			}
		}
		
		return $res;
	}
	
	
	protected function loadLocalApp()
	{
		$cf = get_config();
		
		$apps = Factory::GetApps();
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, $apps);
		
		//遍历目录
		$dir = RPATH_APPS;
		$udb = s_readdir($dir);
		$hdb = array('.svn');
		
		$tdb = array();
		foreach ($udb as $key=>$v) {
			$name = $v;
			
			if (in_array($name, $hdb))
				continue;
			$app = Factory::GetApp($name);
			if (!$app) 
				continue;
			
			$item = $app->getAppcfg();						
			if (isset($item['embeded']) && $item['embeded'])
				continue;
			
			$item['name'] = $name.'-rcapp';
			$item['appname'] = $name;
			//$item['installed'] = array_key_exists($name, $apps)?1:0;
			//version.txt
			$versionfile = $dir.DS.$name.DS.'version.txt';
			if (file_exists($versionfile)) {
				$item['version'] = file_get_contents($versionfile);
			}
			
			$this->setLocalApp($item);		
		}
		
		return true;
	}
	
	protected $_templates = array();
	
	protected function getTpls($key=null)
	{
		if (!$this->_templates) {
			$file = RPATH_CONFIG.DS.'templates.php';
			if (file_exists($file)) {
				require $file;
				if ($templates) {
					$this->_templates = $templates;					
				}
			}
		}		
		return $this->_templates;
	}
	
	
	//loadLocalTpl
	protected function loadLocalTpl()
	{
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN");
		
		
		$tpls = $this->getTpls();
		
		
		$templates = array();
		
		$dir = RPATH_TEMPLATES;
		$udb = s_readdir($dir);
		
		$hdb = array('.svn');	
		$id = 1;	
		foreach ($udb as $key=>$v) {
			$name = $v;
			
			if (in_array($name, $hdb))
				continue;
			
			$item = array();	
			$cfgfile = $dir.DS.$name.DS.'config.php';
			if (file_exists($cfgfile)) {
				require $cfgfile;				
				$item = $appcfg;
			} else {
				//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "no config '$cfile'!");
				continue;
			}
			
			$item['name'] = $name.'-rctpl';
			$item['appname'] = $name;
			//$item['installed'] = (array_key_exists($name, $tpls) && $tpls[$name]['enable'] == true)?1:0;
			//version.txt
			$versionfile = $dir.DS.$name.DS.'version.txt';
			if (file_exists($versionfile)) {
				$item['version'] = file_get_contents($versionfile);
			}
			
			$res = $this->setLocalApp($item, AT_RCTPL);
			if (!$res) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "set local TPL failed!");
			}
		}
		return $res;
	}
	
	protected function loadLocalThe()
	{
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "TODO...");
		return false;
	}
	
	
	public function loadApp()
	{
		$res1 = $this->loadLocalApp();
		$res2 = $this->loadLocalTpl();
		$res3 = $this->loadLocalThe();
		$res4 = $this->loadRemoteApp();
		
		$res = $res1 || $res2 || $res3 || $res4;
				
		return $res;
	}
		
	
	protected function doInstall($appinfo, &$ioparams=array())
	{
		$name = $appinfo['appname'];
		
		$res = true;
		$apps = Factory::GetApps();
		
		$names = $name;
		if (!is_array($names))
			$names = explode(',', $names);
		
		foreach ($names as $key=>$v) {
			if (!isset($apps[$v]))
				$apps[$v] = false;		
		} 
		
		cache_apps($apps);
		
		$idb = array();
		$install_apps = array();
		$nr_failed = 0;
		foreach ($apps as $key=>$v) {
			if (!$v) {
				//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "key=$key", $apps, $appinfo);
				$app = Factory::GetApp($key);
				if ($app) {
					if (($res = $app->install())) {//表存在，可能报错
						$idb[] = $key;
						$install_apps[$key] = $v;
					} else {
						rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "install app '$key' failed!");
						$nr_failed ++;
						continue;
					}
				}
			} else {
				$install_apps[$key] = $v;
			}
		}
		
		if ($res) {			
			cache_apps($install_apps);
			//重新缓存菜单
			Factory::GetApp()->cache();		
			$iapps = implode(',', $idb);	
			if ($idb) {
				setMsg('str_plugin_install_ok', $iapps);
			} else {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no install '$iapps'!");
				//setErr('str_plugin_install_failed', $iapps);
			}
		}
		
		return $res;
	}
	
	protected function doInstallTpl($appinfo, &$ioparams=array())
	{
		$m = Factory::GetModel('template');
		
		$res = $m->install($appinfo, $ioparams);
		
		
		return $res;
	}
	
	
	protected function installApp($appinfo, &$ioparams=array())
	{
		if (!$appinfo) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "no appinfo!");
			return false;
		}
		
		//rkey
		if ($appinfo['rkey'] == 1 && !is_rkey_support()) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no rkey for id '$id'!");
			return false;
		}
		
		//
		$id = $appinfo['id'];
		$type = $appinfo['type'];
		switch ($type)
		{
			case AT_RCTHE:
				break;
			case AT_RCTPL:
				$res = $this->doInstallTpl($appinfo, $ioparams);		
				break;
			default:
				$res = $this->doInstall($appinfo, $ioparams);				
				break;
		}
		//rlog(RC_LOG_DEBUG, __LINE__, __FILE__, __FUNCTION__, '$res='.$res);
		if ($res) {
			//set installed=1
			$params = array();
			$params['installed'] = 1;
			if ($appinfo['local'] != 1) {
				$params['version'] = $appinfo['remote_version'];
				$params['local'] = 1;
			}
			$params['id'] = $id;			
			$res = $this->update($params);
		}
		
		return $res;
	}
	
	
	public function install($id, &$ioparams=array())
	{
		$appinfo = $this->get($id);
		if (!$appinfo) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "no id '$id'!");
			return false;
		}
		
		$res = $this->installApp($appinfo, $ioparams);
		
		return $res;
	}


	private function check_app_depends($name)
	{
		$apps = Factory::GetApps();
		foreach ($apps as $key=>$v) {		
			$deps = isset($v['depends'])?$v['depends']:null;			
			if ($deps) {
				if (!is_array($deps))
					$deps = explode(',',$deps);
				if (in_array($name, $deps))
					return true;
			}
		}
		return false;
	}
	

	protected function doUninstall($appinfo, $dropall = false)
	{
		$name = $appinfo['appname'];
		
		$names = $name;
		if (!is_array($names))
			$names = explode(',', $names);
		
		$apps = Factory::GetApps();
		$pdb = array();			
		$idb = array();
		foreach ($apps as $key=>$v) {
			if (!in_array($key, $names)) {
				$pdb[$key] = $v;
			} else {
				//检查依赖项
				if ($this->check_app_depends($key)) { //依赖存在， 不动
					$pdb[$key] = $v;
				} else {
					$idb[] = $key;
				}
			}
		}
		$apps = $pdb;
		cache_apps($apps);
		
		
		foreach ($names as $key=>$v) {
			if (!file_exists(RPATH_APPS.DS.$v)) {
				$idb[] = $v;
			}
		}		
		
		if ($dropall) { //当前uninstall只清理数据
			foreach ($idb as $key=>$v) {
				$app = Factory::GetApp($v);
				if ($app) {
					$app->uninstall($dropall?1:0);					 
				} else {
					//crab 扩展, 如: ffmpeg
					rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "undo '$v' ... ");
					$m = Factory::GetUpgrade();
					$res = $m->undoUpgradeCrab($v);
					if ($res) {
						$_params = array('id'=>$appinfo['id'], 'local'=>0);
						$this->update($_params);
					}
				}
			}
		}
		
		
		//重新缓存菜单
		Factory::GetApp()->cache();					
		
		$res = implode(',', $idb);
		setMsg('str_plugin_uninstall_ok', $res);
		
		return true;
	}
	
	
	protected function doUninstallTpl($tinfo)
	{
		$m = Factory::GetModel('template');
		$res = $m->uninstall($tinfo);
		
		return $res;
	}
		
	public function uninstall($id, $dropall=false)
	{
		$res = $this->get($id);
		if (!$res) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "no id '$id'!");
			return false;
		}
		
		$id = $res['id'];
		$type = $res['type'];
		switch ($type)
		{
			case AT_RCTHE:
				break;
			case AT_RCTPL:
				$res = $this->doUninstallTpl($res);		
				break;
			default:
				$res = $this->doUninstall($res, $dropall);				
				break;
		}	
		
		if ($res) {
			//set installed=0
			$params = array();
			$params['installed'] = 0;
			$params['id'] = $id;
			
			$res = $this->update($params);
		}
		
		return $res;
	}
	
	protected function downloadRemoteApp($vinfo)
	{
		//请求下载地址
		
		$url = $vinfo['url'];

		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $vinfo);
			
		$data = curlGET($url);
		if (!$data) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call curlGET from '$url' failed!", $appinfo);
			return false;
		}

		$dir = RPATH_CACHE.DS."appdownload";
		if (!is_dir($dir))
			mkdir($dir);
		$pfile = $dir.DS."app.lz";
		
		$res = s_write($pfile, $data);
		
		$up = Factory::GetUpgrade();		
		$res = $up->upgrade($pfile);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call upgrade failed!");
			return false;
		}
		
		return $res;
		
	}

	protected function checkAppDownloadVersion($appinfo)
	{
		//请求下载地址
		$cf = get_config();
		$apiurl = $cf['updateapi'].'/checkAppDownloadVersionInfo';
		$params = get_sysinfo();		
		$params['appid'] = $appinfo['appid'];
		$params['rkey'] = is_rkey_support()?1:0;

		$res = requestSAPI($apiurl, array('params'=>$params));		
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "call requestSAPI failed!apiurl=$apiurl");
			return false;
		}
		
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $res);
		
		$res2 = CJson::decode($res);
		if (!isset($res2['data'])) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "invalid requestSAPI result!", $res);
			return false;
		}
		$version = $res2['data'];

		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $version);
			
		return $version;
	}
	
	
	public function installFromRemote($id, &$ioparams=array())
	{
		$appinfo = $this->get($id);
		if (!$appinfo) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "no id '$id'!");
			return false;
		}
		if ($appinfo['remote'] != 1) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no remote '$id'!");
			return false;
		}

		//获取版本与下载URL
		$vinfo = $this->checkAppDownloadVersion($appinfo);
		if ($vinfo['order'] != 1) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no order for id '$id'!");
			$ioparams['data'] = $vinfo;
			return false;
		}
		
		
		if (!$appinfo['local']) {
			$res = $this->downloadRemoteApp($vinfo);
			if (!$res) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "download remote app failed!", $appinfo);
				return false;
			}
		} 
		
		//install
		$res = $this->installApp($appinfo);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "install app failed!", $appinfo);
		}
		
		return $res;
	}
	
	
	protected function doUpgradeApp($appinfo)
	{
		//set installed=0
		$params = array();
		$params['id'] = $appinfo['id'];
		$params['version'] = $appinfop['version'];
		
		$res = $this->update($params);

		return $res;
	}
	
	
	public function upgradeFromRemote($id, &$ioparams=array())
	{
		$appinfo = $this->get($id);
		if (!$appinfo) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "no id '$id'!");
			return false;
		}

		$vinfo = $this->checkAppDownloadVersion($appinfo);
		if ($vinfo['order'] != 1) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no order for id '$id'!");
			$ioparams['data'] = $vinfo;
			return false;
		}

				
		$res = $this->downloadRemoteApp($vinfo);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "download remote app failed", $appinfo);
			return false;
		}
		
		$res = $this->doUpgradeApp($appinfo);
		
		return $res;
	}
	
	
	
	protected function doRemoveApp($appinfo)
	{
		$name = $appinfo['appname'];
		
		$dir = RPATH_APPS.DS.$name;
		$res = s_rmdir($dir);
		if (!$res) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "rmdir '$dir' failed!");
		}
		
		return true;
	}
	
	protected function doRemoveTpl($appinfo)
	{
		$name = $appinfo['appname'];
		
		$dir = RPATH_TEMPLATES.DS.$name;
		$res = s_rmdir($dir);
		if (!$res) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "rmdir '$dir' failed!");
		}
		
		return true;
	}
	
	//remove
	public function remove($id)
	{
		$appinfo = $this->get($id);
		if (!$appinfo) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "no id '$id'!");
			return false;
		}
		
		if ($appinfo['embeded']) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "app '$id' is embeded!");
			return false;
		}		
		
		if ($appinfo['installed']) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "app '$id' is installed");
			return false;
		}
		
		//仅本地未远程不能删除物理文件
		$res = false;
		$remote = intval($appinfo['remote']);
		if ($remote === 1) {//本地项目不删除		
			$type = $appinfo['type'];
			switch ($type) {
				case AT_RCTHE:
					break;
				case AT_RCTPL:
					$res = $this->doRemoveTpl($appinfo);		
					break;
				default:
					$res = $this->doRemoveApp($appinfo);				
					break;
			}
		}
		
		if ($res) {
			$res = $this->del($id);
			
			//clean cache 
			$ts = time() - 57;
			touch($this->_cacheappfile, $ts, $ts);
		}
				
		return $res;
	}

	protected function queryAppRemoteVersionList($id)
	{
		$appinfo = $this->get($id);
		if (!$appinfo) {
			rlog(RC_LOG_DEBUG, __FUNCTION__, "no app '$id'!");
			return false;
		}
		if ($appinfo['remote'] != 1) {
			rlog(RC_LOG_DEBUG, __FUNCTION__, "NOT REMOTE app '$id'!");
			return false;
		}

		$cf = get_config();
		$updatetype = intval($cf['updatetype']);
		if ($updatetype !== 1) {
			rlog(RC_LOG_DEBUG, __FUNCTION__, "Forbbiden APP Update!");
			return false;
		}
			
		$apiurl = $cf['updateapi'].'/getAppVersionList';				
		$params = get_sysinfo();	
		$params['appid'] = $appinfo['appid'];

		$data = requestSAPI($apiurl, array('params'=>$params));		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, 'data='.$data);
		
		$udb = array();
		if ($data) {
			$res2 = CJson::decode($data);
			if ($res2) {
				$udb = $res2['data'];			
			} else {
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "invalid data '$data'!apiurl=$apiurl", $params);
			}
		} 
		if (!$udb) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "no udb!", $udb, $data);
			return false;
		}

		return $udb;
	}


	public function getAppVersionList($id)
	{
		$vdb = array();
		$res = $this->queryAppRemoteVersionList($id);
		if ($res) {
			$vdb = $res;
		}

		return $vdb;
	}
}