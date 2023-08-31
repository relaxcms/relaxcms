<?php

/**
 * @file
 *
 * @brief 
 * 
 * 验证码模型
 *
 */

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class User_seccodeModel extends CTableModel
{
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);
	}
		
	public function User_seccodeModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}
	
	
	protected function _initFieldEx(&$f)
	{
		switch ($f['name']) {
			case 'ts':
				$f['input_type'] = "TIMESTAMP";		
				break;
			default:
				break;
		}		
		return true;
	}
		
	protected function genSECID($name)
	{
		return md5($name.'r.c.secid');	
	}
	
	public function getSecCode($name)
	{
		$secid = $this->genSECID($name);
		$res = $this->getOne(array('secid'=>$secid));
		if (!$res) {
			return false;
		}
		
		$secs = time() - $res['ts'];
		if ($secs > $res['ttl']) {
			$diff = $secs - $res['ttl'];
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "the seccode had timeout '$diff'!", $res);
			return false;
		}
		
		$seccode = $res['seccode'];
		return $seccode;
	}
	
	public function setSecCode($name)
	{
		if (!$name) {
			return false;
		}
		$cf = get_config();
		
		//随机产生6位
		$seccode = randnum(6);
		$params = array();
					
		$secid = $this->genSECID($name);
		$res = $this->getOne(array('secid'=>$secid));
		if ($res) {
			$params['id'] = $res['id'];
		}
				
		$params['secid'] = $secid;
		$params['seccode'] = $seccode;
		$params['ttl'] = $cf['seccodetimeout'];
		$res = $this->set($params);
		if (!$res) {
			rlog(RC_LOG_DEBUG, __FUNCTION__, "set user seccode failed!", $params);
			return false;
		}
		
		return $seccode;
	}
	
	
	public function timer()
	{
		//清理过期的安全码
		$ts = time();
		$this->delete(array('ttl'=>array('lt'=>$ts.'-ts')));
	}
}