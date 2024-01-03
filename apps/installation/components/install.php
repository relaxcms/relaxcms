<?php
/**
 * @file
 *
 * @brief 
 * 安装组件
 *
 * Copyright (c), 2023, relaxcms.com
 */


class InstallComponent extends CUIComponent
{
	function __construct($name, $options)
	{
		parent::__construct($name, $options);
	}
	
	function InstallComponent($name, $options)
	{
		$this->__construct($name, $options);
	}
	
	protected function probReleaseParams(&$params)
	{
		//updateapi
		if (file_exists(RPATH_ROOT.DS."RELAXCMS.txt")) {
			$params['updateapi'] = 'https://www.relaxcms.com/api';
		}
	}
	
	public function show(&$ioparams=array())
	{
		
		//welcome
		$desc = s_read(RPATH_DOCUMENT.DS.'welcome.txt');
		$desc = str_replace("\n", "<p>", $desc);
	
		//license
		$_license= s_read(RPATH_DOCUMENT.DS.'license.txt');
		$_license = str_replace("\n", "<br>", $_license);	
		
		//SYSTEM
		$params = get_config();		

		$params['description'] = $desc;
		$params['license'] = $_license;	
		$params['product_name'] = "RC";
		
		//
		$params['product_id'] = get_product_id();
		$params['product_version'] = get_product_version();
		
		//DB
		$dbcfg = get_dbconfig($params['dbtype']);	
		$params = array_merge($params, $dbcfg);
		
		//manager
		$manager = get_manager();	
		$params = array_merge($params, $manager);
		
		$params['create_dbuser'] = 0;
		$params['exists_rewrite'] = 0;
		$params['install_sample'] = 1;
		
		$this->assignArray(ifcheck($params['create_dbuser'], "create_dbuser")); 		
		$this->assignArray(ifcheck($params['exists_rewrite'], "exists_rewrite")); 		
		$this->assignArray(ifcheck($params['install_sample'], "install_sample")); 	
			
		$this->assign("dbtype_select", get_common_select('dbtype', $params['dbtype']));
		$this->assign("dbcharset_select", get_common_select('dbcharset', $params['dbcharset']));
		
		//release params
		$this->probReleaseParams($params);
		
		//默认应用
		//$this->set_var("checks", ifcheck($params['site_enable'], "site_enable")); 
		$this->assign("params", $params);
		
		$apps = $this->probeApps();
		$this->assign('apps', $apps);
		
		
				
		return true;
	}
	
	protected function checkDBSupport()
	{
		$params = array(
				'dbo'=>'PDO',
				'mysql'=>'mysql_connect',
				'mysqli'=>'mysqli_connect',
				'mssql'=>'sqlsrv_connect', 
				'sqlite'=>'SQLite3', 
				'psql'=>'pg_connect',
				'mongo'=>'MongoClient');
				
		
		$res = array();		
		foreach ($params as $key=>$v) {
			if (function_exists($v) || class_exists($v)){
				$res[$key] = true;
			}
		}	
		return $res;
	}
			
