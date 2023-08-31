<?php
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class CSystemConfig extends CConfig
{
	//构造
	public function __construct($name, $options= array())
	{
		parent::__construct($name, $options);
	}	

	function CSystemConfig($name, $options= array()) 
	{
		$this->__construct($name, $options);
	}
	
	protected function encrypt()
	{
		return false;
	}
	
	public function load($reload=false)
	{
		$cfg = parent::load($reload);
		
		!isset($cfg['title']) &&  $cfg['title'] = 'RC';				
		!isset($cfg['dbtype']) &&  $cfg['dbtype'] = 'mysql';		
		
		!isset($cfg['log_path']) &&  $cfg['log_path'] = str_replace(DS, '/', RPATH_CACHE);
		!isset($cfg['loglevel']) && $cfg['loglevel'] = LOG_DEBUG;
		!isset($cfg['hash']) && $cfg['hash'] = "rc!!@@##";
		!isset($cfg['timediff']) && $cfg['timediff'] = 8;
		!isset($cfg['timeformat']) && $cfg['timeformat'] = "Y-m-d h:i:s";
		!isset($cfg['timezone']) && $cfg['timezone'] = "Asia/Shanghai";
		!isset($cfg['count']) && $cfg['count'] =12;
		!isset($cfg['page_size']) && $cfg['page_size'] =12;
		!isset($cfg['cookie']) && $cfg['cookie'] = "r!@#$";
		!isset($cfg['ckdomain']) && $cfg['ckdomain'] = "";
		!isset($cfg['ckpath']) && $cfg['ckpath'] = "/";
		!isset($cfg['ckadmin']) && $cfg['ckadmin'] = 0;
		!isset($cfg['max_loginfails']) && $cfg['max_loginfails'] = 15;
		!isset($cfg['hkey_timeout']) && $cfg['hkey_timeout'] = 300; //300s		
		!isset($cfg['last_count']) && $cfg['last_count'] =6;
		!isset($cfg['catalog_count']) && $cfg['catalog_count'] =6;
		
		
		!isset($cfg['safepwd']) && $cfg['safepwd'] = false;
		!isset($cfg['min_passwd_length']) && $cfg['min_passwd_length'] = 6;
		!isset($cfg['session_timeout']) && $cfg['session_timeout'] = 1800;
		!isset($cfg['login_failure_times']) && $cfg['login_failure_times'] = 5;
		!isset($cfg['login_fail_lock']) && $cfg['login_fail_lock'] = 30;
		!isset($cfg['login_failure_release_lock_time']) && $cfg['login_failure_release_lock_time'] = 300;
		
		//enable or disable captcha
		!isset($cfg['enable_captcha']) && $cfg['enable_captcha'] = true;
		!isset($cfg['enable_deleteall']) && $cfg['enable_deleteall'] = false;
		!isset($cfg['savecookie']) && $cfg['savecookie'] = 1;
		!isset($cfg['ajaxsystime']) && $cfg['ajaxsystime'] = 0;
		//验证码超时		
		!isset($cfg['seccodetimeout']) && $cfg['seccodetimeout'] = 300;
				
		//! 默认模板名称
		!isset($cfg['tplname']) && $cfg['tplname'] =  'default';
		!isset($cfg['layout']) && $cfg['layout'] =  'default';
		!isset($cfg['enable_simple_layout']) && $cfg['enable_simple_layout'] = true;
		//! 默认主题名称
		!isset($cfg['thename']) && $cfg['thename'] =  'default';
		//! 默认语言包名称
		if (isset($_COOKIE['langname']))
			$cfg['lang'] = $_COOKIE['langname'];
		!isset($cfg['lang']) && $cfg['lang'] = 'zh_CN';
		
		//! 三权分立
		$cfg['checksandbalance'] = true;
		
		//accesskey
		if (!isset($cfg['accesskey'])) {
			$cfg['accesskey'] = get_hashaccesskey($cfg['hash']);
		}
		!isset($cfg['ckey']) && $cfg['ckey'] = $cfg['accesskey'];
		
		//DOCUMENT_ROOT
		$document_root = str_replace(DS, '/', $_SERVER['DOCUMENT_ROOT']);
		empty($cfg['docrootdir']) && $cfg['docrootdir'] = $document_root;
		empty($cfg['homedir']) && $cfg['homedir'] = substr($document_root, 0, strpos($document_root, "/var"));
		$cfg['vardir'] = $cfg['homedir']."/var";

		//update API
		empty($cfg['apiaccess']) && $cfg['apiaccess'] = 0;
		empty($cfg['updateapi']) && $cfg['updateapi'] = 'http://localhost/api';
		
		return $cfg;
	}
	
	public function save($cfgdb, $over=false)
	{
		//update accesskey
		$cfgdb['accesskey'] = get_hashaccesskey($cfgdb['hash']);
		
		
		$res = parent::save($cfgdb, $over);
		return $res;
	}
}
