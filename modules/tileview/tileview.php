<?php

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class TileviewModule extends CContentModule
{
	function __construct($name, $attribs)
	{
		parent::__construct($name, $attribs);
	}
	
	function TileviewModule($name, $attribs)
	{
		$this->__construct($name, $attribs);
	}
	
	public function show(&$ioparams=array())
	{
		$udb = parent::show($ioparams);
		
		$view = isset($this->_attribs['view'])?$this->_attribs['view']:'large';
		$vmask = isset($this->_attribs['vmask'])?$this->_attribs['vmask']:7;
		
		
		//0-large, 1-listimg, 2-detail
		$all_view_list  = array(
				'large'=>array('name'=>'large', 'className'=>'fa-th-large'),
				'listimg'=>array('name'=>'listimg','className'=>'fa-th-list'),
				'detail'=>array('name'=>'detail','className'=>'fa-list'));
			
		$enable_view_list  = array();
		if ($vmask&1)
			$enable_view_list['large'] = $all_view_list['large'];
		if ($vmask&2)
			$enable_view_list['listimg'] = $all_view_list['listimg'];
		if ($vmask&4)
			$enable_view_list['detail'] = $all_view_list['detail'];
		
		if (!$enable_view_list)
			$enable_view_list['large'] = $all_view_list['large'];	

		if (empty($enable_view_list[$view])) 
			$view = 'large';
			
		foreach($all_view_list as $key=>$v) {
			if ($key == $view) {
				$this->assign('active_'.$key, '');
				$this->assign('active_switchview_class', $v['className']);
			} else {
				$this->assign('active_'.$key, 'hidden');
			}
		}
		
		$_enable_view_list = array();
		foreach ($enable_view_list as $key=>$v) {
			$_enable_view_list[] = $v;
		}
				
		$this->assign('view', $view);
		$this->assign('enable_view_list', CJson::encode($_enable_view_list));
		
		
		return $udb;
		
	}	
}