	protected function __check(&$res=array()) 
	{
		$lang = Factory::GetLanguage();
		
		$str_readwrite = i18n('str_readwrite'); 
		$str_read = i18n('str_read'); 
		$str_write = i18n('str_write'); 
		
		$test_failed = '<font color=red>'.$lang['str_777_test'].'</font>';
		$no_file = '<font color=red>'.$lang['str_no_file'].'</font>';
		
		$yes = $lang['str_yes']; 
		$no = $lang['str_no'];
		
		$support = '<font color=green>'.i18n('str_support').'</font>';
		$unsupport = '<font color=red>'.i18n('str_not_support').'</font>';
		
		$w_check = array('cache', 'public'.DS.'data', 'config');
		
		$udb = array();
		$item = array();
		$pass = true;
		$id = 1;

		foreach($w_check as $key=>$v)
		{
			$item ['check'] = $v;
			$item ['recommend'] = $str_readwrite;
			$item['id'] = $id ++;
						
			if (!file_exists(RPATH_ROOT.DS.$v)) {
				$item ['status'] = $no_file;
				$item['pass'] = $pass = false;
			} else {
				$is_read = is_readable(RPATH_ROOT.DS.$v);
				$is_write = is_writable(RPATH_ROOT.DS.$v);

				if ( $is_read && $is_write) {
					$item ['status'] = '<font color=green>'.$str_readwrite.'</font>';
					$item['pass'] = true;
				}  else {
					$item['pass'] = $pass = false;
					if ($is_read) {
						$item ['status'] = '<font color=red>'.$str_read.'</font>';
					} else if ($is_write) {
						$item ['status'] = '<font color=red>'.$str_write.'</font>';
					} else {
						$item ['status'] =$test_failed;
					}
				}
			}
			
			$udb[] =  $item;
		}
		
		
		//check
		$supports_check = array('SQL'=>'sql', 
			'Socket'=>'socket_create', 'SSL'=>'openssl_open', 'curl'=>'curl_exec');

		foreach($supports_check as $key=>$v)
		{
			$item ['check'] = $key;
			$item ['recommend'] = $support;
			$item['id'] = $id ++;
			
			if (function_exists($v) || ($v == 'sql' && $this->checkDBSupport()))
			{
				$item['status'] = $yes;
				$item['pass'] = true;
			}
			else
			{
				$item['status'] = $no;
				$item['pass'] = $pass = false;				
			}
			

			$udb[] =  $item;
		}

		
		$res = $udb;
		$this->assign("udb", $udb);
		
		return $pass;
	}
	
	//检查
	protected function check(&$ioparams=array(), $params = array())
	{
		$this->_template = "install-check";		
		$this->init_menu();
		
		if (!$params) {
			$params = array();
			$this->getParams($params);
		}
		
		$pass = $this->__check();
		$ioparams['nexttask'] = 'check';
		//var_dump($pass);
		if ($params['current'] == 'check') {
			if ($this->_sbt) {
				if ($pass) { //过
					return $this->config($ioparams, $params);				
				}
			}
		}
	}	

