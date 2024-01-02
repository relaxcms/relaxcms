<?php
/**
 * @file
 *
 * @brief 
 * 
 * 参数
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class System_menuModel extends CParamsModel
{
	public function __construct($name, $options=array())
	{	
		parent::__construct($name, $options);
	}
		
	public function System_menuModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}

	
	protected function initDefaultParams(&$params=array())
	{
		return $params;				
	}

	public function set(&$params=array(), &$ioparams=array())
	{
		$res = parent::set($params, $ioparams);
		if ($res) {
			$app = Factory::GetApp();
			$app->cacheMenus();
		}
		return $res;
	}

	public function reset(&$params=array())
	{
		$res = parent::reset($params);
		if ($res) {
			$app = Factory::GetApp();
			$app->cacheMenus();
		}
		return true;
	}

}
