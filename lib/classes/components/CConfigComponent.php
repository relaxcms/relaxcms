<?php

/**
 * @file
 *
 * @brief 
 *  系统配置
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class CConfigComponent extends CFileDTComponent
{
	protected $_bgdir;
	protected $_logodir;

	function __construct($name, $options)
	{
		parent::__construct($name, $options);
		$this->_bgdir  = RPATH_DATA.DS."bg";
		$this->_logodir  = RPATH_DATA.DS."logo";
	}
	
	function CConfigComponent($name, $options)
	{
		$this->__construct($name, $options);
	}
	
	protected function show(&$ioparams = array())
	{
		$params = get_config();
				
		$this->assign("stime", tformat_current());
		
		$this->assign('tplname_select', get_common_select('template', $params['tplname']));
		$this->assign('thename_select', get_common_select('theme', $params['thename']));
		$this->assignArray(ifcheck($params['enable_captcha'], "enable_captcha")); 
		$this->assignArray(ifcheck($params['enable_simple_layout'], "enable_simple_layout")); 	
		
		//title
		$params['title']  = isset($params['title'])? $params['title'] : i18n('str_system_title', "RC");
		
		//DATADIR
		(!isset($params['datadir']) || !$params['datadir']) && $params['datadir'] = str_replace(DS, '/', RPATH_DATA);
		//DATAURL
		(!isset($params['datauri']) || !$params['datauri']) && $params['datauri'] = $ioparams['_webroot'].'/data';
		
		
		//dbinfo
		$db = Factory::GetDBO();
		$options = $db->db_options();
		$params['dbhost'] = $options['dbhost'];
		$params['dbport'] = $options['dbport'];
		$params['dbuser'] = $options['dbuser'];
		$params['dbtype'] = $options['dbtype'];
		$params['dbname'] = $options['dbname'];
		$params['dbcharset'] = $options['dbcharset'];
		
		$this->assignArray(ifcheck($params['newalias'], "newalias")); 	

		
		//loglevel
		/*
		<option value="7">开启</option>
		<option value="6">关闭</option>
		*/
		
		$this->assign('loglevel_select', get_common_select('loglevel', $params['loglevel']));
		
		$this->assign('enable_captcha_select', get_common_select('enable_captcha', $params['enable_captcha']));
		$this->assign('savecookie_select', get_common_select('savecookie', $params['savecookie']));
		$this->assign('ajaxsystime_select', get_common_select('ajaxsystime', $params['ajaxsystime']));
		$this->assign('webtimer_select', get_common_select('webtimer', $params['webtimer']));
		$this->assign('apiaccess_select', get_common_select('enable', $params['apiaccess']));
		$this->assign('updatetype_select', get_common_select('enable', $params['updatetype']));
		
		$this->assign('proxyapi_select', get_common_select('enable', $params['proxyapi_enable']));
		
		//layout
		$this->assign('layout_select', get_common_select('layout', $params['layout']));
		//language
		$this->assign('language_select', get_common_select('language', $params['langname']));

		//safepwd
		$this->assign('safepwd_select', get_common_select('safepwd', $params['safepwd']));
		
		$apiurl = $ioparams['_weburl'].'/api';
		
		$this->assign('apiurl', $apiurl);
		!isset($params['apiurl']) && $params['apiurl'] = $apiurl;
		//xss_access
		$this->assign('xss_access_select', get_common_select('enable', $params['xss_access']));
		//seccodeonleynum
		$this->assign('seccodeonleynum_select', get_common_select('enable', $params['seccodeonleynum']));
		
		
		
		//local timer
		if (!isset($params['webtimer_request_api'])) {
			$apiurl = $ioparams['_weburl'].'/api';	
			$hostname = s_url2hostname($apiurl);
			$params['webtimer_request_api'] = str_replace($hostname, "127.0.0.1", $apiurl);
		}
		
		
		$this->assign('params', $params);
		
		
		return $params;	
		
	}
	
	
	protected function formatForFS($tdir, $name, $baseurl, &$fdb=array(), $format=false)
	{
		$id = $name;
		$ext = s_fileext($id);
		
		$item = array();
		
		$item['id'] = $id;
		$item['name'] = $name;
		$item['ctype'] = 1;
		$item['type'] = 4;
		$item['mimetype'] = CFileType::ext2mimetype($ext);	
		$item['url'] = $baseurl.'/'.$name;
		$item['lpreviewUrl'] = $item['url'];
				
		if ($format) {	
			$item['path'] = $tdir.DS.$name;
			$fdb[$id] = $item;
		} else {	
			$fdb[] = $item;
		}
		
	}
		
	protected function loadDir($type, $ioparams=array(), $format=false)
	{
		$id = 1;
		
		$tdir = RPATH_DATA.DS.$type;
		$baseurl = $ioparams['_dataroot'].'/'.$type;
		
		$fdb = array();
		if (($files  = s_readdir($tdir, "files"))) {			
			foreach ($files as $key => $value) {
				$this->formatForFS($tdir, $value, $baseurl, $fdb, $format);
			}
		}	
		return $fdb;
	}
	
	
	protected function loadLogo($ioparams=array(), $format=false)
	{
		$fdb = $this->loadDir('logo', $ioparams, $format);			
		return $fdb;
	}
	
	protected function loadBG($ioparams=array(), $format=false)
	{
		$fdb = $this->loadDir('bg', $ioparams, $format);			
		return $fdb;
	}
		
	/*	
	  [logo] => Array
	      (
	          [0] => 5
	      )
		
	*/
	
	protected function saveDir($type, $max, $width, $height, &$params, &$ioparams = array())
	{
		$fdb = $this->loadDir($type, $ioparams, true);
		$ndb = $params[$type];
		
		
		$ddb = array();
		foreach ($fdb as $key=>$v) {
			if (!$ndb || !in_array($key, $ndb))
				$ddb[$key] = $v;
		}
		
		foreach ($ddb as $key=>$v) {
			unset($fdb[$key]);
			unlink($v['path']);
		}	
		
		$nr = count($fdb);			
		if ($ndb) {
			$newdb = array();
			foreach ($ndb as $key=>$id)  {
				if (!isset($fdb[$id])) {
					$newdb[] = $id;
				}
			}
						
			$tdir = RPATH_DATA.DS.$type;		
			if (!is_dir($tdir))
				s_mkdir($tdir);
				
			$baseurl = $ioparams['_dataroot'].'/'.$type;
					
			$m = Factory::GetModel('file');			
			foreach ($newdb as $key=>$fid) {
				$finfo = $m->get($fid);
				if ($finfo) {
					$id = $nr+1;
					$filename = $id.'_'.$finfo['fileid'];
					$src = $finfo['opath'];
					$dst = $tdir.DS.$filename;
					$res = $m->resizeImage($src, $dst, $width, $height, $resizeinfo, true);
					if ($res) {
						$filename .= '.'.$resizeinfo['extname'];						
						$this->formatForFS($tdir, $filename, $baseurl, $fdb, true);	
						$nr ++;
						if ($nr >= $max)
							break;					
					}
				}
			}
		}
				
		return $fdb;
	}
	
	protected function saveLogo(&$params, $ioparams=array())
	{
		$fdb = $this->saveDir('logo', 1, 160, 68, $params, $ioparams, $format);		
		//rlog(RC_LOG_ERROR, __FILE__, __LINE__, $fdb);	exit;
		$logo = '';
		foreach ($fdb as $key=>$v) {
			$logo = $v['url'];
			break;
		}	
		$params['logo'] = $logo;
		return true;
	}
	
	protected function saveBG(&$params, $ioparams=array())
	{
		$fdb = $this->saveDir('bg', 3, 1920, 1080, $params, $ioparams, $format);			
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, $fdb);
		$params['bg'] = $fdb;
		
		return true;
	}
		
	
	protected function createAliasDir()
	{
		$params = get_config(true);
		
		//检查DATA
		$defaultrootdir = str_replace(DS, '/', RPATH_ROOT);
		if (strpos($params['datadir'], $defaultrootdir) !== false) { 
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "no need create web subdir!");
			return false;
		}
		
		//创建alias
		$fname = 'sd_'.md5($params['datauri']);
		$extdir = $params['vardir'].DS.'conf'.DS.'storage';
		$datadir = $params['datadir'];
		if (!is_dir($datadir))
			s_mkdir($datadir);
		
		if (!is_dir($extdir))
			s_mkdir($extdir);
		
		$cfgfile = $extdir.DS.$fname.'.conf';						
		createAliasDir($cfgfile, $params['datauri'], $params['datadir']);
		
		return false;
	}	
	
	protected function setWebTimer($apiurl)
	{
		$cf = get_config(true);
		$params = array();
		
		$params['timeout'] = intval($cf['webtimer']);
		$params['apiurl'] = $apiurl;
		$params['accesskey'] = $cf['accesskey'];
		
		$res = sapi_setwebtimer($params);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call sapi_setwebtimer failed!", $params);
		}
	}
	
	
	protected function edit(&$ioparams = array())
	{
		if ($this->_sbt) {
			if (!($res = $this->getParams($params))) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no params!");
				return false;
			}	
			
			foreach ($params as $key=>&$v) {
				if (is_string($v))
					$v = trim($v);				
			}
			
			//检查登录背景
			$this->saveBG($params, $ioparams);
			//检查logo
			$this->saveLogo($params, $ioparams);
			
			$params['hash'] = md5('rc1@3$_'.$params['cookie']);
			!$params['thename'] && $params['thename'] ='default';
			!$params['tplname'] && $params['tplname'] = 'default';
			
			//appId, appSecret
			$manager = get_manager();
			$m = Factory::GetModel('user');
			$userinfo = $m->getOne(array('name'=>$manager['manager']));
			if ($userinfo) {
				if ($params['apiaccess']) {
					$updatetoken = $this->requestInt('updatetoken', 0);
					$appinfo = $m->createToken($userinfo['id'], $updatetoken);
					$params['apiAccessKey'] = $appinfo['token'];
					$params['apiAccessSecret'] = $appinfo['secret'];
				} else {
					$res = $m->deleteToken($userinfo['id']);
					$params['apiAccessKey'] = '';
					$params['apiAccessSecret'] = '';
				}
			}
			
			set_config($params, false);						
			if (isset($params['loglevel'])) {
				$logcfg = array();
				$logcfg['loglevel'] = $params['loglevel'];
				$l = Factory::GetLog();
				$l->set_logcfg($logcfg);
			}
			
			//local timer
			if (isset($params['webtimer_request_api'])) {
				$apiurl = $params['webtimer_request_api'];				
			} else {
				$apiurl = $ioparams['_weburl'].'/api';	
				$hostname = s_url2hostname($apiurl);
				$apiurl = str_replace($hostname, "127.0.0.1", $apiurl);
			}
			$this->setWebTimer($apiurl);
			
			//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, 'update system config', $apiurl);	
			
			//检查datadir
			$newalias = intval($_REQUEST['newalias']);
			if ($newalias)
				$this->createAliasDir();

			showStatus(0);
		}
	}

	protected function fileselectorForSelected(&$ioparams=array())
	{
		$mid = $this->requestInt('mid');
		if ($mid == 1) {
			$res = $this->loadBG($ioparams);
		} else {
			$res = $this->loadLogo($ioparams);
		}
		showStatus(0, $res);
	}
}

?>