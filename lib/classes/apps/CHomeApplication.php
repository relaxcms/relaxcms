<?php

/**
 * @file
 * CHomeApplication
 * 会员中心
 *
 */
class CHomeApplication extends CMainApplication
{
		
	public function __construct($name, $options = array())
	{
		parent::__construct($name, $options);
	}
	
	public function CHomeApplication($name, $options = array())
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
			
	protected function initSession()
	{
		$this->_session = Factory::GetUser();
	}	
	
	public function dispatch(&$ioparams=array())
	{
		$ss = $this->getSession();
		
		$component = $ioparams['cname'];
		$tname = $ioparams['tname'];
		
		$component = $this->switchIfTop($component, $tname);			
		if (!$ss->isLogin()) {
			if (!$this->isPublicItem($component, $tname)) {
				$component = 'login';
			}
		}
		
		$ioparams['component'] = $component;	
		
		return false;
	}	
	
	
}