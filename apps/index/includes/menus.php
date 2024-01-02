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

		'list'=>array(
			'mid' => '100',
			'name'	=>'list',
			'component'=>'list',
			'modname'=>'content',
			'task'	=>'',
			'icon' => '',
			'pid' => 0,
			'parent'=> '',
			),
		'register'=>array(
			'mid' => '100',
			'name'	=>'register',
			'component'=>'register',
			'task'	=>'',
			'icon' => '',
			'pid' => 0,
			'parent'=> '',
			),
		);			
?>