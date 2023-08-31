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
		'my'=>array(
			'mid' => '100',
			'name'	=>'my',
			'class' => 'icon-home',
			'component'=>'my',
			'task'	=>'',
			'icon' => '',
			'pid' => '100',
			'parent'=> '',
			'sort'=>0
		),
		'my_info'=>array(
			'mid' => '101',
			'name'	=>'my_info',
			'component'=>'my_info',
			'task'	=>'',
			'icon' => '',
			'pid' => '101',
			'parent' => 'my',
			'sort'=>8,
			),
		
		
		'my_password'=>array(
			'mid'=>'102',		
			'name'	=>'my_password',
			'component'=>'my_password',
			'task'	=>'',
			'icon' => '',
			'pid'=>'102',
			'parent' => 'my',
			'sort'=>9,
			),
		'my_resetpassword'=>array(
			'mid'=>'102',		
			'name'	=>'my_resetpassword',
			'component'=>'my_resetpassword',
			'task'	=>'',
			'icon' => '',
			'pid'=>0,
			'hidden' => true,
			'parent' => 'my',
			),
			
		'my_ip'=>array(
			'mid' => '103',
			'name'	=>'my_ip',
			'component'=>'my_ip',
			'task'	=>'',
			'icon' => '',
			'pid' => '103',
			'parent' => 'my',
			'hidden' => true,
			),
		'login'=>array(
			'mid'=>'104',		
			'name'	=>'login',
			'component'=>'login',
			'task'	=>'',
			'icon' => '',
			'pid'=>'0',
			'hidden' => 'true',
			'parent' => 'my',
			),
		
		'seccode'=>array(
			'mid'=>'105',		
			'name'	=>'seccode',
			'component'=>'seccode',
			'task'	=>'',
			'icon' => '',
			'pid'=>'0',
			'hidden' => 'true',
			'parent' => 'my',
			),
			
		'logout'=>array(
			'mid'=>'106',		
			'name'	=>'logout',
			'component'=>'logout',
			'task'	=>'',
			'icon' => '',
			'pid'=>'0',
			'hidden' => 'true',
			'parent' => 'my',
			),
		
		'main'=>array(
			'mid'=>'11',
			'name'	=>'main',
			'component'=>'main',
			'task'	=>'',
			'icon' => '',
			'parent' => 'my',
			'pid'=>'1',
			),
		
);			
?>