<?php

/**
 * @file
 *
 * @brief 
 * 系统信息
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );


class HelpSysinfoComponent extends CUIComponent
{
	function __construct($name, $options=null)
	{
		parent::__construct($name, $options);
	}
	
	function HelpSysinfoComponent($name, $options=null)
	{
		$this->__construct($name, $options);
	}
	
	public function show(&$ioparams=array())
	{		
		$params = array();
		
		// NOTE: When accessing a element from the above phpinfo_array(), you can do:
		//$array = phpinfo(INFO_GENERAL);
		$params['system'] = php_uname();  
		$params['os'] = PHP_OS;
		
		$db = Factory::GetDBO();
		$cf = get_config();
		
		$params['systime'] = tformat_current();
		$params['php_version'] = PHP_VERSION;
				
		if(function_exists("gd_info")) {
			$gd = gd_info();
			$params['gdinfo'] = $gd['GD Version'];
		} else  {
			$params['gdinfo'] = '<span style="color:red">Unknown</span>';
		}
		$params['serverinfo'] = $_SERVER['SERVER_SOFTWARE'];
		$params['useragentinfo'] = $_SERVER['HTTP_USER_AGENT'];
		
		$params['allowurl'] = ini_get('allow_url_fopen') ? '<span style="color:green">Supported</span>' : '<span style="color:red">Not supported</span>';

		$params['max_upload'] = ini_get('file_uploads') ? ini_get('upload_max_filesize') : '<span style="color:red">Disabled</span>';
		$params['post_max_size'] = ini_get('file_uploads') ? ini_get('post_max_size') : '<span style="color:red">Disabled</span>';

		$params['max_execution_time'] = ini_get('max_execution_time').' seconds';
		$params['dbinfo'] = $db->get_dbinfo();
		$params['database_space'] = $db->db_space();
		$params['database_name'] = $db->db_name();
		
		$free = disk_free_space(RPATH_ROOT);
		$total = disk_total_space(RPATH_ROOT);
				
		$params['disk_space'] = nformat_size($free);
		$params['disk_total'] = nformat_size($total);
		
		//rkey
		$rkeyversion = phpversion('rkey');
		if (!$rkeyversion)
			$rkeyversion = '<span style="color:red">Not supported</span>';
				
		$params['rkey'] = $rkeyversion;
		
		$this->assign('sysinfo', $params);	
		
		//license
		$id = 1;
		$plugins = array();
		foreach ($this->_jscssdb['plugins'] as $key=>$v) {
			if (isset($v['showlicense']) && $v['showlicense'] == true) {
				$v['id'] = $id++;
				$plugins[$key] = $v;				
			}
		}
		
		//tcpdf
		//http://www.gnu.org/copyleft/lesser.html GNU-LGPL
		$p = array();
		$p['id'] = $id++;
		$p['name'] = "TCPDF";
		$p['version'] = "6.2.13";
		$p['description'] = "TCPDF library, Nicola Asuni - Tecnick.com LTD - www.tecnick.com - info@tecnick.com";
		$p['licensename']='LGPLv3';
		$p['licenseurl']='http://www.gnu.org/copyleft/lesser.html';
		$plugins[] = $p;
		
		
		$this->assign('plugins', $plugins);	
	}
}