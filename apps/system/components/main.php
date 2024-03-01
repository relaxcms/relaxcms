<?php

/**
 * @file
 *
 * @brief 
 * 起始页
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );


class MainComponent extends CUIComponent
{
	function __construct($name, $options=null)
	{
		parent::__construct($name, $options);
	}
	
	function MainComponent($name, $options=null)
	{
		$this->__construct($name, $options);
	}
		
	public function show(&$ioparams=array())
	{
		parent::show($ioparams);
		
		//$this->enableJSCSS('amcharts5');
		
		//所有已经安装的主页
		$dashbordinfo = array();
		$apps = Factory::GetApps();
		$tags = array();
		
		foreach ($apps as $key=>$v) {
			$app = Factory::GetApp($key);
			if (!$app)
				continue;
				
			if (($di = $app->getDashbordInfo($ioparams))) {
				$tags[$key] = $di;
				foreach ($di as $key2=>$v2) {
					if (hasPrivilegeOf($v2['cname'])) {
						$dashbordinfo[] = $v2;
					}
				}
			}
		}
		
		// 当前APP
		$app = Factory::GetApp();
		if (($di = $app->getDashbordInfo($ioparams))) {
			foreach ($di as $key2=>$v2) {
				if (hasPrivilegeOf($v2['cname'])) {
					$dashbordinfo[] = $v2;
				}
			}
		}
		
		// 2*2 宫格
		$layout0 = array();
		$layout1 = array();
		$layout2 = array();
		$layout3 = array();
		
		foreach ($dashbordinfo as $v) {
			switch($v['layout']) {
				case 1:
					$layout1[] = $v;
					break;
				case 2:
					$layout2[] = $v;
					break;
				case 3:
					$layout3[] = $v;
					break;
				default:
					$layout0[] = $v;
					break;
			}
		}
		$nr_col = 0;
		if ($layout0)
			$nr_col ++;
		if ($layout1)
			$nr_col ++;
		if ($layout2)
			$nr_col ++;
		if ($layout3)
			$nr_col ++;
			
		!$nr_col && $nr_col = 1;
		$col_width = 12/$nr_col;
		
		
		$this->assign('col_width', $col_width);
		$this->assign('layout0', $layout0);
		$this->assign('layout1', $layout1);
		$this->assign('layout2', $layout2);
		$this->assign('layout3', $layout3);
		
		
		$nr = count($layout0);
		$rows = ceil($nr / 4);
		$this->assign('rows', $rows);
	}	
}