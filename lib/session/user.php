<?php

/**
 * @file
 *
 * @brief 
 * 用户会话
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class UserSession extends CSession
{
	function __construct($type, $options=null)
	{
		parent::__construct($type, $options);
	}
	
	function UserSession($type, $options=null)
	{
		$this->__construct($type, $options);
	}
		
	protected function _update_login($username)
	{
		$db = Factory::GetDBO();		
		$ip = $_SERVER['REMOTE_ADDR'];		
		$sql = "update cms_member set fails=0, last_ip='$ip', last_time=unix_timestamp() where username='$username'";
		$db->exec($sql);
	}
		
	//鉴权,成功返回用户信息，失败返回null
	protected function checkUser($username, $password)
	{		
		$username = str_replace(array('\'', '"', '='), array('', '', ''), $username);
		
		$db = Factory::GetDBO();
		if (!$db)
			return false;
		
		$sql = "select * from cms_member where username='$username' ";
		$res = $db->get_one($sql);
		if (!$res) return false;
		
		if ($res['password'] != $password)
			return false;
		$this->_userinfo = $res;		
		$this->_update_login($username);	
		
		return true;	
	}
	
	
	
	public function getUserInfo()
	{
		if (!$this->isLogin()) 
			return null;
		return $this->_userinfo;
	}
	
	//用户退出
	public function logout()
	{
		$this->_authenticated = false;
		Cookie::set_cookie($this->_type, '');
		return false;
	}
	
	protected function checkPassword($username, $password, &$res)
	{
		$ts = time();
		
		$db = Factory::GetDBO();
		$cf = get_config();
		
		$res = $db->get_one("select * from cms_member where username='$username'");
		if (!$res) {
			set_error( "str_login_error") ;
			return false;
		}
		
		if ($res['status'] == 0) {
			set_error('str_login_no_check');
			return false;
		} else if ($res['status'] == 2) {
				set_error('str_login_forbidden');
				return false;
			} 
		
		if($res['loginfail'] >= 15) {
			if($res['logintime'] + 3600*24 > $ts) {//超过一天，请空
				$db->exec("update cms_member set fails=0 where username='$username'");
			} else {
				rlog('login_failed_forbidden', ETYPE_LOGIN); // 登录失败次数已经超限
				return false;
			}
		}
		
		if ($res['password'] != $password) {
			$db->exec("update cms_member set fails=fails+1 where username='$username'");
			rlog('str_login_error', ETYPE_LOGIN);
			return false;
		}
		
		$ip = $_SERVER['REMOTE_ADDR'];		
		$db->exec("update cms_member set fails=0, last_time=unix_timestamp(), last_ip='$ip' where username='$username'");
		return true;
	}
	
	protected function check_code($udb)
	{
		$cf = get_config();
		if ($cf['ck_site']) {
			if (!$udb['ckcode']) {
				set_error('login_ckcode_error');
				return false;
			}			
			session_start();
			if (strtolower($_SESSION['ckcode']) != strtolower($udb['ckcode'])) {
				set_error('str_login_ckcode_error');
				return false;
			}
		}
		
		//不作处理
		return true;
	}
	
	public function login(&$params)
	{
		$cf = get_config();		
		if (($ck = $this->check_code($udb)) !== true) {
			return $ck;
		}
		
		$username = $udb["username"];
		$password = $udb["password"];
		
		if(empty($username) || empty($password)) {
			rlog("login_empty", ETYPE_LOGIN);
			return false;
		}
		
		$username = trim($username);
		$password = MD5(trim($password));
		
		if (($err = $this->checkPassword($username, $password, $res)) !== true) {
			return false;
		}
		
		$res['ck_ts'] = time();		
		if (array_key_exists('cookie', $udb) && $udb['cookie'] == 1){//记忆
			$res['ck_time'] = 31536000;
		} else {
			$res['ck_time'] = 0;
		}
		
		$this->_userinfo = $res;
		Cookie::set_cookie($this->_type, $res, $res['ck_time']);
		
		rlog("str_member_login_ok");	
		return true;
	}
	
	//提取菜单
	public function get_menus($pkey = 'root')
	{
		$menus = Factory::GetSiteMenus();	
		return $menus;
	}		
}
