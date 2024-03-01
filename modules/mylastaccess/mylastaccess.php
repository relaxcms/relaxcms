<?php
/**
 * @file
 *
 * @brief 
 * 最近访问模块
 *
 */
class MylastaccessModule extends CModule
{
	function __construct($name, $attribs)
	{
		parent::__construct($name, $attribs);
		$this->_attribs['task'] = 'show';
	}
	
	function MylastaccessModule($name, $attribs)
	{
		$this->__construct($name, $attribs);
	}
	
	protected function show(&$ioparams=array())
	{
		//访问历史
		$m = Factory::GetModel('history');
		$udb = $m->getHistory();		
		$menus = Factory::GetApp()->getMenus();		
		$hdb = array();
		if ($udb) {
			foreach ($udb as $key=>$v1) {
				if (isset( $menus[$v1['cname']])) {
					$v1['title'] = $menus[$v1['cname']]['title'];			
					$v1['url'] = $ioparams['_basename'].'/'.$v1['cname'];
					$hdb[] = $v1;				
				}
			}
		}
		
		$this->assign('hisdb', $hdb);
	}	
}