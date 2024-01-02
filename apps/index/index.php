<?php

define('RPATH_FRONT_CDIR', dirname(__FILE__) );
class IndexApplication extends CFrontApplication
{
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);
	}
	
	public function IndexApplication($name, $options=null)
	{
		$this->__construct($name, $options);
	}
	
	public function install($ioparams=array())
	{
		return true;
	}
	
	protected function getDefaultComponent()
	{
		return 'index';
	}
}
