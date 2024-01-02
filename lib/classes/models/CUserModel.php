<?php
/**
 * @file
 *
 * @brief 
 * 
 * 用户模型
 *
 */

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

define('UF_USER',		1);
define('UF_ADMIN',		2);
define('UF_WEBSHELL',	4);
define('UF_SSH',		8);
define('UF_ALL',		0xff);


define('UAT_NAME',		1);
define('UAT_EMAIL',		2);
define('UAT_MOBILE',	3);
define('UAT_OTHER',		0xff);


class CUserModel extends CTableModel
{
	protected $_userinfo = array();
	
	protected $_roledb = array();
	/** 会话是否认证 */
	protected $_auth = false;
	
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);
	}
	
	public function CUserModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}
	
	protected function _init()
	{
		$this->_modname = 'user';
		parent::_init();
	}
	
	protected function _initFieldEx(&$f)
	{
		parent::_initFieldEx($f);
		
		switch ($f['name']) {
			case 'type':
				$f['input_type'] = 'selector';	
				break;
			case 'flags':
				$f['input_type'] = 'multicheckbox';	
				break;
			case 'allow_ip':
			case 'last_pwd': 			
			case 'pwd_last_update_ts':
				$f['detail'] = false;	
			case 'fails':
			case 'logins':
			case 'oid':
			case 'last_ip':
			case 'last_pwd':				
			case 'avatar':
				$f['edit'] = false;		
			case 'description':
				$f['show'] = false;		
				break;
			case 'last_time':
			case 'ts':
				$f['show'] = false;		
				$f['edit'] = false;		
				$f['input_type'] = "TIMESTAMP";		
				break;
			case 'status':
				$f['input_type'] = 'selector';
				break;
			case 'rid':
				$f['input_type'] = 'model';
				$f['model'] = 'role';
				break;
			case 'uid':
				$f['edit'] = false;		
				$f['show'] = false;	
				break;
			case 'password':
				$f['input_type'] = 'password';
				$f['show'] = false;
				$f['detail'] = false;
				$f['searchable'] = false;
			default:
				break;
		}
		
		return true;
	}
	
	
	protected function getRole($rid)
	{
		$m = Factory::GetModel('role');
		$res = $m->get($rid);
		if (!$res)
			return false;
		return $res['name'];		
	}
	
	protected function loadPrivilege(&$userinfo)
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "IN", $userinfo);
		$name = $userinfo['name'];
		$rid = $userinfo['rid'];
		$m = Factory::GetModel('group2role');
		$params = array('rid'=>$rid);
		$udb = $m->select($params);
		
		$gids = array();
		foreach($udb as $v) {
			$gids[] = $v["gid"];
		}
		$_gids = implode(',', $gids);	
		
		
		if (!$_gids) {
			//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "user '$name', rid=$rid no group!");
			return false;
		}
		
		//查询	
		$m2 = Factory::GetModel('privilege2group');
		$params = array('gid'=>array('in'=>$_gids));	
		$udb = $m2->select($params);
		
		
		$permisions = array();
		foreach($udb as $v) {
			$old = 0;
			if (isset($permisions[$v['pid']]))
				$old = $permisions[$v['pid']];
			
			$permisions[$v['pid']] = $old | $v['permision'];
		}
		$userinfo['permisions'] = $permisions;	
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "OUT", $permisions);
		return true;
	}
	
	protected function _initActions()
	{
		parent::_initActions();
		
		$name = 'resetpassword';
		$resetpwd = array(
				'name'=>$name,
					'icon'=>'fa fa-key',
				'title'=>'重置口令',
				'action'=>'button',
					'sort'=>1,
					'class'=>'green',
					'enable'=>true,
					'msg'=>'确定重置口令吗？',
					);
					
		$this->_default_actions[$name] = $resetpwd;
		
	}
	protected function formatOperate($row, &$ioparams=array())
	{
		$id = $row[$this->_pkey];
		
		$depOpt = parent::formatOperate($row, $ioparams);
		
		if (isset($depOpt['resetpassword'])) {

			$url = "$ioparams[_base]/resetpassword?id=$id";
			/*$res = "<a href='$url' target='_blank' class='btn green btn-xs btn-circle tmilink needconfirm' action='button' data-original-title='重置口令' title='重置口令' data-id=$id data-task='resetpassword' msg='确定重置口令吗？'> <i class='fa fa-key' ></i> </a>";
			$res .= $depOpt;*/
			
			$depOpt['resetpassword']['url'] = $url;
		}
		
		
		return $depOpt;
	}
	
	
	protected function formatUserInfo(&$params)
	{
		//nickname
		$nickname = trim($params['nickname']);
		!$nickname && $params['nickname'] = $params['name'];
			
		//fixed $allow_ip
		$actipstr = "";
		$actallowip = array();
		
		if ($params['allow_ip']) {
			$actallowip = json_decode($params['allow_ip']);
			if (count($actallowip)>0){
				$actipstr = join("\n", $actallowip);
			}
		}
		
		$params['ips'] = $actipstr;
		$params['allowip'] = $actallowip;
		$params['role'] = $this->getRole($params['rid']);
		
		//privileges
		$this->loadPrivilege($params);
		
		return true;		
	}
	
	
	public function get($id)
	{
		$res = parent::get($id);
		//if ($res) 
		//	$this->formatUserInfo($res);
		
		return $res;
	}
	
	public function getUserInfo($id)
	{
		$res = $this->get($id);
		if ($res) 
			$this->formatUserInfo($res);
		return $res;
	}
	
	
	public function getByName($name)
	{
		$res = $this->getOne(array('name'=>$name));
		if (!$res) {
			$m = Factory::GetModel('user_account');
			$res = $m->getOne(array('account'=>$name));
			if (!$res) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "WARNING: no user '$name'!");
				return false;
			}
			$uid = $res['uid'];
			$res = $this->get($uid);			
		}
		
		if ($res) 
			$this->formatUserInfo($res);
		
		return $res;	
	}
	
	public function getCurrentUserInfo()
	{
		return $this->_userinfo;
	}
	
	/* =====================================================================================
	 * set functions
	 * ====================================================================================*/
	public function formatForView(&$row, &$ioparams = array())
	{
		$res =  parent::formatForView($row, $ioparams);
		
		$avatar = $row['avatar'];
		if ($avatar) 
			$avatarurl = is_url($avatar)?$avatar:"$ioparams[_dataroot]/avatar/$avatar";
		
		$row['_avatar'] = $avatar?"<img src='$avatarurl' class='img-circle' width='128'>":'';
		//$status = $row['_status'];
		$row['_status'] = $this->formatLabelColorForView($row['status'], $row['_status']);
		
		//previewUrl for Listview
		$row['previewUrl'] = $avatarurl?$avatarurl:$ioparams['_dstroot']."/img/avatar.png";
		
	}
	
	
	public function setAvatar($params)
	{
		//$params['avatar'];
		//$params['id'];		
		$res = $this->update($params);		
		return $res;
	}
	
	public function getFieldsforInput($params=array(), &$ioparams=array(), $isadd=false)
	{
		$fdb = parent::getFieldsforInput($params, $ioparams);
		
		//加password2
		$name = 'password2';
		
		$newfield = $this->newField($name, array('sort'=>$this->_fields['password']['sort']+1));
		$newfield['input_type'] = 'password';
		$newfield['required'] = $isadd?'true':"false";
		$newfield['input'] = $this->buildInput($newfield, $params,  $ioparams);
		
		$fdb[$name] = $newfield;
		
		array_sort_by_field($fdb, "sort", false);
		
		return $fdb;
	}
	
	public function getFieldsForInputAdd($params=array(), &$ioparams=array())
	{
		$fdb = $this->getFieldsforInput($params, $ioparams, true);
		return $fdb;
	}
	
	public function getFieldsForInputEdit($params=array(), &$ioparams=array())
	{
		$this->_fields['password']['required'] = false;		
		$fdb = $this->getFieldsforInput($params, $ioparams);
		return $fdb;
	}
	
	protected function checkParams(&$params, &$ioparams=array())
	{
		$res = parent::checkParams($params, $ioparams);
		if (!$res) {
			return $res;			
		}
		
		$flags = 0;
		$uid = isset($params['id']) ? $params['id'] : 0;
		!$uid && isset($params[$this->_pkey]) && $uid = intval($params[$this->_pkey]);	
		
		if (isset($params['name'])) {
			$name = $params['name'];			
			if (!$uid && !$name) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no name!");
				return false;
			}
			if ($name && !is_username($name)) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "invalid user name '$name'!");
				return false;
			}
		}  else if (!$uid) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no name!");
				return false;
			}
		
		if (isset($params['password'])) {
			$password = trim($params['password']);		
			$password2 = isset($params['password2'])?trim($params['password2']):'invalid';		
			
			//password
			if (!$uid && !$password) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no password!");
				return false;			
			} else if (!$password) {	
					unset($params['password']);
				} else {				
					if ($password != $password2 ) {
						rlog(RC_LOG_ERROR, __FILE__, __LINE__, "password again error!");
						return false;
					}
					$params['password'] = encryptPassword($password);
				}	
		} else if (!$uid) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no password!");
				return false;
			}
		
		//allow_ip
		if (isset($params['ips'])) {
			$allowip = explode("\n", trim($params['ips']));
			$params['allowip'] = $allowip;
			
			$cli_bind_ip = '';
			if (is_array($allowip) && trim($allowip[0]) != ""){
				$ips = array();
				foreach ($allowip as $k=>$v) {
					$v = trim($v);
					if (!isCIDR($v)) {
						rlog(RC_LOG_ERROR, __FILE__, __LINE__, "invalid allow ip!");
						return false;
					}
					$ips[] = $v;
					if (!$cli_bind_ip)
						$cli_bind_ip = $v;
				}			
				$ipjson = json_encode($ips);
			}else{
				$ipjson = "NULL";
			}			
			$params['bind_ip'] = $cli_bind_ip;
		}
		
		
		return true;
	}
	
	public function set(&$params, &$ioparams=array())
	{
		$res = parent::set($params, $ioparams);
		
		return $res;
	}
	
	
	
	/* =====================================================================================
	 * Session functions
	 * ====================================================================================*/
	
	protected function enSSID($ssid)
	{
		if (!$ssid)
			return false;
		
		$cf = get_config();	
		if (is_array($ssid)) {
			$ssid = serialize($ssid);
		} 
		$e = Factory::GetEncrypt();
		//$essid = $e->mcrypt_des_encode($cf['ckey'], $ssid);
		
		$baccesskey = pack('H*', $cf['accesskey']);		
		$essid = $e->aesEncrypt($baccesskey, $ssid);
		
		//rlog(__FILE__, __LINE__, '$ssid='.$ssid, 'encrypt $essid='.$essid);	
		return $essid;
	}
	
	protected function deSSID($essid)
	{
		if (!$essid)
			return false;
		
		$cf = get_config();		
		$e = Factory::GetEncrypt();
		
		//fixed essid : ' '=> '+'
		$essid = str_replace(' ', '+', $essid);
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, 'accesskey='.$cf['accesskey']);
		//$ssid = $e->mcrypt_des_decode($cf['ckey'], $essid);
		$baccesskey = pack('H*', $cf['accesskey']);
		$ssid = $e->aesDecrypt($baccesskey, $essid);
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, '$essid='.$essid, 'decrypt $ssid='.$ssid);
		return $ssid;
	}
	
	
	/**
	 * 产生SSID COOKIE 名称
	 */
	protected function getSSIDName()
	{
		return 'ssid';
		/*
		$cf = get_config();
		$hash = $cf["hash"].$this->_type;
		return 'ssid_'.substr(md5($hash),0,5);*/
	}
	
	protected function getSSID()
	{
		$ckname = $this->getSSIDName();
		
		$ckvalue = false;
		if (isset($_COOKIE[$ckname]))
			$ckvalue = $_COOKIE[$ckname];		
		if (!$ckvalue && isset($_REQUEST['ssid'])) //SSID以变量
			$ckvalue = $_REQUEST['ssid'];
		if (!$ckvalue)
			return false;
		
		$ssid = $this->deSSID($ckvalue);
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "$ckname=$ckvalue, deSSID=$ssid");
		return $ssid;
	}
	
	protected function setSSID($ssid, $ck_time = 0)
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "in setSSID '$ssid', ck_time=$ck_time");
		
		$ckname = $this->getSSIDName();
		$ts = time();
		/*
		[HTTPS] => on
		*/
		$ssl = 0;
		if (isset($_SERVER['HTTPS']))
			$ssl = $_SERVER['HTTPS'] == 'on' ? 1:0;
		
		$ckdomain = "";		
		$ckpath = "/";
		
		$essid = $this->enSSID($ssid);	
		if (!$essid || $ck_time < 0) { //过期
			$res = setcookie($ckname, $essid, $ts-30*3600*24, $ckpath, $ckdomain, $ssl);
		} elseif ($ck_time === 0) {
			$res = setcookie($ckname, $essid, 0, $ckpath, $ckdomain, $ssl);
		} else {
			$ck_time += $ts;
			$res = setcookie($ckname, $essid, $ck_time, $ckpath, $ckdomain, $ssl);			
		}
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "out");
		
		return $essid;
	}
	
	/**
	 * checkSSID 检查ssid是否有效，有没有过期
	 *
	 * @param mixed $ssid This is a description
	 * @return mixed This is the return value description
	 *
	 */
	protected function checkSSID($ssid)
	{
		$m = Factory::GetModel('session');
		$ssinfo = $m->getOne(array('ssid'=>$ssid));
		if (!$ssinfo) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no ssid '$ssid'!");
			return false;
		}
		
		$id = $ssinfo['uid'];	
		$userinfo = $this->getUserInfo($id);
		if (!$userinfo) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no uid '$id'!");
			return false;
		}
		
				
		$userinfo['ssid'] = $ssinfo['ssid'];
		$userinfo['cktime'] = $ssinfo['cktime'];
				
		$this->_userinfo = $userinfo;
		$this->_auth = true;
		
		$cktime = intval($userinfo['cktime']);			
		
		//更新COOKIE, 用户有活动，延长过期时间，
		$this->setSSID($ssid, $cktime);
		
		return true;
	}
	
	
	/**
	 * genSSID 生成SSID
	 *
	 * @param mixed $params This is a description
	 * @return mixed This is the return value description
	 *
	 */
	protected function genSSID($params)
	{
		$uid = $params['id'];
		$name = $params['name'];
		$login_ip = isset($params['login_ip'])?$params['login_ip']:'';
		$login_type = isset($params['login_type'])?$params['login_type']:'';
		$model = $params['model'];
		$ssid = md5($model.$uid.'_'.$name.'_'.$login_ip.'_'.$login_type);		
		return $ssid;
	}
	
	
	/* =====================================================================================
	 * Login functions
	 * ====================================================================================*/
	
	public function resetFails($id)
	{
		$params=array();
		$params['id'] = $id;
		$params['fails']= 0;
		$res = $this->update($params);		
		
		return $res;
	}	
	
	public function addFails($id)
	{
		$res = $this->inc($id, 'fails');
		
		return $res;
	}
	
	
	
	protected function checkSession()
	{
		$ssid = $this->getSSID();
		if (!$ssid)
			return false;
		
		$res = $this->checkSSID($ssid);
		return $res;
	}
	
	public function isLogin()
	{
		if ($this->_auth)
			return true;		
		$res = $this->checkSession();
		
		return $res;
	}
	
	public function isFirstLogin()
	{
		return intval($this->_userinfo['logins']) == 1 && $this->_userinfo['pwd_last_update_ts'] == 0;
	}
	
	public function isNeedChangePassword()
	{
		$cf = get_config();
		$safepwd = $cf['safepwd'];
		
		$isFirstLogin = $this->isFirstLogin();
		
		return $safepwd && $isFirstLogin;
	}
	
	protected function isSuper()
	{
		return false;
	}
	
	public function isAuth()
	{
		return $this->_auth;
	}
	
	protected function isAdmin($userinfo)
	{
		return ($userinfo['flags'] & UF_ADMIN) != 0;
	}
	
	protected function isUser($userinfo)
	{
		$val = ($userinfo['flags'] & UF_USER);
		return $val != 0;
	}
	
	protected function encryptPassword($password)
	{
		return encryptPassword($password);
	}
	
	/**
	 * checkPassword 检查口令
	 *
	 * @param mixed $username This is a description
	 * @param mixed $password This is a description
	 * @param mixed $userinfo This is a description
	 * @return mixed This is the return value description
	 *
	 */
	protected function checkPassword($name, $password)
	{
		$ts = time();
		
		$userinfo = $this->getByName($name);
		if (!$userinfo) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "WARNING: no user '$name' ") ;
			return RC_E_INVALID_USER;
		}
		
		$cf = get_config();
		$max_loginfail = $cf['login_failure_times'];
		$loginfail_lock = $cf['login_fail_lock'];
		
		$uid = $userinfo['id'];
		
		if ($max_loginfail && $userinfo['fails'] >= $max_loginfail) {
			$locksec = 60* $loginfail_lock;	
			if ($res['last_time'] + $locksec > $ts ) {//超过一天，请空
				$this->resetFails($uid);
			} else {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "user '$name' login failed($max_loginfail) locked({$locksec}s) !"); // 登录失败次数已经超限
				return RC_E_LOGIN_LOCKED;
			}
		}
		
		$epassword = $this->encryptPassword($password);		
		if ($userinfo['password'] != $epassword) {
			$this->addFails($uid);
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "user '$name' password error!");
			return RC_E_INVALID_PASSWORD;
		}
		
		return true;
	}
	
	protected function checkFlags($userinfo)
	{
		return $this->isUser($userinfo); 
	}
	
	protected function decryptPassword($password)
	{
		if (isset($_SESSION['__aeskey'])) {
			$__aeskey = $_SESSION['__aeskey'];
			
			$e = Factory::GetEncrypt();
			$password = $e->aesDecryptJS($__aeskey, $password);
		}
		
		return $password;
	}
	
	
	protected function setSessionInfo(&$userinfo)
	{
		$uid = $userinfo['id'];
		$cktime = $userinfo['cktime'];
		$model = $userinfo['model'];
		$r = Factory::GetRequest();
		$client_ip = $r->client();
		$ts = time();
		
		$ssid = $this->genSSID($userinfo);	
		
		$params = array();	
		$params['uid'] = $uid;		
		$params['ssid']  = $ssid;
		$params['cktime']  = $cktime;
		$params['login_ip']  = $client_ip;
		$params['login_ts']  = $ts;
		$params['model']  = $model;
		
		$m = Factory::GetModel('session');
		$res = $m->getOne(array('ssid'=>$ssid));
		if ($res) 
			$params['id'] = $res['id'];
		
		$res = $m->set($params);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "set session failed!");
			return false;
		}
		
		$userinfo['ssid'] = $ssid;
		return true;
	}
	
	protected function updateLogin($userinfo)
	{
		$id = $userinfo['id'];
		$logins = $userinfo['logins'];
		
		$r = Factory::GetRequest();
		$ip = $r->client();
		$ts = time();
		
		$params = array();
		
		$params['id'] = $id;
		$params['fails'] = 0;
		$params['last_time'] = $ts;
		$params['last_ip'] = $ip;
		$params['logins'] = $logins+1;
		
		$this->update($params);
		
		
		return false;
	}
	
	protected function checkStatus($userinfo)
	{
		return $userinfo['status'] == 1;
	}
	
	public function setSession($userinfo)
	{
		
		$name = trim($userinfo["name"]);
				
		//检查是否禁用
		if (!$this->checkStatus($userinfo)) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "user '$name' status disabled!", $userinfo);
			return RC_E_LOGIN_FORBIDDEN;
		}
		
		//检查登录标志位
		$res = $this->checkFlags($userinfo);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "user '$name' flags '$userinfo[flags]' disabled!");
			return RC_E_LOGIN_FORBIDDEN; 
		}
				
		$userinfo['model'] = $this->_name;
		if (isset($userinfo['remember']) && $userinfo['remember']) { //记忆
			$userinfo['cktime'] = 31536000;
		} else {
			$userinfo['cktime'] = 0;
		}
		
		$res = $this->setSessionInfo($userinfo);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, 'set session failed!');
			return false;
		}
		
		if (!isset($userinfo['nocookie']) || !$userinfo['nocookie']) 
			$userinfo['essid'] = $this->setSSID($userinfo['ssid'], $userinfo['cktime']);
		
		$this->_auth = true;
		$this->_userinfo = $userinfo;
		
		$this->updateLogin($userinfo);
		
		slog_info("str_user_login_ok");	
		
		return true;
	}
	
	protected function loginByName(&$params)
	{
		//		
		$cf = get_config();			
		$username = trim($params["username"]);
		$password = trim($params["password"]);				
		if (!$username || !$password) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, 'invalid params', $params);
			return false;
		}
		
		$password = $this->decryptPassword($password);
		
		//用户名
		$userinfo = $this->getByName($username);
		if (!$userinfo) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "WARNING: no user '$username' ") ;
			setErr(RC_E_INVALID_USER);
			return RC_E_INVALID_USER;
		}
		
		//口令
		if (($res = $this->checkPassword($username, $password)) !== true) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, 'check password failed!res='.$res);
			setErr(RC_E_INVALID_PASSWORD);
			return $res;			
		}
		
		//创建会话
		
		
		$userinfo['remember']= isset($params['remember'])?true:false;
		$userinfo['nocookie']= isset($params['nocookie'])?true:false;
		
		$res = $this->setSession($userinfo);
		if ($res !== true) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, 'set login session failed!');
			return $res;
		}
		
		return true;
	}

	public function checkAccountAutoRegisterLogin($account)
	{
		$m = Factory::GetModel('user_account');
		$res = $m->getOne(array('account'=>$account));
		if (!$res) {
			$uid = $this->autoRegister();
			//绑定帐户
			$res = $this->bindAccountUID($uid, $account);
			if (!$res) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, 'bind account failed!');
				return false;
			}
			
		} else {
			$uid = $res['uid'];
		}
		
		$userinfo = $this->get($uid);
		
		$res = $this->setSession($userinfo);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, 'set login session failed!');
			return false;
		}

		return $userinfo;
	}
	
	
	/**
	 * This is method loginBySeccode
	 *
	 * @param mixed $params This is a description
	 * 
	 Array
	(
	   [account] => 180******017
	   [seccode] => AFF
	)
	
	 * @return mixed This is the return value description
	 *
	 */
	protected function loginBySeccode(&$params)
	{
		if (!$params) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "invalid params!", $params);
			return false;
		}
			
		$seccode = $params['seccode'];
		$account = $params['account'];
		
		$m = Factory::GetModel('user_seccode');
		$oldcode = $m->getSecCode($account);
		if (!$oldcode) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__,"get seccode failed!", $params);
			return false;
		}
		if ($oldcode != $seccode) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__,"invalid seccode '$seccode' failed!");
			return false;
		}

		//检查帐户，注册并登录
		$userinfo = $this->checkAccountAutoRegisterLogin($account);
		
		$res = $userinfo?true:false;
		
		return $res;
	}
	
	
	/**
	 * login
	 *
	 * @param mixed $params This is a description
	 * @return mixed 成功: true, 失败: false
	 *
	 */
	public function login(&$params)
	{
		if (isset($params['account']) && isset($params['seccode'])) { //验证码
			$res = $this->loginBySeccode($params);
		} else {
			$res = $this->loginByName($params);
		}
		
		return $res;
	}
	
	
	
	
	//用户退出
	public function logout()
	{
		$this->_authenticated = false;
		$this->setSSID($this->_userinfo['ssid'], -1);
		return false;
	}
	
	protected function getUserRoleID()
	{
		//str_user_group
		return 3;
	}	
	
	protected function createUser($name, $password, $params=array())
	{
		$params['name'] = $name;
		$params['password'] = $password;
		$params['password2'] = $password;
		$params['flags'] = 1;
		$params['type'] = 1; //普通
		$params['rid'] = $this->getUserRoleID();  //用户
		$params['status'] = 1; //启用
		
		$res = $this->set($params);
		if (!$res) {
			rlog(RC_LOG_DEBUG, __FILE__, __FILE__, __LINE__, "auto register failed!", $params);
			return false;			
		}
		
		return $params;
	}
	
	
	public function register($params, $ioparams=array())
	{
		if (!is_model('home_config')) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "no model 'home_config'!");
			return false;
		}
		
		$m = Factory::GetModel('home_config');
		$hcf = $m->get(0);
		
		$reg_email_seccode = $hcf['reg_email_seccode'];
		$reg_mobile_seccode = $hcf['reg_mobile_seccode'];
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $params);
		if (!$params) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__,"invalid params!", $params);
			return false;
		}
		
		$name = $params['name'];
		$password = $params['password'];
		$password2 = $params['password2'];
		$seccode = $params['seccode'];
		$account = $params['account'];
		
		//checkname
		if (!is_username($name)) {//
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__,"invalid username '$name'!");
			setErr(RC_E_USERNAME_INVALID, $name);
			return false;	
		}
		
		//检查用户名是否存在
		$res = $this->getOne(array('name'=>$name));
		if ($res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__,"username '$name' exists!");
			setErr(RC_E_USERNAME_EXISTS, $name);
			return false;
		}
		//检查帐户$account
		if ($reg_email_seccode && !is_email($account) ) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__,"invalid email account '$account'!", $params);
			setErr(RC_E_ACCOUNT_INVALID);
			return false;
		}
		
		if ($reg_mobile_seccode && !is_mobile($account)) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__,"invalid mobile account '$account'!", $params);
			setErr(RC_E_ACCOUNT_INVALID);
			return false;
		}
		
		//检查验证码
		if ($reg_mobile_seccode || $reg_email_seccode) {
			$m = Factory::GetModel('user_seccode');
			$oldcode = $m->getSecCode($account);
			if (!$oldcode) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__,"get seccode failed!", $params);
				setErr(RC_E_SECCODE_INVALID);
				return false;
			}
			if ($oldcode != $seccode) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__,"invalid seccode '$seccode' failed!");
				setErr(RC_E_SECCODE_INVALID);
				return false;
			}
		}
		
		//检查口令
		$password = $this->decryptPassword($password);
		$password2 = $this->decryptPassword($password2);
		if (!$password) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__,"invalid password '$password' failed!");
			setErr(RC_E_INVALID_PASSWORD);
			return false;
		}
			
		if ($password !== $password2) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__,"invalid password2 failed!");
			setErr(RC_E_INVALID_PASSWORD);
			return false;
		}	
		
		//创建帐户
		$res = $this->createUser($name, $password, $params);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__,"create user failed!");
			return false;
		}
		
		
		//绑定绑户
		$uid = $res['id'];
		$m = Factory::GetModel('user_account');
		$this->bindAccountUID($uid, $account);

		//设置登录
		$this->setSession($res);

		return $res;
		
	}	
	
	
	/* =======================================================================
	 * privilege functions
	 * ======================================================================*/
	
	public function hasPrivilegeOf($pid, $perm=0)
	{
		if (!$pid) //不需要权限
			return true;
			
		if ($this->isSuper()) //管理员登录
			return true;
				
		if (!isset($this->_userinfo['permisions'][$pid]))
			return false;
		$permision = $this->_userinfo['permisions'][$pid];
		if (!$permision)
			return false;
		if (!$perm) //不要求权限
			return true;	
		if (($perm & $permision) == $perm) //要求的权限通过
			return true;
		return false;
	}
	
	/* =======================================================================
	 * password functions
	 * ======================================================================*/
	/**
	 * 强密码检测
	 * 
	 * 最小8位，最大64位
	 * 密码应包括大写字母、小写字母、数字和特殊字符
	 *
	 * @param mixed $passwd This is a description
	  * @return 成功true, 失败false
	 */
	protected function checkPasswdSafe($passwd)
	{
		$cf = get_config();
		$safepwd = $cf['safepwd'];
		$min_passwd_length = $cf['min_passwd_length'];
		!$min_passwd_length && $min_passwd_length = 8;
		
		if ($safepwd)
		{
			if (strlen($passwd) < $min_passwd_length)
			{
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__,"passwd too short");
				return false;
			}
			
			//包含数字
			if (!preg_match('/\d+/', $passwd)) {
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__,"no digital");
				return false;
			}
			
			//包含小写字母
			if (!preg_match('/[a-z]+/', $passwd)) {
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__,"no small alpha");
				return false;
			}
			
			//包含小写字母
			if (!preg_match('/[A-Z]+/', $passwd)) {
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__,"no big alpha");
				return false;
			}
			
			//特殊字符
			if (!preg_match('/[-`=\\\[\];\',\.\/~!@#$%^&\*\(\)_\+\|\{\}:"<>\?]+/', $passwd)) {
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__,"no other alpha");
				return false;
			}
			
			/*
			if(item.getProperty("passwd")){
					if(sysPasswsStrong==1){
						if(item.value.match(/\d+/)&&item.value.match(/[a-z]+/)&&item.value.match(/[A-Z]+/)&&item.value.match(/[-`=\\\[\];',\.\/~!@#$%^&\*\(\)_\+\|\{\}:"<>\?]+/)){
							if(item.getNext(".pstatus")){
								item.getNext(".pstatus").set("text", "安全").setProperty("style", "padding:0 0 0 10px; color:green;");
							}
						}else{
							this.error(item, "密码应包括大写字母、小写字母、数字和特殊字符");
						}
					}
					if(item.value.length<sysMinPasswd || item.value.length>88){
						this.error(item, "密码长度在"+sysMinPasswd+"~88个字符");
					}
				}*/
		}
		
		
		return true;	
	}
	
	/**
	 * 检查新密码与最新使用的密码是否相同
	 *
	 * @param mixed $uid This is a description
	 * @param mixed $newpasswd This is a description
	 * @return mixed This is the return value description
	 *
	 */
	protected function check_passwd_last_used($id, $newpasswd)
	{
		$cf = get_config();
		$safepwd = $cf['safepwd'];
		if ($safepwd)
		{
			$filter = array('id'=>$id, 'last_pwd'=>array('like'=>$newpasswd));
			if ($this->getOne($filter)) {
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__,"id '%d' new passwd used");
				return true;
			}
		}
		return false;
	}
	
	protected function updateLastChangePasswdTime($id, $oldpwd)
	{
		$cf = get_config();
		$safepwd = $cf['safepwd'];
		if ($safepwd)
		{
			$res = $this->get($id);
			$oldpwd = $oldpwd.'|'.$res['last_pwd'];
			$ts = time();
			$params = array();
			$params['id'] = $id;
			$params['last_pwd'] = $oldpwd;
			$params['pwd_last_update_ts'] = $ts;
			$res = $this->update($params);
			if (!$res) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "set last_pwd failed!", $params);
				return false;
			}
		}
		
		return true;
	}
	
	protected function fixed_for_user_newpassword($uid, $epass)
	{
		$ts = time();
		
		$params = array();
		$params['id'] = $uid;
		//$params['password'] = $epass;
		$params['pwd_last_update_ts'] = $ts;
		
		$res = $this->update($params);
		
		return $res;
	}
	
	
	public function changePassword($id, $oldpass, $newpassword)
	{
		$userinfo = $this->get($id);
		if (!$userinfo)
			return false;
		
		//解密口令
		$oldpass = $this->decryptPassword(trim($oldpass));
		$oldpass = trim($oldpass);		
		$eoldpass = encryptPassword($oldpass);		
		if ($eoldpass != $userinfo['password']) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "old password error!");
			return false;
		}
		
		//检查口令合规
		$newpassword = $this->decryptPassword(trim($newpassword));
		$newpassword = trim($newpassword);
		if (!$this->checkPasswdSafe($newpassword)) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, 'New password is too simple!');
			return false;
		}
		
		$enewpassword = encryptPassword($newpassword);
		if ($this->check_passwd_last_used($id, $enewpassword) || $enewpassword == $eoldpass) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, 'New password last used!');
			return false;
		}		
		
		
		$params = array();
		
		$params['id'] = $id;
		$params['password'] = $enewpassword;
		$params['pwd_last_update_ts'] = time();
		$res = $this->update($params);
		if (!$res) {
			setErr('str_user_changepassword_failed');
		} else {
			//$this->syncSetSystemUser($userinfo);
			//更最近使用更新密码时间
			$this->updateLastChangePasswdTime($id, $userinfo['password']);		
			setMsg('str_user_changepassword_ok');
		}	
		return $res;
	}
	
	
	/* ======================================================================================
	 * user storage info
	 * =====================================================================================*/
	
	protected function getUserStorageDispathInfo($uid)
	{
		$m = Factory::GetModel('user2org');
		$res = $m->getOne(array('id'=>$uid));
		if (!$res) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "no org of uid '$uid'");
			return false;
		}
		
		$oid = $res['oid'];				
		$m2 = Factory::GetModel('storage_dispatch');
		$filter = array('oid'=>$oid);
		$sddb = $m2->select($filter);		
		if (!$sddb) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "no storage dispath of oid '$oid'");
			return false;
		}
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, $udb);
		
		//可用空间最大的存储为默认存储
		//查询用户所在单位分配置的空间
		$dispatch_total = 0;
		$used_total = 0;
		$max_freespace = 0;
		$max_freespace_sid = 1;
		
		foreach ($sddb as $v) { //空闲空间最大的
			$dispatch_total += $v['dispatch'];
			$used_total += $v['used'];					
			$free = $v['dispatch'] - $v['used'];		
			if ($max_freespace < $free  ) {
				$max_freespace = $free ;
				$max_freespace_sid = $v['sid'];
			}
		}
		
		
		
		$sdinfo = array();
		
		$sdinfo['uid'] = $uid;
		$sdinfo['oid'] = $oid;
		$sdinfo['sid'] = $max_freespace_sid;
		$sdinfo['dispatch'] = $dispatch_total;
		$sdinfo['used'] = $used_total;
		$sdinfo['free'] = $max_freespace;
		
		return $sdinfo;
	}
	
	public function getUserStorageInfo($uid)
	{
		$userinfo = $this->get($uid);
		if (!$userinfo) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no id '$uid'!");
			return false;
		}
		
		$sdinfo = $this->getUserStorageDispathInfo($uid);
		if ($sdinfo) {
			$sid = $sdinfo['sid'];
		} else {
			$sid = 1;
		}
		
		$m = Factory::GetModel('storage');
		$storageinfo = $m->get($sid);
		if (!$storageinfo) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no storage '$sid'");
			return false;
		}
		
		$dispatch = $sdinfo?$sdinfo['dispatch']:$storageinfo['total'];
		$used = $sdinfo?$sdinfo['used']:$storageinfo['used'];					
		$free = $sdinfo?$sdinfo['free']:$dispatch - $used;
		$oid = $sdinfo?$sdinfo['oid']:0;
		
		//附加		
		$storageinfo['uid'] = $uid;
		$storageinfo['oid'] = $oid;
		$storageinfo['dispatch'] = $dispatch;
		$storageinfo['used'] = $used;
		$storageinfo['free'] = $free;
		
		$basepath = $uid.'/'.tformat(0, 'Ym');
		$basedir = $storageinfo['mountdir'].DS.$basepath; //eg:1/202007
		if (!is_dir($basedir))
			s_mkdir($basedir);		
		
		$storageinfo['basedir'] = $basedir;
		$storageinfo['basepath'] = $basepath;
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, $storageinfo);
		
		return $storageinfo;
	}
	
	public function getMyStorage()
	{
		$userinfo = get_userinfo();
		$uid = $userinfo['id'];
		return $this->getUserStorageInfo($uid);
	}
	
	public function getMyStorageUI(&$ioparams=array())
	{
		$mystorageinfo = $this->getMyStorage();
		
		$total = $mystorageinfo['dispatch'];
		$used = $mystorageinfo['used'];
		$free = $total - $used;
		
		//i18n
		$t = $ioparams['_i18ndb'];
		$key = 'Dispatch';
		$dispatchTitle = isset($t[$key])?$t[$key]:$key;
		
		$key = 'Total';
		$totalTitle = isset($t[$key])?$t[$key]:$key;
		
		$key = 'Used';
		$usedTitle = isset($t[$key])?$t[$key]:$key;
		$key = 'Free';
		$freeTitle = isset($t[$key])?$t[$key]:$key;
		
		$tplinfo = '';
		$tplinfo .= '<div class="form-group">
				<label class="control-label col-md-3">'.$dispatchTitle.'
				</label>
				
				<div class="col-md-4">
				<span class="form-control-static">'.$mystorageinfo['name'].'</span>
				</div>
				</div>';
		
		
		$tplinfo .= '<div class="form-group">
				<label class="control-label col-md-3">'.$usedTitle.'
				</label>
				
				<div class="col-md-4">
				<span class="form-control-static">'.nformat_human_file_size($mystorageinfo['dispatch']).'</span>
				</div>
				</div>';
		
		$tplinfo .= '<div class="form-group">
				<label class="control-label col-md-3">'.$usedTitle.'
				</label>
				
				<div class="col-md-4">
				<span class="form-control-static">'.nformat_human_file_size($mystorageinfo['used']).'</span>
				</div>
				</div>';
		
		$tplinfo .= '<div class="form-group">
				<label class="control-label col-md-3">'.$freeTitle.'
				</label>
				
				<div class="col-md-4">
				<span class="form-control-static">'.nformat_human_file_size($free).'</span>
				</div>
				</div>';
		
		
		return $tplinfo;
		
	}
	
	
	/* lock/unlock */
	
	public function isLocked($id)
	{
		$res = $this->get($id);
		if (!$res) {
			return false;
		}
		

		$cf = get_config();
		$max_loginfail = $cf['login_failure_times'];
		$loginfail_lock = $cf['login_fail_lock'];
		
		if ($max_loginfail && $res['fails'] >= $max_loginfail) {
			return true;	
		}
		
		return false;
	}
		
	public function unLock($id)
	{
		return $this->resetFails($id);
	}
	
	protected function genPassword()
	{
		$pwd = randstr();
						
		return $pwd;		
	}
	
	protected function genResetPasswordSign($userinfo)
	{
		$cf = get_config();
		$hash = $cf["hash"];
		
		$id = $userinfo['id'];
		$password = $userinfo['password'];
		
		return md5($id.'_'.$password.' '.$hash);
	}
	
	
	protected function enResetPasswordSign($id, $sign)
	{
		return base64_encode($id.'-'.$sign);
	}
	
	protected function deResetPasswordSign($signCode)
	{
		$signCode = base64_decode($signCode);
		return explode('-', $signCode);
	}
	
	
	protected function getResetPasswordSign($userinfo)
	{
		$sign = $this->genResetPasswordSign($userinfo);
		
		return $this->enResetPasswordSign($userinfo['id'], $sign);
	}
	
	protected function sendResetPasswordNotify($userinfo, $ioparams=array())
	{
		$res = false;
		$id = $userinfo['id'];
		$accountinfo = $this->getAccountInfo($id);
		
		$target = $accountinfo['email'];
		if ($target) {
			$subject = '重置口令';
			$sign = $this->getResetPasswordSign($userinfo);	
			$resetUrl = $ioparams['_basenameurl']."/my_resetpassword?sign=$sign";			
			$content = "请点击链接重置口令：<a href='$resetUrl' target=_blank>$resetUrl</a>";
			
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $content);
			
			$res = send_email($target, $subject, $content);
			if (!$res) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "call send_email failed!", $target);
			}
		} else {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "WARNING: no email '$id'!");
		}
		
		return $res;
	}
		
	public function resetPassword($id, $ioparams=array())
	{
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "IN");
		
		$userinfo = $this->get($id);
		if (!$userinfo) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no user of '$id'!");
			return false;
		}
		
		$newpassword = $this->genPassword();
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "newpassword=$newpassword");
		
		$enewpassword = encryptPassword($newpassword);
		
		$params = array();
		$params['id'] = $id;
		$params['password'] = $enewpassword;
		$params['last_time'] = 0;
		$params['fails'] = 0;
		$params['logins'] = 0;
		$params['pwd_last_update_ts'] = 0;
		
		$res = $this->update($params);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "reset password for id '$id' failed!");
		}
		
		//邮件通知		
		$userinfo['password'] = $enewpassword;
		
		$res = $this->sendResetPasswordNotify($userinfo, $ioparams);		
		return $res;
	}
	
	
	public function resetPasswordBySign($signCode, $params)
	{
		//rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, '$signCode='.$signCode, $params);
		
		//id
		$sdb = $this->deResetPasswordSign($signCode);
		$id = $sdb[0];
		$sign = $sdb[1];
		
		$userinfo = $this->get($id);
		if (!$userinfo) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "no id '$id'!");
			return false;
		}
		
		//check sign
		$oldsign = $this->genResetPasswordSign($userinfo);
		
		if ($sign != $oldsign) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "invalid sign '$sign' of id '$id'!");
			return false;
		}
		
		$newpassword = trim($params['newpassword']);
		$newpassword2 = $params['newpassword2'];
		
		if (!$newpassword || $newpassword != $newpassword2) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "invalid newpassword '$newpassword'!");
			return false;
		}
		
		//检查口令合规
		$newpassword = $this->decryptPassword($newpassword);
		$newpassword = trim($newpassword);
		if (!$this->checkPasswdSafe($newpassword)) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, 'New password is too simple!');
			return false;
		}
		
		$enewpassword = encryptPassword($newpassword);
		
		$params = array();		
		$params['id'] = $id;
		$params['password'] = $enewpassword;
		$params['pwd_last_update_ts'] = time();
		$res = $this->update($params);
		if (!$res) {
			setErr('str_user_resetpassword_failed');
		} else {
			//更最近使用更新密码时间
			$this->updateLastChangePasswdTime($id, $userinfo['password']);		
			setMsg('str_user_resetpassword_ok');
		}	
		return $res;
	}
	
	/*
Array
(
    [newpassword] => aa
    [newpassword2] => aa
    [type] => 2
    [seccode] => 998903
)

*/
	public function resetPasswordBySecCode($params)
	{
		$type = intval($params['type']);
		$seccode = intval($params['seccode']);
		
		$uid = get_uid();
		if (!$uid){
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "no uid '$uid'!");
			return false;
		}
		
			
		$accountinfo = $this->getAccountInfo($uid);
		if ($type == UAT_EMAIL) {
			$account = $accountinfo['email'];
		}
		elseif ($type == UAT_MOBILE) {
			$account = $accountinfo['mobile'];
		}	
		
		if (!$account) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "no account '$account'!", $params);
			return false;
		}
		
		$m = Factory::GetModel('user_seccode');
		$oldcode = $m->getSecCode($account);
		if (!$oldcode) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__,"get seccode failed!", $params);
			return false;
		}
		if ($oldcode != $seccode) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__,"invalid seccode '$seccode' failed!");
			return false;
		}
		
		$newpassword = trim($params['newpassword']);
		$newpassword2 = $params['newpassword2'];
		
		if (!$newpassword || $newpassword != $newpassword2) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "invalid newpassword '$newpassword'!");
			return false;
		}
		
		//检查口令合规
		$newpassword = $this->decryptPassword($newpassword);
		$newpassword = trim($newpassword);
		if (!$this->checkPasswdSafe($newpassword)) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, 'New password is too simple!');
			return false;
		}
		
		$enewpassword = encryptPassword($newpassword);
		
		$params = array();		
		$params['id'] = $uid;
		$params['password'] = $enewpassword;
		$params['pwd_last_update_ts'] = time();
		$res = $this->update($params);
		if (!$res) {
			setErr('str_user_resetpassword_failed');
		} else {
			//更最近使用更新密码时间
			$userinfo = $this->get($uid);
			$this->updateLastChangePasswdTime($uid, $userinfo['password']);		
			setMsg('str_user_resetpassword_ok');
		}	
		
		return $res;
		
		
	}
			
	
	public function genUID()
	{
		$uid = randnum();
		while($res) {
			$res = $this->getOne(array('uid'=>$uid));			
		} 
		return $uid;
	}
	
	protected function newID(&$params=array())
	{
		$id = parent::newID($params);
		$params['uid'] = $this->genUID();
		return $id;
	}
	
	
	public function genName($params=array())
	{
		$name = trim($params['name']);
		!$name && $name = randName(8);
		$res = $this->getOne(array('name'=>$name));
		while($res) {
			$name = randName(8);
			$res = $this->getOne(array('name'=>$name));			
		} 
		return $name;
	}
	
	public function autoRegister($params=array())
	{
		$name = $this->genName($params);
		$params['name'] = $name;
		$autopass = md5('s.x.p'.time());		
		$params['email'] = $name.'@relaxcms.com'; //默认初始邮件地址
		$params['password'] = $autopass;
		$params['password2'] = $autopass;
		$params['flags'] = 1;
		$params['type'] = 1; //普通
		$params['rid'] = 3;  //用户
		$params['status'] = 1; //启用
		
		$res = $this->set($params);
		if (!$res) {
			rlog(RC_LOG_DEBUG, __FILE__, __FILE__, __LINE__, "auto register failed!", $params);
			return false;			
		}
		
		return $params['id'];
	}
	
	
	public function autoRegisterOAuth($params)
	{
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "IN");
		
		//生成user		
		$_params = $params;
		$_params['nickname'] = isset($params['nickname'])?$params['nickname']:$params['name'];
		
		//自动注册一个本地用户
		$res = $this->autoRegister($_params);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "auto register failed!");
			return false;
		}
		$uid = $res;
		
		$params['uid'] = $uid;
		$m = Factory::GetModel('oauth_user');
		$res = $m->set($params);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "set oauth user failed!", $params);
			return false;
		}
		
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "OUT");
		return $params;
	}
	
	
	public function del($id)
	{
		$old = parent::del($id);
		
		if ($old) {
			//删除帐户认证
			$m = Factory::GetModel('user_auth');
			$m->delete(array('uid'=>$id));
			
			//删除绑定帐户
			$m = Factory::GetModel('user_account');
			$m->delete(array('uid'=>$id));
			
			//删除第三方绑定帐户
			$m = Factory::GetModel('oauth_user');
			$m->delete(array('uid'=>$id));
			
			//删除会话
			$m = Factory::GetModel('session');
			$m->delete(array('uid'=>$id));
			
			//删除TOKEN
			$m = Factory::GetModel('user_token');
			$m->delete(array('uid'=>$id));
			
		}
		
		return $old;
	}
	
	
	protected function genToken($hash, $userinfo)
	{
		return md5($hash.'-'.$userinfo['id'].'-'.$userinfo['name']);	
	}
	
	
	protected function genTokenSecret($hash, $userinfo)
	{
		return sha1($hash.'-'.$userinfo['id'].'-'.$userinfo['password']);	
	}
	
	public function createToken($uid, $update=1)
	{
		$m = Factory::GetModel('user_token');
		$old = $m->getOne(array('uid'=>$uid));
		if ($update || !$old) {
			if ($old)
				$this->deleteToken($uid);		
			
			$hash = md5(time().randstr(5));			
			$userinfo = $this->get($uid);
			if (!$userinfo) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no uid '$uid'!");
				return false;
			}
			
			$token = $this->genToken($hash, $userinfo);
			$secret = $this->genTokenSecret($hash, $userinfo);
			
			$params = array();
			$params['uid'] = $uid;
			$params['token'] = $token;
			$params['secret'] = $secret;			
			$res = $m->set($params);
			if (!$res) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "set user token failed!", $params);
				return false;
			}
			
		} else {
			$params = $old;
		}		
				
		return $params;
	}	
	
	public function deleteToken($uid)
	{
		$m = Factory::GetModel('user_token');
		$res = $m->getOne(array('uid'=>$uid));
		if ($res) {
			return $m->del($res['id']);					
		} else {
			return false;
		}
	}
	
	public function getToken($params)
	{
		/*
		Array
			(
		   [token] => 6be1824c971d4e2ded5e3967cfc7605c
		   [timeout] => 3600
		   [sign] => 59c10a23795a8e0233aee1fef988057e
			)
		*/
		
		$m = Factory::GetModel('user_token');
		$tinfo = $m->getOne(array('token'=>$params['token']));
		if (!$tinfo)
			return false;
		$uid = $tinfo['uid'];
		
		
		//验证签名
		$secret = $tinfo['secret'];
		$newsign = sign($secret, $params);
		if ($newsign !== $params['sign']) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "invalid sign!", $params);
			return false;
		}
		
		$userinfo = $this->get($uid);
		if (!$userinfo) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "no uid '$uid'!");
			return false;
		}
		
		//登录
		//$userinfo['remember'] = 1;
		$res = $this->setSession($userinfo);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "set session failed!");
			return false;			
		}
		
		$essid = $this->_userinfo['essid'];
		
		return $essid;
		
	}
	
	public function forgetPassword($email, $ioparams=array())
	{
		$m = Factory::GetModel('user_account');
		$res = $m->getOne(array('account'=>$email));
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__,"no email account '$email'!");
			return false;
		}
		
		//查询
		$userinfo = $this->get($res['uid']);
		if (!$userinfo) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__,"no email '$email'!");
			return false;
		}
		
		$res = $this->sendResetPasswordNotify($userinfo, $ioparams);
				
		return $res;
	}
	
	public function sendSecurityCodeByEmail($email)
	{
		rlog(RC_LOG_DEBUG, __FUNCTION__, 'IN... $email='.$email);
		
		if (!$email) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__,"no email '$email'!");
			return false;
		}
		
		if (!is_email($email)) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__,"invalid email '$email'!");
			return false;
		}
		
		$m = Factory::GetModel('user_seccode');
		$seccode = $m->setSecCode($email);
		if (!$seccode) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__,"set seccode failed! '$seccode'!");
			return false;
		}	
		
		$subject = '邮件验证码';
		$content = "邮件验证码：$seccode";		
		$res = send_email($email, $subject, $content);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "call send_email failed!", $email);
		}
		
		return $res;
	}
	
	public function sendSecurityCodeBySms($mobile)
	{
		//rlog(RC_LOG_DEBUG, __FUNCTION__, 'IN... mobile='.$mobile);
		
		
		if (!$mobile) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__,"no mobile '$mobile'!");
			return false;
		}
		
		if (!is_mobile($mobile)) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__,"invalid email '$mobile'!");
			return false;
		}
		
		$m = Factory::GetModel('user_seccode');
		$seccode = $m->setSecCode($mobile);
		if (!$seccode) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__,"set seccode failed! '$seccode'!");
			return false;
		}
		
		//发短信		
		$cf = get_config();
		$smsparams = array();
		$smsparams['url'] = $cf['api_sm_apiurl'];
		$smsparams['signId'] = $cf['api_sm_app_id'];
		$smsparams['appCode'] = $cf['api_sm_app_sign_id'];
		$smsparams['templateId'] = $cf['api_sm_template_id'];
		$smsparams['phone'] = $mobile;
		$smsparams['params'] = '{"code": "'.$seccode.'"}'; //变量
		
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "SEND SMS....", $smsparams);
		
		$sms = Factory::GetSms();			
		//$res = $sms->send($smsparams);
				
		return false;
	}
	
	public function sendSecurityCode($account, $type=0)
	{
		
		if (!$account) {//帐户为空，默认使用当前登录帐户
			$uid = get_uid();
			$params = $this->getAccountInfo($uid);
			if ($type == UAT_EMAIL) {
				!empty($params['email']) && $account = $params['email'];
			}
			elseif  ($type == UAT_MOBILE) {
				!empty($params['mobile']) && $account = $params['mobile'];
			}
		}
		
		
		if ($type == 0) {
			if (is_email($account))
				$type = UAT_EMAIL;
			elseif (is_mobile($account))
				$type = UAT_MOBILE;
		}
		
		
		
		$res = false;
		switch($type)
		{
			case UAT_EMAIL:
				$res = $this->sendSecurityCodeByEmail($account);
				break;
			case UAT_MOBILE:
				$res = $this->sendSecurityCodeBySms($account);
				break;
			default :
				rlog(RC_LOG_ERROR, __FILE__,__LINE__, __FUNCTION__, "Unknown type '$type' or account '$account'!");
				break;
		}
		
		
		return $res;
	}
	
	public function bindAccountUID($uid, $account, $type=0)
	{
		if ($type == 0) {
			if (is_email($account))
				$type = UAT_EMAIL;
			elseif (is_mobile($account))
				$type = UAT_MOBILE;
			else {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__,"invalid account  type '$type' failed!");
				return false;
			}
		}
		
		$params = array();
		$params['uid'] = $uid;
		$params['account'] = $account;
		$params['type'] = $type;
		$params['status'] = 1;
		
		$m = Factory::GetModel('user_account');
		$res = $m->set($params);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__,"bing account '$account' failed!");
			return false;
		}	
		
		return $res;
	}
	
	
	public function bindAccount($params)
	{
		if (!$params)
			return false;
		
		$action = $params['action'];
		$type = $params['type'];
		$seccode = $params['seccode'];
		$account = $params['account'];
		
		$m = Factory::GetModel('user_seccode');
		$oldcode = $m->getSecCode($account);
		if (!$oldcode) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__,"get seccode failed!", $params);
			return false;
		}
		if ($oldcode != $seccode) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__,"invalid seccode '$seccode' failed!");
			return false;
		}
			
		$uid = get_uid();
		if (!$uid)
			return false;
		
		$m = Factory::GetModel('user_account');
		if ($action == 1) {
			$res = $this->bindAccountUID($uid, $account, $type);			
		} else {
			$res = $m->delete(array('account'=>$account));
			if (!$res) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__,"unbind account '$account' failed!");
				return false;
			}	
		}
		
		return $res;
	}
	
	public function getAccountInfo($uid)
	{
		$userinfo = $this->get($uid);
		if (!$userinfo) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "no uid '$uid'!");
			return false;
		}
		
		$m = Factory::GetModel('user_account');
		$accounts = $m->select(array('uid'=>$uid));
		
		$i = 1;
		foreach ($accounts as $v) {
			$type = $v['type'];
			$account = $v['account'];
			
			$idx = 'account'.$i++;
			$userinfo[$idx] = $account;
			
			switch($type) {
				case UAT_EMAIL://email
					$userinfo['email'] = $account;
					$userinfo['hasEmail'] = true;
					
					break;
				case UAT_MOBILE://mobile
					$userinfo['mobile'] = $account;
					$userinfo['hasMobile'] = true;
					break;
				default:					
					break;
			}
		}
		
		$userinfo['accounts'] = $accounts;
		
		return $userinfo;
		
	}
	
	
	public function getWalletInfo($uid)
	{
		$userinfo = $this->get($uid);
		if (!$userinfo) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "no uid '$uid'!");
			return false;
		}


		
		$m = Factory::GetModel('wallet');
		$wallet = $m->getOne(array('uid'=>$uid));
		if(!$wallet)
			$wallet = array('money'=>0, 'bean'=>0, 'point'=>0);
		
		$wallinfo = array_merge($userinfo, $wallet);
		
		return  $wallinfo;
	}
	
	
}