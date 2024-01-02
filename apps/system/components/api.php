<?php

/**
 * @file
 *
 * @brief 
 * ÆðÊ¼Ò³
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class ApiComponent extends CUIComponent
{
	function __construct($name, $options=null)
	{
		parent::__construct($name, $options);
	}
	
	function ApiComponent($name, $options=null)
	{
		$this->__construct($name, $options);
	}
	
	
	protected function redirectForAPI(&$ioparams=array())
	{
		$cf = get_config();
		$pos = strpos($ioparams['_uri'], '/api/');
		if ($pos === false)
			return false;
		
		$api = substr($ioparams['_uri'], $pos + 5);		
		$proxyapi = s_slashify($cf['proxyapi']).$api; //'https://www.wwwc.store:40443/'.$api;;		
		
		$res = curlProxy($proxyapi);
		
		return $res;
	}
		
	public function show(&$ioparams=array())
	{
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "TODO...");
		
		//´¦ÀíÖØ¶¨Ïò
		$cf = get_config();
		if ($cf['proxyapi_enable']) {
			$this->redirectForAPI($ioparams);
		}
		
		showStatus(-1);
	}
	
	protected function helloapi(&$ioparams=array())
	{
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "TODO...");
		showStatus(-1);
	}
	
	protected function todoForNew(&$ioparams=array())
	{
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "TODO todoForNew...");
		showStatus(-1);
	}
	
	protected function localwebservice(&$ioparams=array())
	{
		//LSSID
		$is_lssid = false;
		if (isset($_COOKIE['LSSID'])) {
			$LSSID = $_COOKIE['LSSID'];
			$is_lssid = check_lssid($LSSID);
		}
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__,'$is_lssid='.$is_lssid);		
		if (!$is_lssid) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "Invalid client '".$ioparams['_client']."'");
			exit("forbidden");
		}
		
		$l = Factory::GetLog();
		//$l->set_loglevel(1);
		
		$apps = Factory::GetApps();
		foreach ($apps  as $key=>$v) {
			$app = Factory::GetApp($key);
			$app->localwebservice($ioparams);
		}
		
		Factory::GetApp()->localwebservice($ioparams);
		showStatus(0);
	}

	protected function getToken(&$ioparams=array())
	{
		$params = $_POST;		
		$m = Factory::GetModel('user');
		$res = $m->getToken($params);
		
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "getToken", $params, 'res', $res);
		
		showStatus($res?0:-1, $res);
	}


	/**
 	 * @api {get} /getRequestToken getRequestToken 获取请求临时Token
 	 * @apiName getRequestToken
 	 * @apiVersion 2.0.0
 	 * @apiGroup SYSTEM
  
 	 * @apiSuccess {String} json Request Token
 	 */
	protected function getRequestToken(&$ioparams=array())
	{
		return parent::getRequestToken($ioparams);
	}
}