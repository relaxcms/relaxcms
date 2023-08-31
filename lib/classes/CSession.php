<?php

/**
 * @file
 *
 * @brief 
 * 会话类
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class CSession
{
	protected $_userinfo = array();
	
	protected $_nologin_components = array();
	protected $_public_components = array();
	
	/** 会话是否认证 */
	protected $_authenticated = false;
	protected $_is_first = false;
	
	protected $domain_root	= "";
	
	protected $_type			= null;
	protected $_type_value		= 1;
	protected $_session_name   = 'ssid';

	//构造函数
	public function __construct($type, $options=null)
	{
		$this->_type = $type;		
		$this->authenticated = false;	
	}
	
	public function CSession($type, $options=null)
	{
		$this->__construct($type, $options);
	}
	
	//对与不同类创建不同的
	static function &GetInstance($type, $options=null)
	{
		static $instances;
		
		if (!isset( $instances )) {
			$instances = array();
		}
		
		$sig = serialize($type);		
		if (empty($instances[$sig])) {	
			require_once(RPATH_SESSION.DS.$type.'.php');
			$class = ucfirst($type)."Session";
			if(!class_exists($class)) {
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "no class '$class'");
				return null;
			}
			
			$instance	= new $class($type, $options);
			$instances[$sig] =&$instance;
		}
		
		return $instances[$sig];
	}

	////// help functions

	protected function _enSSID($ssid)
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

	protected function _deSSID($essid)
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
	protected function genSSIDCookieName()
	{
		return 'ssid';
		/*
		$cf = get_config();
		$hash = $cf["hash"].$this->_type;
		return 'ssid_'.substr(md5($hash),0,5);*/
	}

	protected function getSSID()
	{
		$ckname = $this->genSSIDCookieName();
		
		$ckvalue = false;
		if (isset($_COOKIE[$ckname]))
			$ckvalue = $_COOKIE[$ckname];		
		if (!$ckvalue && isset($_REQUEST['ssid'])) //SSID以变量
			$ckvalue = $_REQUEST['ssid'];
		if (!$ckvalue)
			return false;
		
		$ssid = $this->_deSSID($ckvalue);
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "$ckname=$ckvalue, deSSID=$ssid");
		return $ssid;
	}


	protected function setSSID($ssid, $ck_time = 0)
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "in setSSID '$ssid', ck_time=$ck_time");

		$ckname = $this->genSSIDCookieName();
		$ts = time();
		/*
		[HTTPS] => on
		*/
		$ssl = 0;
		if (isset($_SERVER['HTTPS']))
			$ssl = $_SERVER['HTTPS'] == 'on' ? 1:0;
		
		$ckdomain = "";		
		$ckpath = "/";
		
		$essid = $this->_enSSID($ssid);	
		if (!$essid || $ck_time < 0) { //过期
			$res = setcookie($ckname, $essid, $ts-30*3600*24, $ckpath, $ckdomain, $ssl);
		} elseif ($ck_time === 0) {
			$res = setcookie($ckname, $essid, 0, $ckpath, $ckdomain, $ssl);
		} else {
			$ck_time += $ts;
			$res = setcookie($ckname, $essid, $ck_time, $ckpath, $ckdomain, $ssl);			
		}

		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "out");
		
		return true;
	}

	/** 检查ssid是否有效，有没有过期 */
	protected function checkSSID($ssid)
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "IN checkSSID");
		
		$m = Factory::GetModel('session');
		$res = $m->checkSSID($ssid);
		if (!$res) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "invalid ssid=".$ssid, $res);
			return false;
		}
		$this->_userinfo = $res;
		$this->_authenticated = true;

		$cktime = intval($res['cktime']);			
				
		//更新COOKIE, 用户有活动，延长过期时间，
		$this->setSSID($ssid, $cktime);
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "OUT checkSSID");
		
		return true;
	}	

	/////////////////////////////// public function
	public function isSuper()
	{
		if (!$this->_userinfo)
			return false;
			
		$cf = get_manager();
		if ($this->_userinfo['name'] == $cf['manager'])
			return true;
			
		return false;
	}

	public function isAuth()
	{
		return $this->_authenticated;
	}

	protected function checkLogin()
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "IN");
		
		$ssid = $this->getSSID();
		if (!$ssid)
			return false;
			
		$res = $this->checkSSID($ssid);
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "OTU");
		
		return $res;
	}

	public function isLogin()
	{
		if ($this->_authenticated)
			return true;		
		return $this->checkLogin();
	}


	public function isFirstLogin()
	{
		return intval($this->_userinfo['logins']) == 1 && $this->_userinfo['pwd_last_update_ts'] == 0;
	}

	public function getUserInfo()
	{
		return $this->_userinfo;
	}


	protected function decryptPassword($password)
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "password=$password");
		if (isset($_SESSION['__aeskey'])) {
			$__aeskey = $_SESSION['__aeskey'];
			//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "decrypt AES password, __aeskey=".$__aeskey);

			$e = Factory::GetEncrypt();
			$password = $e->aesDecryptJS($__aeskey, $password);
		}

		return $password;
	}

	/**
     * 成功: true, 失败: false or error code
	*/
	public function login(&$params)
	{
		$cf = get_config();	
		//rlog($params);
		
		$username = trim($params["username"]);
		$password = trim($params["password"]);				
		if (!$username || !$password) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, 'invalid params', $params);
			return false;
		}

		$password = $this->decryptPassword($password);
		
		$m = Factory::GetModel($this->_type);	
		$userinfo = array();
		$res = $m->checkLogin($username, $password, $userinfo);	
		if ($res !== true) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "username '$username' or password error!");
			return $res;
		}

		//创建会话
		if (isset($params['remember']) && $params['remember'] == 1) { //记忆
			$userinfo['cktime'] = 31536000;
		} else {
			$userinfo['cktime'] = 0;
		}
		
		$userinfo['model'] = $this->_type;
		$this->_userinfo = $userinfo;
		$s = Factory::GetModel('session');	 
		$res = $s->set($userinfo);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, 'session login failed!');
			return false;
		}
		
		if (!isset($params['nocookie']))
			$this->setSSID($userinfo['ssid'], $userinfo['cktime']);
				
		$this->_authenticated = true;
		
		$m->updateLogin($userinfo);
		
		slog_info("str_user_login_ok");	
		
		return true;
	}

	//用户退出
	public function logout()
	{
		$this->_authenticated = false;
		$this->setSSID($this->_userinfo['ssid'], -1);
		return false;
	}


	public function hasPrivilegeOf($pid, $perm=0)
	{
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
}