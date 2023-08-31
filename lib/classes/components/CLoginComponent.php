<?php
/**
 * @file
 *
 * @brief 
 *  登录基类
 *
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );
class CLoginComponent extends CUIComponent
{
	function __construct($name, $options)
	{
		parent::__construct($name, $options);
	}
	
	function CLoginComponent($name, $options)
	{
		$this->__construct($name, $options);
	}
	
	protected function initBG(&$ioparams=array())
	{
		//fixed js and css
		//$this->enableJSCSS(array('datatables', 'jquery_blockui', 'jquery_fileupload', 'datatable'), false);
		$this->enableJSCSS(array('jquery_backstretch', 'crypto', 'encrypt', 'bootstrap_toastr'), true);
		
		//bg
		$bg = Factory::GetModel('splashclient');
		$bg->updateDesktopBackground();
		
	}

	
	protected function initloginsession(&$ioparams=array())
	{
		//公key
		$pkey = md5(time());
		$this->assignSession('__aeskey', $pkey);
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, '$__aeskey='.$pkey);

		//背景
		$bgdb = loadgb();
		$bgurls = array();
		foreach ($bgdb as $key => &$v) {
			$url = $ioparams['_dataroot'].'/bg/'.$v['name'];
			$v['url'] = $url;
			$bgurls[] = $url;
		}

		$initlogindata['pkey'] = $pkey;
		$initlogindata['bgurls'] = $bgurls;

		return $initlogindata;

	}

	protected function show(&$ioparams=array())
	{
		$this->initBG($ioparams);	
		$initlogindata = $this->initloginsession($ioparams);	
		
		$this->assign('_bgdb', $initlogindata['bgurls']);
		$cf = get_config();
		$savecookie = $cf['savecookie'];	
		$enable_captcha = $cf['enable_captcha'];	

		
		$this->assign('savecookie', $savecookie);	
		$this->assign('enable_captcha', $enable_captcha);
		
		$backurl = $this->request('backurl');
		!$backurl && $backurl = $ioparams['_uri'];
		
		//rlog(RC_LOG_DEBUG, __FUNCTION__, $backurl);
		$this->assign('backurl', $backurl);	
		
		return true;
	}
	
	protected function edit(&$ioparams=array())
	{
		return $this->show($ioparams);
	}
	
	protected function add(&$ioparams=array())
	{
		return $this->show($ioparams);
	}

	protected function gentoken(&$ioparams=array())
	{
		$initlogindata = $this->initloginsession($ioparams);
		$this->setSbt($initlogindata);
		
		showStatus(0, $initlogindata);
		return 0;
	}


	protected function checkSecCode($captcha)
	{
		$cf = get_config();
		if (!isset($cf['enable_captcha']) || !$cf['enable_captcha'])
			return true;

		$captcha = strtolower($captcha);
		$sessionCap = $_SESSION['seccode'];
		if ($captcha != $sessionCap){
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "str_login_invalid_seccode");
			return false;
		}
		return true;
	}
	

	protected function login(&$ioparams=array())
	{
		if ($this->_sbt) {
			$this->getParams($params);
			if (isset($params['captcha']))
				$captcha = $params['captcha'];
			else 
				$captcha = '';	

			if (!isset($params['account']) 
				&& !isset($params['seccode']) 
				&& !$this->checkSecCode($captcha)) {
				rlog(RC_LOG_INFO, __FILE__, __LINE__, "invalid captcha!");
				showStatus(RC_E_INVALID_CAPTCHA);
				return false;
			}

			$app = Factory::GetApp();
			if (($res = $app->login($params)) === true) {
				//$gourl = $this->_basename;
				//redirect($gourl);
				$backurl = $this->request('backurl');
				!$backurl && $backurl = str_replace('/login', '', $ioparams['_uri']);
				//rlog(RC_LOG_INFO, __FILE__, __LINE__, "login OK!backurl=$backurl");
				
				showStatus(0, array('backurl'=>$backurl));
				return true;
			} else {
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "login failed! res=$res");
				showStatus($res);
			}
		} else {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "invalid sbt");
			showStatus(RC_E_INVALID_SBT);
		}
		showStatus(RC_E_FAILED);
		return false;
	}
	
	
	
	
	
	protected function logout(&$ioparams=array())
	{
		$app = Factory::GetApp();
		$app->logout();
		redirect($ioparams['_basename']);
	}
		
	protected function forgetPassword(&$ioparams=array())
	{
		$email = $this->request('email');
		
		$m = Factory::GetModel('user');
		$res = $m->forgetPassword($email, $ioparams);
		
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, '$email='.$email.',$res='.$res);
		showStatus($res?0:-1);
	}
	
	
	protected function register(&$ioparams=array())
	{
		$params = array();
		$this->getParams($params);
		
		$m = Factory::GetModel('user');
		$res = $m->register($params, $ioparams);
		
		showStatus($res?0:-1);
	}
}