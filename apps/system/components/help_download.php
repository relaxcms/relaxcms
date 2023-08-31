<?php

/**
 * @file
 *
 * @brief 
 * ÏÂÔØÒ³
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );


class HelpDownloadComponent extends CUIComponent
{
	function __construct($name, $options=null)
	{
		parent::__construct($name, $options);
	}
	
	function HelpDownloadComponent($name, $options=null)
	{
		$this->__construct($name, $options);
	}
	
	
	public function show(&$ioparams=array())
	{
		$apps = Factory::GetApps();
		$ddb = array();
		foreach ($apps as $key=>$v) {
			$file = RPATH_APPS.DS.$key.DS."docs".DS."download.php";
			if (file_exists($file)) {
				require $file;
				$ddb = array_merge($ddb, $downloaddb);			
			}
		}
		
		$app = Factory::GetApp();
		$appname = $app->getAppName();		
		$file = RPATH_APPS.DS.$appname.DS."docs".DS."download.php";
		if (file_exists($file)) {
			require $file;
			$ddb = array_merge($ddb, $downloaddb);		
		}
		
		$id = 1;
		foreach ($ddb as $key=>&$v) {
			$v['id'] = $id ++;
			if (!is_url($v['url'])) {
				$v['url'] = $ioparams['_webroot'].'/'.$v['url'];
			}
		}
		
		
		$this->assign('ddb', $ddb);
	}
	
}