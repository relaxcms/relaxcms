<?php
/**
 * @file
 *
 * @brief 
 *  个人文件组件
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );
class CMyFileDTComponent extends CFileDTComponent
{
	function __construct($name, $options)
	{
		parent::__construct($name, $options);
	}
	
	function CMyFileDTComponent($name, $options)
	{
		$this->__construct($name, $options);
	}
	
	protected function selectForFileView($modname, $params, &$ioparams=array())
	{
		$userinfo = get_userinfo();
		
		$params['cuid'] = $userinfo['id'];
		$res = parent::selectForFileView($modname, $params, $ioparams);
		
		return $res;
	}
}