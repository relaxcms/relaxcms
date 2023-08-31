<?php

/**
 * @file
 *
 * @brief 
 * 管理员
 *
 */
class AdminSession extends CSession
{
	function __construct($type, $options=null)
	{
		parent::__construct($type, $options);
	}
	
	function AdminSession($type, $options=null)
	{
		$this->__construct($type, $options);
	}	
	
	public function genRequestSSID($accesskey)
	{
		//ssid|username|role|client_ip|hash
		//$commonkey = "1234567812345678"; 
		//$commonkey = pack('H*', "bcb04b7e103a0cd8b54763051cef08bc");
		$commonkey = pack('H*', $accesskey);
		$r = Factory::GetRequest();
		$client_ip = $r->client();
		$usrinfo = $this->_userinfo;
		if (!$usrinfo)
			return false;
		
		$username = $usrinfo['username'];
		$role = isset($usrinfo['role'])? $usrinfo['role']: '';
		!$client_ip && $client_ip= '127.0.0.1';
		
		//ssid
		$magic = '!2#4';
		$hash = md5($magic.$username.$role.$client_ip.$accesskey);
		
		//rlog($magic.$username.$role.$client_ip.$accesskey);
		
		$data = $magic.'|'.$username.'|'.$role.'|'.$client_ip.'|'.$hash;
		
		$base64_requssid = base64_encode($data);

		$e = Factory::GetEncrypt();
		$requssid = $e->aesEncrypt($commonkey, $data);
		$de_requssid = $e->aesDecrypt($commonkey, $requssid);

		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, 'accesskey='.$accesskey.',org_requestssid='.$data.', $requssid='.$requssid.', $de_requssid='.$de_requssid);
		return $requssid;
	}


	public function checkRequestSSID($requestssid)
	{
		$cf = get_config();
		$accesskey = $cf['accesskey'];
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, 'accesskey='.$accesskey.', $requestssid='.$requestssid);
		//$commonkey = pack('H*', "bcb04b7e103a0cd8b54763051cef08bc");
		$commonkey = pack('H*', $accesskey);	
		$e = Factory::GetEncrypt();
		$de_requestssid = $e->aesDecrypt($commonkey, $requestssid);
				
		$m = Factory::GetModel('session');
		$res = $m->checkRequestSSID($de_requestssid);
		if (!$res) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "Invalid request ssid!");
			return false;
		}
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, $res);
		$res['enssid'] = $this->_enSSID($res['ssid']);

		return $res;
	}
	
		
	public function changePassword($id, $oldpass, $newpassword)
	{
		$oldpass = $this->decryptPassword(trim($oldpass));
		$newpassword = $this->decryptPassword(trim($newpassword));
		
		$oldpass = trim($oldpass);
		$newpassword = trim($newpassword);
		if (!check_passwd($newpassword)) {
			set_error('str_user_password_too_simple');
			return false;
		}
		
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "oldpass=$oldpass, newpassword=$newpassword");
		
		$m = Factory::GetAdmin();						
		if ($this->isSuper()) {
			$res = $m->setSuperPassword($id, $oldpass, $newpassword);
		} else {
			$res = $m->changePassword($id, $oldpass, $newpassword);			
		}
												
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "change user '$id' password failed!");
			return false;
		}
		
		return $res;
	}
	
	
	public function changeSuper($params)
	{
		$newSuperName = $params['newsupername'];
		$oldpass = $params['password'];
		$newpassword = $params['newpassword'];
		
		if (!$this->isSuper()) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "not super!");
			return false;
		} 
		
		
		$oldpass = $this->decryptPassword(trim($oldpass));
		$newpassword = $this->decryptPassword(trim($newpassword));
		
		$oldpass = trim($oldpass);
		$newpassword = trim($newpassword);
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "oldpass=$oldpass, newpassword=$newpassword");
		
		$m = Factory::GetModel('admin');	
		$id = $this->_userinfo['id'];			
		
		$params['oldpass'] = $oldpass;		
		$params['newpassword'] = $newpassword;		
		$res = $m->changeSuper($id, $params);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "change user '$id' password failed!");
			return false;
		}
		return $res;
	}
}