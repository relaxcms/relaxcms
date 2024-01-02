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
		//$this->enableJSCSS(array('jquery_backstretch', 'crypto', 'encrypt', 'bootstrap_toastr'), true);
		
		//bg
		$bg = Factory::GetModel('splashclient');
		$bg->updateDesktopBackground();
		
	}

		
	protected function initLoginToken(&$ioparams=array())
	{
		$token = $this->genRequestToken($ioparams);

		//背景
		$bgdb = loadgb();
		$bgurls = array();
		foreach ($bgdb as $key => &$v) {
			$url = $ioparams['_dataroot'].'/bg/'.$v['name'];
			$v['url'] = $url;
			$bgurls[] = $url;
		}
		
		$token['bgurls'] = $bgurls;
		return $token;

	}

	protected function show(&$ioparams=array())
	{
		$this->initBG($ioparams);	
		$token = $this->initLoginToken($ioparams);	
		
		$this->assign('_bgdb', $token['bgurls']);
		$cf = get_config();
		$savecookie = $cf['savecookie'];	
		$enable_captcha = $cf['enable_captcha'];	

		
		$this->assign('savecookie', $savecookie);	
		$this->assign('enable_captcha', $enable_captcha);
		
		$backurl = $this->request('backurl');
		!$backurl && $backurl = $ioparams['_uri'];
		
		//rlog(RC_LOG_DEBUG, __FUNCTION__, $backurl);
		$this->assign('backurl', $backurl);	

		$this->assign('seccodeimg', $token['seccodeimg']);	

		
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
		$token = $this->initLoginToken($ioparams);		
		showStatus(0, $token);
	}


	protected function getLoginToken(&$ioparams=array())
	{

		$token = $this->initLoginToken($ioparams);

		showStatus(0, $token);
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
			if (isset($params['seccode']))
				$seccode = $params['seccode'];
			else 
				$seccode = '';	

			if (!isset($params['account']) 
				&& !isset($params['seccode']) 
				&& !$this->checkSecCode($seccode)) {
				rlog(RC_LOG_INFO, __FILE__, __LINE__, "invalid seccode!");
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
		
		//兼容性处理
		if (!$params)
			$params = $_REQUEST;
		if (!isset($params['name']) && isset($params['username']))
			$params['name'] = $params['username'];
		
		$m = Factory::GetModel('user');
		$res = $m->register($params, $ioparams);
		
		showStatus($res?0:-1);
	}
}