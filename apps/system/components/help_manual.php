<?php

/**
 * @file
 *
 * @brief 
 * ÊÖ²á
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );


class HelpManualComponent extends CUIComponent
{
	function __construct($name, $options=null)
	{
		parent::__construct($name, $options);
	}
	
	function HelpManualComponent($name, $options=null)
	{
		$this->__construct($name, $options);
	}
	
	public function show(&$ioparams=array())
	{
		$apps = Factory::GetApps();
		$ddb = array();
		$appdocs = array();
		foreach ($apps as $key=>$v) {
			$file = RPATH_APPS.DS.$key.DS."docs".DS."manual.php";
			if (file_exists($file)) {
				require $file;
				$ddb = array_merge($ddb, $docdb);			
			}
		}
		
		$app = Factory::GetApp();
		$appname = $app->getAppName();
		
		$file = RPATH_APPS.DS.$appname.DS."docs".DS."manual.php";
		if (file_exists($file)) {
			require $file;
			$ddb = array_merge($ddb, $docdb);		
		}
		
		
		//filter
		$_ddb = array();
		foreach($ddb as $key=>$v) {
			if (isset($v['cname']) && !hasPrivilegeOf($v['cname']))
				continue;
				
			$_ddb[$key] = $v;			
		}
		
		$this->assign('ddb', $_ddb);
	}
}