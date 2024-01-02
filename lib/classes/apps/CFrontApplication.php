<?php

/**
 * 前端应用基类
 *
 */
class CFrontApplication extends CMainApplication
{
	protected $_scf = array();	
	public function __construct($name, $options = array())
	{
		parent::__construct($name, $options);
	}
	
	public function CFrontApplication($name, $options = array())
	{
		$this->__construct($name, $options);
	}
	
	protected function _init()
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "IN appname=".$this->_name);
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "OUT");
		$this->_default_flags_mask = 1;
		$this->_default_level_mask = 1;
		
		return false;
	}
	
	/*
	array(
	'index_open'=>'1',
	'login_access'=>'1',
	'name'=>'the RelaCMS sample site',
	'metakeyword'=>'the RelaCMS sample site',
	'metadescrip'=>'the RelaCMS sample site',
	'template'=>'index',
	'theme'=>'dark',
	'page_size'=>'12',
	'searchtime'=>'1',
	'searchmax'=>'100',
	'htmlpub'=>'0',
	'htmlupdate'=>'3',
	'is_open_comment'=>'0',
	'company'=>'',
	'company_person'=>'',
	'company_tel'=>'',
	'company_email'=>'',
	'wap_online_qq'=>'',
	'company_qq'=>'',
	'company_img_qq'=>'',
	'company_wx'=>'',
	'company_img'=>'',
);*/
	protected function initAppTemplate(&$ioparams=array())
	{
		$scf  = Factory::GetSiteConfiguration();
		$this->setAppTemplateDir($scf['template'], $ioparams);			
	}
	
	protected function initSession()
	{
		$this->_session = Factory::GetUser();
	}
	
		
	protected function isComponent2($cname)
	{
		return true;
	}

	protected function getDefaultComponent()
	{
		return 'index';
	}
	
	
	public function dispatch(&$ioparams=array())
	{
		$component = $ioparams['cname'];
		$tname = $ioparams['tname'];			
			
		$ss = $this->getSession();
		if (!($res = $ss->isLogin())) {
			$component = $this->switchIfTop($component, $tname);			
			if (!hasPrivilegeOf($component, $tname)) {
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__, 'no login of "'.$component.'"');
				$component = 'login';	
			}
			$ioparams['component'] = $component;	
		} else if ($component == 'login') {//已经登录
			$ioparams['component'] = 'index' 	;
		}
		$ioparams['isLogin'] = $res;
		
		return false;
	}	
	
	protected function init(&$ioparams=array())
	{
		parent::init($ioparams);
		
		//$scf = Factory::GetSiteConfiguration();
		$ioparams['_logo'] = isset($scf['logo'])?$scf['logo']: $ioparams['_dstroot'].'/img/logo.png';
	}
	
}