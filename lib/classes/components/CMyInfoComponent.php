<?php
/**
 * @file
 *
 * @brief 
 *  个人信息
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );
class CMyInfoComponent extends CMyFileDTComponent
{
	protected $_avatardir;
	
	function __construct($name, $options)
	{
		parent::__construct($name, $options);
		$this->_avatardir  = RPATH_DATA.DS."avatar";
	}
	
	function CMyInfoComponent($name, $options)
	{
		$this->__construct($name, $options);
	}

	protected function show(&$ioparams=array())
	{
		$this->enableJSCSS('cropimg');
		
		$userinfo = get_userinfo();
		
		$userinfo['last_time'] = tformat($userinfo['last_time']);
		
		$uid = $userinfo['id'];
		$avatarfile = $this->_avatardir.DS."avatar$uid.png";
		$userinfo['hasAvatar'] = file_exists($avatarfile)?1:0;
		$this->assign("userinfo", $userinfo);
		$this->assign("params", $userinfo);		
	}
	
	public function edit(&$ioparams=array())
	{
		$userinfo = get_userinfo();		
		$uid = $userinfo['id'];		
		$res = false;
		if ($this->_sbt) {
			$this->getParams($params);
			$m = Factory::GetModel('admin');
			
			$_params = array();
			$_params['id'] = $uid;
			$_params['email'] = $params['email'];
			$_params['nickname'] = $params["nickname"];
											
			$res = $m->update($_params);
		}	
		
		showStatus($res?0:-1);		
	}
	
	protected function avatar(&$ioparams=array())
	{
		$uid = get_uid();		
		$avatarfile = $this->_avatardir.DS."avatar$uid.png";
		
		
		if (is_file($avatarfile))
			$ioparams['imgfile'] = $avatarfile;
		
		$this->showimg($ioparams);
	}
	
	protected function docropimg(&$ioparams=array())
	{
		$uid = get_uid();
		
		$id = $this->_id;
		if (!is_dir($this->_avatardir))
			s_mkdir($this->_avatardir);
		$avatarfile = $this->_avatardir.DS."avatar$uid.png";
		
		$ioparams['width'] = 128;
		$ioparams['height'] = 128;
		$ioparams['dstimgfile'] = $avatarfile;
		
		$res = $this->__docropimg($ioparams);
		
		if ($res) {
			$m2 = Factory::GetModel('user');
			$params = array();
			
			$params['id'] = $uid;
			$params['avatar'] = "avatar$uid.png";
			
			$m2->setAvatar($params);
		}
		
		showStatus($res?0:-1);
	}
	protected function delcropimg(&$ioparams=array())
	{
		$res = false;
		$uid = get_uid();
		
		
		$avatarfile = $this->_avatardir.DS."avatar$uid.png";
		if (file_exists($avatarfile)) {
			unlink($avatarfile);	
			
			$m2 = Factory::GetModel('user');
			$params = array();			
			$params['id'] = $uid;
			$params['avatar'] = "";			
			$res = $m2->setAvatar($params);			
		}
		
		showStatus($res?0:-1);
	}


	protected function getUserInfo(&$ioparams=array())
	{	
		$m = Factory::GetModel('my');
		$res = $m->myInfo();
		showStatus($res?0:-1, $res);
	}
}