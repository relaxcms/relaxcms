<?php
/**
 * @file
 *
 * @brief 
 * TVBOX管理
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class WebclientModel extends CTerminalModel
{
	public function __construct($name, $options=array())
	{
		$options['modname'] = 'terminal';
		parent::__construct($name, $options);
	}
	
	public function WebclientModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}
	
	public function getInfoByID($webid, &$ioparams=array())
	{
		$_client = $ioparams['_client'];
		$_useragent = $ioparams['_useragent'];
				
		$tid = md5($webid);
		$tinfo = $this->getBy("where tid='$tid'");
		if (!$tinfo) {
			
			$m = Factory::GetModel('tmconfig');
			if (!method_exists($m, 'getConfig'))
				return false;
				
			$tmcfg = $m->getConfig();
			if ($tmcfg['savewebclient'] != 1) {
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "WARNING:no save webclient");
				return false;
			}
						
			$tinfo = array();
			$tinfo['tid'] = $tid;
			$tinfo['type'] = 2;
			$tinfo['name'] = "WEB".$_client;		
			$tinfo['systeminfo'] = $webid;
			
			$tinfo['ip'] = $_client;
			$tinfo['online'] = 1; //在线		
			$tinfo['last_access_time'] = time();
			
			$res = $this->set($tinfo);
			if (!$res) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call set failed!",$tinfo);
			}
		} else { 
			$tinfo['ip'] = $_client;
			$tinfo['last_access_time'] = time();
			$tinfo['last_access_id'] = 0; //最后访问
			$tinfo['online'] = 1; //在线
			$res = $this->set($tinfo);
			if (!$res) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call set failed!", $tinfo);
			}			
		}	
		
		return $tinfo;
	}
		
}
