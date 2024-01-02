<?php
/**
 * @file
 *
 * @brief 
 * 
 * CClusterModel
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class CClusterModel extends CModel
{
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);
	}
		
	public function CClusterModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}
	
	//检查请求头是否有'tag'
	protected function isClusterPost($params)
	{
		rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, $params);
		if (isset($params['__SYNCCLUSTERPOSTTAG']))
			return true;
			
		return false;
	}
	
	
	protected function sendPostCluster($cinfo, $fn, $params)
	{
		$_params = array();
		$_params['modname'] = $this->_name;
		$_params['fn'] = $fn;
		$_params['params'] = base64_encode(serialize($params));
		$_params['ssid'] = $cinfo['ssid'];
				
		$url = $cinfo['apiurl'].'/postCluster';
		$res = curlPOST($url, $_params);
		
		return $res;		
	}
	

	protected function syncCluster($fn, $params)
	{
		if ($this->isClusterPost($params)) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "is cluster POST skip!");
			return false;
		}
		
		$m = Factory::GetModel('cluster');	
		$sdb = $m->gets();
		
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN");
		foreach ($sdb as $key=>$v) {
			//skip localhost
			if ($v['is_local'])
				continue;
			if ($v['status'] != 1)
				continue;
				
			$res = $this->sendPostCluster($v, $fn, $params);
			if (!$res) { //cluster
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "call doPostCluster failed", $fn, $params);
			}
		}
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "OUT");
		
		return $res;
	}
	
	public function recvPostCluster($fn, $params)
	{
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN");
		$params['__SYNCCLUSTERPOSTTAG'] = 1;
		if (!method_exists($this, $fn)) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "no method '$fn'!");
			return false;
		}
		
		$res = $this->$fn($params);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "call '$fn' failed!");
			return false;
		}
		
				
		return $res;
	}
}
