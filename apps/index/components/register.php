<?php

defined( 'RMAGIC' ) or die( 'Restricted access' );

class RegisterComponent extends CLoginComponent
{
	function __construct($name, $options)
	{
		parent::__construct($name, $options);
	}
	
	function RegisterComponent($name, $options)
	{
		$this->__construct($name, $options);	
	}
	
	protected function show(&$ioparams=array())
	{
		$res = parent::show($ioparams);
		
		$scf = Factory::GetSiteConfiguration();
		!isset($scf['logo']) && $scf['logo'] = $ioparams['_dstroot'].'/img/logo.png';
		$this->assign('scf', $scf);		
		
		$this->setTpl('register');
		return $res;
	}
}