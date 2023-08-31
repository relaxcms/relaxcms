<?php
/**
 * @file
 *
 * @brief 
 * ÈÕÖ¾
 *
 * Copyright (c), 2022, relaxcms.com
 */

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class CLog
{
	protected $_initialized = false;
	protected $_logcfg = array();
	
	protected $_format = "{DATE}\t{TIME}\t{LEVEL}\t{C-IP}\t{STATUS}\t{COMMENT}";

	public function __construct()
	{
	}
	
	public function CLog()
	{
		$this->__construct();
	}
	
	static function &GetInstance()
	{
		static $instance;		
		if (!is_object($instance)) {			
			$instance = new CLog();
		}
		return $instance;
	}
		
	public function set_logcfg($logcfg)
	{
		$cache ="<?php\n";		
		foreach($logcfg as $key=>$v) {
			$cache.="\$$key='$v';\n";
		}		
		$cache .= "?>";		
		s_write(RPATH_CONFIG.DS.'logcfg.php',  $cache);
	}
	
	
	protected function _init()
	{
		$path = RPATH_CONFIG.DS."logcfg.php";
		if (file_exists($path)) {
			require_once($path);
			$cfg['loglevel'] = $loglevel;
		}		
		if (isset($cfg)) 
			$this->_logcfg = $cfg;	
		
		$this->_initialized = true;
	}
	
	public function get_logcfg()
	{
		if (!$this->_initialized)
			$this->_init();
			
		return $this->_logcfg;
	}
	public function set_loglevel($loglevel)
	{
		if (!$this->_initialized)
			$this->_init();
		$this->_logcfg['loglevel'] = $loglevel;
	}
	
	public function slog($loglevel, $message, $cmd='', $object='', $oid=0)
	{
		$r = Factory::GetRequest();
		$message = i18n($message);
		$db = Factory::GetDBO();
		$app = Factory::GetApp();
		if ($app) {
			$subsys = $app->getAppName();
		} else {
			$subsys = "";
		}
		
		$userinfo = get_userinfo();
		if (!isset($userinfo['uid']))
			return false;
		
		$uid = $userinfo['uid'];
		
		$client = get_client_ip();
		
		if ($db) {			
			$cmd = addslashes($cmd);
			$sql = "insert into cms_log(ts, ip, des, uid, subsys, loglevel, cmd, object, oid) values";
			$sql .= "(unix_timestamp(), '$client', '$message', '$uid', '$subsys', $loglevel, '$cmd', '$object', $oid)";			
			@$db->exec($sql);
		} else {
			//rlog($message);
		}
	}
}
