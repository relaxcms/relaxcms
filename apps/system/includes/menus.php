<?php
/**
 * @file
 *
 * @brief 
 * 初始菜单
 *
 */

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

$menus = array(
		
			
		//system 11				
		'system'=>array(
			'mid'=>'300',
			'name'	=>'system',
			'component'=>'system',
			'task'	=>array('show'=>'r', 'reboot'=>'x','shutdown'=>'x'),
			'icon' => '',
			'class' => 'icon-settings',
			'pid'=>'300',
			'parent' => '',
			'level' => 2,
			'pos'=> 'top',
			'sort'=>13,
			),
		
		'api'=>array(
			'mid'=>'324',		
			'name'	=>'api',
			'component'=>'api',
			'task'	=> array('helloapi','localwebservice'=>'i', 'todoForNew'=>'i'),
			'icon' => '',
			'level' => 7,
			'pid'=>'0',
			'hidden' => true,
			'parent' => 'system',
			),
		'file'=>array(
			'mid' => 701,
			'name'	=>'file',
			'component'=>'file',
			'task'	=>array('getFrameFromVideo'=>'i', 'f'=>'i'),
			'modname' => 'file',
			'parent' => 'system',
			'level' => 1,	
			'sort'=>6,	
			),
		'storage'=>array(
			'mid' => 701,
			'name'	=>'storage',
			'component'=>'storage',
			'task'=>'',
			'parent' => 'system',
			'level' => 1,	
			'sort'=>6,	
			),
		
		
		
		'msg'=>array(
			'mid' => 701,
			'name'	=>'msg',
			'component'=>'msg',
			'task'	=>'',
			'modname' => 'msg',
			'parent' => 'system',
			'level' => 1,	
			'sort'=>7,	
			),
		
		
		/*
		'system_var'=>array (
			'mid'=>313,
			'name'	=>'system_var',
			'component'=>'system_var',
			'task'=>array('show'=>'r', 'edit'=>'w'),
			'icon' => '',
			'parent' => 'system',
			'level' => 2
			),*/

		'system_config'=>array(
			'mid'=>'302',
			'name'	=>'system_config',
			'component'=>'system_config',
			'task'	=>array('show'=>'r', 'apply'=>'w'),
			'icon' => '',
			'pid'=>'302',
			'level' => 2,
			'parent' => 'system',
			'is_default'=>true,
			'sort'=>1,
			),
			
		'system_user'=>array(
			'mid'=>'306',
			'name'	=>'system_user',
			'component'=>'system_user',
			'task'	=>array('show'=>'r', 'add'=>'a', 'edit'=>'u', 'del'=>'d'),
			'icon' => '',
			'level' => 2,
			'parent' => 'system',
			'sort'=>3,
			),	
		'system_group'=>array(
			'mid'=>'307',
			'name'	=>'system_group',
			'component'=>'system_group',
			'task'	=>array('show'=>'r', 'add'=>'a', 'edit'=>'u', 'delete'=>'d'),
			'icon' => '',
			'pid'=>'307',
			'level' => 2,
			'parent' => 'system',
			'sort'=>4,		
			),	
		'system_role'=>array(
			'mid'=>'308',
			'name'	=>'system_role',
			'component'=>'system_role',
			'task'	=>array('show'=>'r', 'add'=>'a', 'edit'=>'u', 'delete'=>'d'),
			'icon' => '',
			'pid'=>'308',
			'level' => 2,
			'parent' => 'system',
			'sort'=>5,		
			),	
		'system_log'=>array(
			'mid'=>'309',
			'name'	=>'system_log',
			'component'=>'system_log',
			'task'	=>array('show'=>'r', 'delete'=>'d'),
			'icon' => '',
			'pid'=>'309',
			'level' => 2,
			'parent' => 'system',
			'sort'=>6,		
			),	
			
		'system_backup'=>array(
			'mid'=>'310',
			'name'	=>'system_backup',
			'component'=>'system_backup',
			'task'	=>array('show'=>'r', 'add'=>'w'),
			'icon' => '',
			'pid'=>'310',
			'level' => 2,
			'parent' => 'system',
			'sort'=>8,		
			),	
		
		'system_super'=>array(
			'mid'=>'311',
			'name'	=>'system_super',
			'component'=>'system_super',
			'task'	=>'',
			'icon' => '',
			'pid'=>'311',
			'level' => 8,
			'parent' => 'system',
			'sort'=>9,
			),
		'system_var'=>array (
			'mid'=>313,
			'name'	=>'system_var',
			'component'=>'system_var',
			'task'=>array('show'=>'r', 'edit'=>'w'),
			'icon' => '',
			'parent' => 'system',
			'level' => 2,
			'sort'=>10,
			),
		'system_menu'=>array (
			'mid'=>'602',
			'name'	=>'system_menu',
			'component'=>'system_menu',
			'task'	=>'',
			'icon' => '',
			'parent' => 'system',
			'level' => 2,	
			'sort'=>10,		
			),
			
		'system_app'=>array (
			'mid'=>'602',
			'name'	=>'system_app',
			'component'=>'system_app',
			'task'	=>'',
			'icon' => '',
			'parent' => 'system',
			'level' => 2,	
			'sort'=>11,		
			),
		
			
		//help menu items
		'help'=>array(
			'mid'=>'400',
			'name'	=>'help',
			'component'=>'help',
			'task'	=>'',
			'icon' => '',
			'class' => 'icon-question',
			'pid'=>'400',
			'parent' => '',
			'sort'=>14,
			),	
		
		'help_version'=>array(
			'mid'=>'402',
			'name'	=>'help_version',
			'component'=>'help_version',
			'task'	=>'',
			'icon' => '',
			'pid'=>'402',
			'parent' => 'help',
			),
		'help_sysinfo'=>array(
		'mid'=>'403',
			'name'	=>'help_sysinfo',
			'component'=>'help_sysinfo',
			'task'	=>'',
			'icon' => '',
			'parent' => 'help',
			),
			
		'help_license'=>array(
			'mid'=>'404',
			'name'	=>'help_license',
			'component'=>'help_license',
			'task'	=>'',
			'icon' => '',
			'parent' => 'help',
			),
				
			
		'help_restfulapi'=>array(
			'mid'=>'403',
			'name'	=>'help_restfulapi',
			'component'=>'help_restfulapi',
			'task'	=>'',
			'icon' => '',
			'parent' => 'help',
			'linkurl'=>'docs/api',
			),

		'help_manual'=>array(
			'mid'=>'405',
			'name'	=>'help_manual',
			'component'=>'help_manual',
			'task'	=>'',
			'icon' => '',
			'parent' => 'help',
			),
		'help_download'=>array(
			'mid'=>'406',
			'name'	=>'help_download',
			'component'=>'help_download',
			'task'	=>'',
			'icon' => '',
			'parent' => 'help',
			
			),
			
		'help_upgrade'=>array(
			'mid'=>'407',
			'name'	=>'help_upgrade',
			'component'=>'help_upgrade',
			'task'	=>array('checkICloudClientVersion'=>'i'),
			'icon' => '',
			'level' => 2,			
			'parent' => 'help',
			),
			
			
		'help_about'=>array(
			'mid'=>'408',
			'name'	=>'help_about',
			'component'=>'help_about',
			'task'	=>'',
			'icon' => '',
			'parent' => 'help',
			'hidden'=>true,
			),
);			
?>