<?php

/**
 * @file
 *
 * @brief 
 * 
 * 管理员模型
 *
 */

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class CAdminModel extends CUserModel
{
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);
	}
	
	public function CAdminModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}
	
	
	
	
	public function isLogin()
	{
		$res = parent::isLogin();
		if (!$res) 
			return $res;
		//检查flags
		if (!$this->isAdmin($this->_userinfo))
			return false;
			
		return true;
	}
		
	
	protected function isSuper()
	{
		if (!$this->_userinfo)
			return false;		
		$cf = get_manager();
		if ($this->_userinfo['name'] == $cf['manager'])
			return true;		
		return false;
	}
	
	protected function isManager($name)
	{
		$cf = get_manager();
		return ($name === $cf['manager']);
	}
	
	protected function initManager()
	{
		$cf = get_manager();
		
		$params = array();
		$params['name'] = $cf['manager'];
		$params['password'] = $cf['manager_pwd'];
		$params['email'] = $cf['manager_email'];
		$params['flags'] = UF_ALL;
		$params['status'] = 1;
		
		$res = $this->add($params);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "add manager failed!");
			return false;
		}			
		
		$userinfo = $this->get($params['id']);
		if (!$userinfo) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no manager '$params[name]'!");
			return false;
		}
		
		return $userinfo;
	}
	
	
	public function get($id)
	{
		$userinfo = parent::get($id);
		if (!$userinfo) 
			return false;
			
		$userinfo['isSuper'] = $this->isManager($userinfo['name']);
		$userinfo['isAdmin'] = $this->isAdmin($userinfo);
		
		return $userinfo;	
	}
	
	
	public function getByName($name)
	{
		$isManager = $this->isManager($name);
		$userinfo = parent::getByName($name);
		if (!$userinfo) {
			if (!$isManager) 
				return false;							
			$userinfo = $this->initManager();
			if (!$userinfo) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "init manager failed!");
				return false;
			}
		}		
		
		$userinfo['isSuper'] = $isManager;
		$userinfo['isAdmin'] = $this->isAdmin($userinfo);
		
		return $userinfo;	
	}
	
	
	protected function checkFlags($userinfo)
	{
		return ($userinfo['flags']&UF_ADMIN) != 0;
	}
	
	
	/* ==================================================================
	 * change super
	 * =================================================================*/
	
	protected function doChangeSuper($uid, $params)
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, $params);
		
		$newSuperName = $params['newsupername'];
		$oldpasswd = $params['oldpass'];
		$newpasswd = $params['newpassword'];
		
		$cf = get_manager();
		
		$oep = encryptPassword($oldpasswd);
		if ($oep != $cf['manager_pwd'] ) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "old password '$oldpasswd' error!");
			return false;
		}
		
				
		$manager_email = empty($params['manager_email'])?$cf['manager_email']:$params['manager_email'];
		
		$mdb['manager'] = $newSuperName;
		$mdb['manager_pwd'] = $newpasswd; //set_manager中加密
		$mdb['manager_email'] = $manager_email;
		
		$res = set_manager($mdb, true);
		if (!$res)	{
			setErr('str_user_changesuper_failed');
			return false;
		}		
		setMsg('str_user_changesuper_ok');
		
		$manager_newpwd = encryptPassword($newpasswd);
		$params = array(
				'id'=>$uid, 
				'name'=>$newSuperName,
				'password'=>$manager_newpwd,
				$params['pwd_last_update_ts'] = time()
				);
		$res = $this->update($params);
		
		return $res;		
	}
	
	
	public function changeSuper($params)
	{
		if (!$this->isSuper()) 
			return false;
		
		$newSuperName = $params['newsupername'];
		$oldpass = $params['password'];
		$newpassword = $params['newpassword'];
		
		$oldpass = $this->decryptPassword(trim($oldpass));
		$newpassword = $this->decryptPassword(trim($newpassword));
		
		$oldpass = trim($oldpass);
		$newpassword = trim($newpassword);
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "oldpass=$oldpass, newpassword=$newpassword");
		
		$id = $this->_userinfo['id'];			
		
		$params['oldpass'] = $oldpass;		
		$params['newpassword'] = $newpassword;		
		$res = $this->doChangeSuper($id, $params);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "change user '$id' password failed!");
			return false;
		}
		return $res;
	}
	
	
}