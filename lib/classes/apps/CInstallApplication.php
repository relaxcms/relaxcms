<?php

/**
 * 安装基类
 *
 */
class CInstallApplication extends CApplication
{
	public function __construct($name, $options = array())
	{
		parent::__construct($name, $options);
	}
	
	public function CInstallApplication($name, $options = array())
	{
		$this->__construct($name, $options);
	}
	
	public function getDefaultComponent()
	{
		return 'install';
	}
	
	protected function isComponent($cname)
	{
		if ($cname != 'install')
			return false;
		return true;
	}
	
	public function hasPrivilegeOf($component, $task='')
	{
		return true;
	}
}