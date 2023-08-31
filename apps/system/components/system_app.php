<?php

/**
 * @file
 *
 * @brief 
 *  基本应用管理类,实现应用安装,卸载
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class SystemAppComponent extends CAppComponent
{
	function __construct($name, $options=null)
	{
		parent::__construct($name, $options);
	}
	
	function SystemAppComponent($name, $options=null)
	{
		$this->__construct($name, $options);
	}
	protected function _initModel()
	{
		$this->_modname = 'app';
		$this->_default_viewtype=1;
	}
	
	protected function getTools($activetminame, $tools = array())
	{
		return array();
	}
	
	protected function show(&$ioparams=array())
	{
		$m = Factory::GetModel('app');
		$m->loadApp();
		
		$res = parent::show($ioparams);	
		
		return $res;
	}
	
	
	protected function detail(&$ioparams=array())
	{
		$this->setActiveTab(2);

		
		$res = parent::detail($ioparams);

		$m = Factory::GetModel('app');
		$vdb = $m->getAppVersionList($this->_id);

		$this->assign('vdb', $vdb);
		$this->setTpl('system_app_detail');

		return $res;
	}
	
	
	
	/**
	 * 插件安装
	 *
	 * @return mixed 成功true, 失败false
	 *
	 */
	protected function install(&$ioparams=array())
	{
		$m = Factory::GetModel('app');		
		$res = $m->install($this->_id, $ioparams);		
		showStatus($res?0:-1, ($res)?array('refresh'=>1):array());
	}
	
	
	//installFromRemote
	protected function installFromRemote(&$ioparams=array())
	{
		$m = Factory::GetModel('app');		
		$res = $m->installFromRemote($this->_id, $ioparams);	
		$data = $res?array('refresh'=>1):array();
		if (isset($ioparams['data'])){
			$data = $ioparams['data'];
			$data['redirect'] = $data['url'];
		}

		showStatus($res?0:-1, $data);
	}
	
	
	protected function upgradeFromRemote(&$ioparams=array())
	{
		$m = Factory::GetModel('app');
		$res = $m->upgradeFromRemote($this->_id, $ioparams);
		$data = $res?array('refresh'=>1):array();
		if (isset($ioparams['data'])){
			$data = $ioparams['data'];
			$data['redirect'] = $data['url'];
		}
		
		showStatus($res?0:-1, $data);
	}
	
	/**
	 * 卸载插件
	 *
	 * @return mixed This is the return value description
	 *
	 */
	protected function uninstall(&$ioparams=array())
	{
		$m = Factory::GetModel('app');		
		$res = $m->uninstall($this->_id);	

		showStatus($res?0:-1, $res?array('refresh'=>1):array());
	}

	protected function uninstallall(&$ioparams=array())
	{
		$m = Factory::GetModel('app');		
		$res = $m->uninstall($this->_id, true);	

		showStatus($res?0:-1, $res?array('refresh'=>1):array());
	}
	
	
	protected function remove(&$ioparams=array())
	{
		$m = Factory::GetModel('app');		
		$res = $m->remove($this->_id);		
		showStatus($res?0:-1, $res?array('redirect'=>$ioparams['_base']):array());
	}
	
	protected function del(&$ioparams=array())
	{
		return $this->remove($ioparams);
	}	
}