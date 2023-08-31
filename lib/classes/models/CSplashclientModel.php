<?php

/**
 * @file
 *
 * @brief 
 * 
 * 画报模型
 *
 */

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class CSplashClientModel extends CNoTableModel
{
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);
	}
	
	public function CSplashClientModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}	
	
	/*
	{
		"status": 0,
		"data": [
			{
				"id": "28",
				"url": "http:\/\/localhost\/rc7\/data\/1\/202207\/28_0a14cb03c648992d2f5581f14a4aa3ae.jpg",
				"mimetype": "image\/jpeg"
			},
			{
				"id": "26",
				"url": "http:\/\/localhost\/rc7\/data\/1\/202207\/26_1eaeba5d1b6cd9cea2189cdf72d78765.jpg",
				"mimetype": "image\/jpeg"
			},
			{
				"id": "27",
				"url": "http:\/\/localhost\/rc7\/data\/1\/202207\/27_d9c60af72c18488cb22530e346d96fc8.jpg",
				"mimetype": "image\/jpeg"
			}
		]
	}
	*/
	
	protected function loadBG($bgdir)
	{
		$id = 1;
		
		$fdb = array();
		if (($files  = s_readdir($bgdir, "files"))) {			
			foreach ($files as $key => $name) {
				$item = array();
				$item['path'] = $bgdir.DS.$name;
				$item['exists'] = true;
				$fdb[$name] = $item;
			}
		}	
		return $fdb;
	}
	
	
	//
	protected function loadSplashCacheInfo()
	{
		return cache_array('splashdb');
	}
	
	protected function saveSplashCache($sdb)
	{
		$sdb['ts'] = time();
		return cache_array('splashdb', $sdb);
	}
	
	
	protected function downloadDesktopBackground($bdb, $bgdir, $force)
	{
		//检查更新频率
		$oldsplashdb = $this->loadSplashCacheInfo();
		$oldts = isset($oldsplashdb['ts'])?$oldsplashdb['ts']:0;
		
		if (!$force && time() - $oldts < RC_TIMESEC_DAY) { //每天请求1次
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "not at time of splash!");
			return false;
		}
		
		if (!is_array($bdb))
			$bdb = array();
			
		$fdb = $this->loadBG($bgdir);
		foreach ($bdb as $key=>$v) {
			$url = $v['url'];
			$name = s_uri2filename($url);			
			if ($fdb[$name]) 
				continue;
			
			$fdb[$name]['exists'] = false;
						
			$dst = $bgdir.DS.$name;
			$data = curlGET($url);
			if (!$data) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call curlGET failed!url=$url");
				continue;
			}
			
			$res = s_write($dst, $data);
			if (!$res) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call s_write failed!dst=$dst");
			}
		}
		
		foreach($fdb as $key=>$v) {
			if (!$v['exists']){
				@unlink($v['path']);
			}
		}
		
		$res = $this->saveSplashCache($bdb);
		
		return $res;
	}
	
	
	
	public function updateDesktopBackground($bgdir='', $force=false)
	{
		!$bgdir && $bgdir = RPATH_CONFIG.DS.'bg';
		
		$cf = get_config();	
		//检查 updatetype
		$updatetype = intval($cf['updatetype']);
		if ($updatetype === 0) { //关闭
			return false;
		}
		
		$url = $cf['updateapi'].'/getDesktopBackground';
		$params = get_sysinfo();				
		$res = curlPOST($url, array('params'=>$params));
		if ($res) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, $res);
			$res2 = CJson::decode($res);
			$this->downloadDesktopBackground($res2['data'], $bgdir, $force);			
		}
		
		return $res;		
	}
}