	protected function checkenv(&$ioparams=array())
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "in...");

		$data = array();
		$pass = $this->__check($res);

		//版本
		$data['cdb'] = $res;

		

		
		showStatus($pass, $data);

		return true;
	}

	private function _get_plugininfo($dir)
	{
		$file = RPATH_PLUGIN.DS.$dir.DS.'config.php';
		if (file_exists($file)) {
			require $file;
			return $plugins;
		}
		
		return false;
	}
	
	//输入配置信息有效性检查
	protected function __check_config($params) 
	{
		//有效性检查
		if ($params['dbhost'] == "" ||
				$params['dbuser'] == "" ||
				$params['dbname'] == "")
		{
			set_error("str_database_parameter_error");		
			return false;
		}
		
		if (!$params["manager_pwd"])
		{
			set_error("str_manager_pwd_empty");
			return false;				
		}
		
		if ($params["manager_pwd"] != $params["manager_pwd2"])
		{
			set_error("str_manager_pwd_error");
			return false;
		}
		
		$dbname = $params['dbname'];
		$params['dbname'] = "";
		
		$create_dbuser = $params['create_dbuser']; 
		
		//是否要创建用户
		if ($create_dbuser && $params['dbuser'] && $params['dbuser'] != 'root') {
			$tmp = $params;
			$tmp['dbuser'] = 'root';
			$tmp['dbpassword'] = $params['dbroot_password'];
			$tmp['dbname'] = 'mysql';
			
			//连接
			$db = Factory::GetDBO($params['dbtype'], $tmp);
			if (!$db || !$db->is_connected())
			{
				set_error("str_database_connect_error_or_root_password_error");		
				return false;
			}
			
			//创建用户
			$dbuser = $params['dbuser'];
			$dbpassword = $params['dbpassword'];
			
			//查一下用户是否存在
			$sql = "select 1 from user where user='$dbuser'";
			if ($db->exists($sql)) {
				set_error('str_install_dbuser_exists');
				return false;				
			}	
			$db->close();			
		}
		
		//重置root.
		if ($params['dbroot_password_reset'] && $params['dbroot_password_reset'] == $params['dbroot_password_reset2']) {
			$tmp = $params;
			$tmp['dbuser'] = 'root';
			$tmp['dbpassword'] = $params['dbroot_password'];
			$tmp['dbname'] = 'mysql';
			
			//连接
			$db = Factory::GetDBO($params['dbtype'], $tmp);
			if (!$db || !$db->is_connected())
			{
				set_error("str_database_connect_error_or_root_password_error");		
				return false;
			}
			
			$db->close();
		}
		
		
		$db = Factory::GetDBO($params['dbtype'], $params);
		if (!$db || !$db->is_connected())
		{
			set_error("str_database_connect_error");		
			return false;
		}
		
		return true;
	}
	
	
	protected function config(&$ioparams = array(), $params = array())
	{
		
		
	}
	
	protected function probePlugins()
	{
		$plugins = Factory::GetPlugins();
		
		//编历目录
		$dir = RPATH_PLUGIN;
		$udb = s_readdir($dir);
		
		$hdb = array('.svn');
		
		$tdb = array();
		if (!is_array($udb))
			$udb = array();
		
		foreach ($udb as $key=>$v) {
			if (in_array($v, $hdb))
				continue;
			
			$item = $this->_get_plugininfo($v);
			$item['dirname'] = $v;
			if (array_key_exists($v, $plugins)) {
				$item['checked'] = 'checked';
				if ($plugins[$v]['readonly'])
					$item['readonly'] = 'readonly';
			}
			
			
			$tdb[$key] = $item;
		}
		
		return $tdb;
		
	}
	
	private function getAppInfo($dir)
	{
		$file = RPATH_APPS.DS.$dir.DS.'config.php';
		if (file_exists($file)) {
			require $file;
			if(isset($appcfg))
				return $appcfg;
		}
		
		return false;
	}
	
	protected function probeApps()
	{
		$apps = array();
		
		//编历目录
		$dir = RPATH_APPS;
		$udb = s_readdir($dir);
		
		$hdb = array('.svn');
		
		$tdb = array();
		if (!is_array($udb))
			$udb = array();
		
		$id = 1;
		foreach ($udb as $key=>$v) {
			if (in_array($v, $hdb))
				continue;
			
			$item = $this->getAppInfo($v);
			if (!$item)
				continue;
			
			//name
			$item['id'] = $id ++;
			$item['dirname'] = $v;
			if (!isset($item['appname']))
				$item['appname'] = $v;
			if (isset($item['embeded']) && $item['embeded'])
				continue;
				
			$item['checked'] = 'checked';
			$item['readonly'] = '';
			
			if (array_key_exists($v, $apps)) {
				$item['checked'] = 'checked';
				if ($apps[$v]['readonly'])
					$item['readonly'] = 'readonly';
			}
			
			$tdb[$item['appname']] = $item;
		}
		
		return $tdb;
		
	}
	
	
	private function installApps($apps, $ioparams=array())
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "IN _install_apps");
		
		$allapps = $this->probeApps();
		
		if (!is_array($apps))
			$apps = array();
			
		//rlog($apps);
		$mainapp = Factory::GetApp('system');
		$mainapp->install($ioparams);
		
		$installapps = array();			
		foreach ($apps as $key=>$name) {
			$app = Factory::GetApp($name);
			if (!$app) {
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "unknown app '$name'!");
			} else {
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "install app '$name'...");
				if (($res = $app->install($ioparams))) {					
					if (isset($allapps[$name]))
						$installapps[$name] = $allapps[$name];
				} else {
					rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "install app '$name' failed!");
				}
			}
		}		
		
		//cache
		cache_apps($installapps);
		$mainapp->cache();		
				
		//清理models
		//@unlink(RPATH_CACHE.DS.'models.php');
		
		//rlog(RC_LOG_ERROR, __FILE__, __LINE__, "OUT _install_apps");			
		
		return true;
	}	
	
	
	protected function install(&$ioparams=array())
	{
		$this->getParams($params);
		
		$dbconfig["dbtype"]	= $params["dbtype"];
		$dbconfig["dbhost"]	= $params["dbhost"];
		$dbconfig["dbport"]	= isset($params["dbport"])?$params["dbport"]:'';
		$dbconfig["dbuser"]	= $params["dbuser"];
		$dbconfig["dbpassword"] = $params["dbpassword"];
		$dbconfig["dbname"]	 = $params["dbname"];
		$dbconfig["dbcharset"]	 = $params["dbcharset"];
		$dbconfig["prefix"]	 = $params["prefix"];
		
		set_default_dbconfig($dbconfig, $params["dbtype"], true);

		//默认连接数据库类型
		$cookie = randstr();				
		$syscfg['dbtype'] = $params["dbtype"];
		$syscfg['hash'] = substr(md5($cookie.time()), 3, 8);
		$syscfg['timediff'] = "8";
		$syscfg['timeformat'] = "Y-m-d H:i:s";
		$syscfg['timezone'] = "shanghai";
		$syscfg['count'] = 20;
		$syscfg['cookie'] = $cookie;
		$syscfg['title'] = isset($params['product_name'])?$params['product_name']:'';
		$syscfg['product_name'] = $params['product_name'];
		$syscfg['updatetype'] = $params['updatetype'];
		$syscfg['updateapi'] = $params['updateapi'];
		
		set_config($syscfg, true);
		$cf = get_config(true);
		
		//管理员配置				
		$manager["manager"] = $params["manager"];
		$manager["manager_pwd"] = $params["manager_pwd"];
		$manager["manager_email"] = $params["manager_email"];
		
		//rlog($manager);
		
		set_manager($manager, true);
		
		$dbname = $params['dbname'];
		///$params['dbname'] = "";
		$newdbuser = 0;
		$exists_rewrite = 0;
		if (isset($params['newdbuser']))
			$newdbuser = intval($params['newdbuser']);
		if (isset($params['exists_rewrite']))
			$exists_rewrite = intval($params['exists_rewrite']);
		
		//是否要创建用户
		if ($newdbuser && $params['dbuser'] && $params['dbuser'] != 'root') {
			$tmp = $params;
			$tmp['dbuser'] = 'root';
			$tmp['dbpassword'] = trim($params['dbroot_password']);
			$tmp['dbname'] = 'mysql';
			$tmp['dbhost'] = $params['dbhost'];

			//连接
			$db = Factory::GetDBO($params['dbtype'], $tmp);
			if (!$db->is_connected()) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "connect database as root failed!");		
				return false;
			}
						
			//创建用户
			$dbuser = $params['dbuser'];
			$dbpassword = $params['dbpassword'];
			
			//库不存在，创建库
			$res = $db->db_create($dbname);
			
			//查一下用户是否存在
			$res = $db->createUser($params);
			/*$sql = "select 1 from user where user='$dbuser'";
			if ($db->exists($sql)) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, 'str_install_dbuser_exists');
				return false;				
			}
			
			$sql = "GRANT ALL PRIVILEGES ON $dbname.* TO $dbuser@localhost IDENTIFIED BY '$dbpassword'";
			$db->exec($sql);
			$sql = "FLUSH PRIVILEGES";
			$db->exec($sql);*/						
		}
		
		//重置root.
		if ($params['dbroot_password_reset'] && $params['dbroot_password_reset'] == $params['dbroot_password_reset2']) {
			$tmp = $params;
			$tmp['dbuser'] = 'root';
			$tmp['dbpassword'] = $params['dbroot_password'];
			$tmp['dbname'] = 'mysql';
			
			//连接
			$db = Factory::GetDBO($params['dbtype'], $tmp);
			if (!$db->is_connected()) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "connect database as root failed!");		
				return false;
			}
			
			$dbroot_password = $params['dbroot_password_reset'];			
			//空重置root
			$db->changePassword('root', $dbpassword);
			$db->close();
		}
		
		//
		$_params = $params;
		//$_params['dbname'] = 'mysql';
		$db = Factory::GetDBO($_params['dbtype'], $_params);
		if (!$db->is_connected()) {
			$res = $db->reconnect('mysql');
			if (!$res) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no db connected!", $_params);		
				return false;
			}
		}
		
		if (!$db->db_exists($dbname)) {
			$res = $db->db_create($dbname);			
			if (!$res) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "create db '$dbname' failed!");	
				return false;
			}
			$createdb_flag = true;
		}
		else
		{
			if ($exists_rewrite == 1) {
				$db->db_drop($dbname);	
				$res = $db->db_create($dbname);	
				if (!$res) {
					rlog(RC_LOG_ERROR, __FILE__, __LINE__, "create db '$dbname' failed!");	
					return false;
				}
				
				$createdb_flag = true;
			}
		}
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "install IN3...");
		$db = Factory::GetDBO($params['dbtype'], $params);
		
		$res = $db->db_select($dbname);
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "install IN4...");
		$udb = array();		
		$udb['check_database'] = "<font color='green'>完成</font>";
		
		//创建基本表
		/*$sql = RPATH_DATABASE.DS."sql".DS.$params['dbtype'].DS."create_table.sql";
		if (!$db->exec_script($sql))  {
			$udb['database_create_table'] = "<font color='red'>失败</font>";
		}  else {
			$udb['database_create_table'] = "<font color='green'>完成</font>";
		}
		
		//存储过程 database_create_procedure
		$udb['database_create_procedure'] = "<font color='green'>完成</font>";
		
		//初始化
		$sql = RPATH_DATABASE.DS."sql".DS.$params['dbtype'].DS."init_table.sql";		
		if (file_exists($sql) && !$db->exec_script($sql) ) {
			$udb['database_init_table'] = "<font color='red'>失败</font>";
		} else {
			$udb['database_init_table'] = "<font color='green'>完成</font>";
		}*/
		
		
		$this->assign("udb", $udb);
		
		//cache_var();
		
		//unlink(RPATH_CACHE.DS."i18n.php");
		
		$res = $this->installApps($params['app'], $ioparams);
		
		$ioparams['nexttask'] =  'install';
			
		return $res;
	}
	
	protected function finished(&$ioparams = array() )
	{
		$r = Factory::GetRequest();
		
		$adminurl = $r->weburl();			
		header('location:'.$adminurl);				
		touch(RPATH_CONFIG.DS.'installed');
	}
	
	protected function setHtaccess($ioparams)
	{
		$tpl_htaccess = <<<EOT
<IfModule mod_rewrite.c>
  RewriteEngine on
  RewriteBase /%s
 
  #sapi
  RewriteRule ^sapi(.*)$ /cgi-bin/sapi.cgi/api/$1 [NC,PT,L]

  RewriteRule ^f/(.*)$ system.php/f/$1 [NC,PT,L]
  RewriteRule ^file/(.*)$ front.php/file/$1 [NC,PT,L]
  RewriteRule ^list/(.*)$ front.php?c=list&id=$1 [NC,PT,L]
  RewriteRule ^content/(.*)$ front.php?c=content&id=$1 [NC,PT,L]
  RewriteRule ^api(.*)$ system.php/api/$1 [NC,PT,L]

  RewriteRule ^admin(.*)$ system.php/$1 [NC,PT,L]

	RewriteCond %%{REQUEST_FILENAME} !-d
	RewriteCond %%{REQUEST_FILENAME} !-f
	RewriteRule ^(.*)$ index.php?s=$1 [QSA,PT,L]
  
</IfModule>
EOT;
		$webroot = $ioparams['_webroot'];
		
		$data = sprintf($tpl_htaccess, $webroot);
		$res = s_write(RPATH_PUBLIC.DS.'.htaccess', $data);
		
		return $res;
	}

	protected function setup(&$ioparams = array())
	{
		$res = $this->install($ioparams);
		if ($res) {
			//创建 .htaccess
			$this->setHtaccess($ioparams);
			
			touch(RPATH_CONFIG.DS.'installed');
		}		
		showStatus($res);
	}
	
}