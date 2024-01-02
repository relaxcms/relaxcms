<?php

/**
 * @file
 *
 * @brief 
 *  系统配置
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class SystemConfigComponent extends CConfigComponent
{
	function __construct($name, $options)
	{
		parent::__construct($name, $options);
	}
	
	function SystemConfigComponent($name, $options)
	{
		$this->__construct($name, $options);
	}
	
	protected function show(&$ioparams = array())
	{
		$this->initActiveTab(10);
		
		$params = parent::show($ioparams);	
		
		//邮件
		$this->assignSelectEnable('api_smtp_enable', $params['api_smtp_enable']);
		//email SSL 
		$this->assign('smtp_auth_type_checked', $params['smtp_auth_type'] == 'ssl'?'checked':'');
		
		//wechat
		$this->assignSelectEnable('api_oauth_wechat_enable', $params['api_oauth_wechat_enable']);
		
		//github
		$this->assignSelectEnable('api_oauth_github_enable', $params['api_oauth_github_enable']);
		//qq
		$this->assignSelectEnable('api_oauth_qq_enable', $params['api_oauth_qq_enable']);

		//db1
		$db1 = get_dbconfig('db1');
		$this->assign("db1_dbtype_select", get_common_select('dbtype', $db1['dbtype']));
		$this->assign('db1', $db1);
		$this->assign('db1_enable', $db1['enable'] == 1?'checked':'');
		$this->assign('db1_dbcharset_select', get_common_select('dbcharset', $db1['dbcharset']));

		//db2
		$db2 = get_dbconfig('db2');
		
		$this->assign("db2_dbtype_select", get_common_select('dbtype', $db2['dbtype']));
		$this->assign('db2', $db2);
		$this->assign('db2_enable', $db2['enable'] == 1?'checked':'');
		$this->assign('db2_dbcharset_select', get_common_select('dbcharset', $db2['dbcharset']));

		//db3
		$db3 = get_dbconfig('db3');
		$this->assign("db3_dbtype_select", get_common_select('dbtype', $db3['dbtype']));
		$this->assign('db3', $db3);
		$this->assign('db3_enable', $db3['enable'] == 1?'checked':'');
		$this->assign('db3_dbcharset_select', get_common_select('dbcharset', $db3['dbcharset']));
		
		//默认起始组件, 注意权限
		//default_component_select
		$app = Factory::GetApp();
		$default_component = isset($params['default_component'])?$params['default_component']:'main';
		$default_component_select = '';
		$menus = $app->getCurrentMenuTree($default_component);
		foreach($menus as $key=>$v) {			
			if ($v['childen']) {
				foreach($v['childen'] as $k2=>$v2) {
					$selected = $default_component == $k2?'selected':'';
					$default_component_select .= "<option value='$k2' $selected>$v[title] -> $v2[title]</option>";
				}
			}
		}
		$this->assign('default_component_select', $default_component_select);
		
		return $res;
	}

	protected function saveDB(&$ioparams=array())
	{
		$name = '';
		$db1 = $this->request('db1');
		$db2 = $this->request('db2');
		$db3 = $this->request('db3');

		if ($db1 && is_array($db1)){
			$db1['enable'] = 1;
			$res1 = set_dbconfig('db1', $db1);
			$name = 'db1';
		}
		if ($db2 && is_array($db2)) {
			$db2['enable'] = 1;
			$res2 = set_dbconfig('db2', $db2);

			$name = 'db2';
		}
		if ($db3 && is_array($db3)){
			$db3['enable'] = 1;
			$res3 = set_dbconfig('db3', $db3);

			$name = 'db3';
		}


		$res = $res1 || $res2 || $res3;

		$db = Factory::GetDBO($name);
		if (!$db || !$db->is_connected()) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "invalid dbconfig '$name'!");
			$res = false;
		}

		showStatus($res?0:-1);
	}
	
	protected function copyForLoginBackground($files, $ioparams=array())
	{
		$tdir = $this->_bgdir;
		if (!is_dir($tdir))
			mkdir($tdir);
		
		foreach ($files as $key=>$v) {
			$targetfile = $tdir.DS.$v['id'].'.'.$v['extname'];
			$res = copy($v['opath'], $targetfile);
		}
	}
	
	protected function uploadloginbackground(&$ioparams=array())
	{
		$ioparams['_fbase'] = $ioparams['_base'].'/uploadloginbackground';
		$ioparams['deletecallback'] = true;
		$ioparams['uploadcallback'] = true;
		
		$m = Factory::GetModel('file');
		$fileinfo = $m->get($this->_id);
		$res = $m->tileupload($ioparams);
		if ($ioparams['issbt']) {
			if ($res && $ioparams['files']) {
				$this->copyForLoginBackground($ioparams['files'], $ioparams);
				CJson::encodedPrint(array('files' => $ioparams['files']));
				exit;
			} else {
				showStatus(-1);
			}
		}
		
		if (isset($ioparams['delete_fid'])) {	
			$targetfile = $this->_bgdir.DS.$ioparams['delete_fid'].'.'.$fileinfo['extname'];
			unlink($targetfile);
			showStatus(0);
		}
	}
	
	protected function copyForLogo($files, $ioparams=array())
	{
		$tdir = $this->_logodir;
		if (!is_dir($tdir))
			mkdir($tdir);
		
		foreach ($files as $key=>$v) {
			$targetfile = $tdir.DS.'adminlogo.'.$v['extname'];
			$res = copy($v['opath'], $targetfile);
			break;
		}
	}
	
	protected function uploadlogo(&$ioparams=array())
	{
		$ioparams['_fbase'] = $ioparams['_base'].'/uploadlogo';
		$ioparams['deletecallback'] = true;
		$ioparams['uploadcallback'] = true;
		
		$m = Factory::GetModel('file');
		$fileinfo = $m->get($this->_id);
		$res = $m->tileupload($ioparams);
		if ($ioparams['issbt']) {
			if ($res && $ioparams['files']) {
				$this->copyForLogo($ioparams['files'], $ioparams);
				CJson::encodedPrint(array('files' => $ioparams['files']));
				exit;
			} else {
				showStatus(-1);
			}
		}
		
		if (isset($ioparams['delete_fid'])) {	
			//$fileinfo = $ioparams['fileinfo'];
			$targetfile = $this->_logodir.DS.'adminlogo.'.$fileinfo['extname'];
			unlink($targetfile);
			showStatus(0);
		}
	}
	
	
	protected function updateDesktopBackground(&$ioparams=array())
	{
		
		$m = Factory::GetModel('splashclient');
		$res = $m->updateDesktopBackground($this->_bgdir, true);
		
		showStatus($res?0:-1);
	}
	
	
	protected function testemail(&$ioparams=array())
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "IN....");
		
		$_params = array();
		$this->getParams($_params);
		
		$params = array();
		
		$params['smtp_auth_type'] = $_params['smtp_auth_type'];
		$params['smtp_server_host'] = $_params['smtp_server_host'];
		$params['smtp_server_port'] = $_params['smtp_server_port'];
		$params['smtp_auth_account'] = $_params['smtp_auth_account'];
		$params['smtp_auth_passwd'] = $_params['smtp_auth_passwd'];
		
		if (!isset($params['smtp_auth_type']))
			$params['smtp_auth_type'] = '';
				
		$params['smtp_target'] = $_params['smtp_auth_account'];
		$params['subject'] = '测试邮件';
		$params['is_html'] = true;
		$params['content'] = "<HTML><BODY><br/>这是SMTP测试电子邮件，请勿回复！ <br/></BODY></HTML>";
		
		$mail = Factory::GetMail();			
		$res = $mail->send($params);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "call mail send failed!", $params);
		}
		
		showStatus($res?0:-1);
	}
	
	
	//测试短信发送
	protected function testsms(&$ioparams=array())
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "IN....");
		
		$params = array();
		$this->getParams($params);
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, $params);
		 	
		$smsparams = array();
		$smsparams['url'] = $params['api_sm_apiurl'];
		$smsparams['signId'] = $params['api_sm_app_id'];
		$smsparams['appCode'] = $params['api_sm_app_sign_id'];
		$smsparams['templateId'] = $params['api_sm_template_id'];
		$smsparams['phone'] = $params['api_sm_test_mobile_no'];
		$smsparams['params'] = '{"code": "7865"}'; //变量
		
		//${code}
		
		//$smsparams['subject'] = '测试短信';
		//$smsparams['content'] = "这是测试短信，请勿回复！";
		
		$sms = Factory::GetSms();			
		$res = $sms->send($smsparams);
		
		showStatus($res?0:-1);
	}	
}