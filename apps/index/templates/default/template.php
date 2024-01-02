<?php
$template = array(
		'name' => 'default',
		'title' => '默认',
		'tplpreview' => 'front/img/template.jpg',
		'description'=>'默认模板',
		'index' => array(
			'index' => '默认首页',			
			),		
		'list' => array(
			'list' => '默认列表',
			'vhost' => '虚拟主机',
		),	
		
		'models'=>array(
			
			
			'catalog' => array(
				'1' => array(
					'title' => '产品系列',
					'models'=>array('content'=>array('name'=>'content')),
					'content'=>array(					
						array('title' => '默认'),),
					),
				'2' => array(
					'title' => '本网公告',
					),
				)
				),
		);
?>