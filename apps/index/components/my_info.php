<?php
/**
 * @file
 *
 * @brief 
 *
 */

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );
class MyInfoComponent extends CMyInfoComponent
{
	function __construct($name, $options)
	{
		parent::__construct($name, $options);
	}
	
	function MyInfoComponent($name, $options)
	{
		$this->__construct($name, $options);
	}
	
	protected function show(&$ioparams=array())
	{
		parent::show($ioparams);
		$scf = Factory::GetSiteConfiguration();
		!isset($scf['logo']) && $scf['logo'] = $ioparams['_dstroot'].'/img/logo.png';
		$this->assign('scf', $scf);	
	}


	/**
 	 * @api {get} /getUserInfo getUserInfo 登录成功后获取用户信息
 	 * @apiName getUserInfo
 	 * @apiVersion 2.0.0
 	 * @apiGroup USER
  
 	 * @apiSuccess {String} json User Info
 	 */
	public function getUserInfo(&$ioparams=array())
	{	
		return parent::getUserInfo($ioparams);
	}
}