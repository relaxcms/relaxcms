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
		parent::show();
		$this->enableJSCSS('cropimg');
		
		$myinfo = array();
		$extinfo = array();
		$apps = Factory::GetApps();
		$tags = array();
		
		foreach ($apps as $key=>$v) {
			$app = Factory::GetApp($key);
			if ($app && ($mi = $app->getMyInfo($ioparams))) {
				if ($mi['name'] == 'myinfo') {
					$myinfo[$key] = $mi;
				} else {
					$extinfo[$key] = $mi;					
				}
			}
		}
		
		$ex_nr = count($extinfo);
		
		$nr = $ex_nr+1;
		$this->initActiveTab($nr);
		
		//table
		$this->assign('myinfo', $myinfo);	
		$this->assign('extinfo', $extinfo);	
		
	}
}