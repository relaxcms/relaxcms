<?php
/**
 * @file
 *
 * @brief 
 * Listview模块
 *
 * Copyright (c), 2021, relaxcms.com
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class ListviewModule extends CListModule
{
	function __construct($name, $attribs)
	{
		parent::__construct($name, $attribs);
	}
	
	function ListviewModule($name, $attribs)
	{
		$this->__construct($name, $attribs);
	}
	
	public function show(&$ioparams=array())
	{
		$modname = isset($this->_attribs['modname'])?$this->_attribs['modname']:'content';
		$cid = isset($this->_attribs['cid'])?$this->_attribs['cid']:0;
		$vmask = isset($this->_attribs['vmask'])?$this->_attribs['vmask']:0;
		$keyword = isset($this->_attribs['keyword'])?$this->_attribs['keyword']:'';
		$notabhead = isset($this->_attribs['notabhead'])?intval($this->_attribs['notabhead']):0;
		$col = isset($this->_attribs['col'])?$this->_attribs['col']:6;


		if ($col >12 || $col < 1)		
			$col = 6;

		$params['__keyword'] = $keyword;

		$m = Factory::GetModel($modname);
		$sfdb = $m->getFieldsForSearch($params, $ioparams);

		//format vmask
		$_vmask = 0;
		if (is_numeric($vmask)) {
			$_vmask = intval($vmask);
		} else {
			$tdb = explode('|', $vmask);
			foreach ($tdb as $key => $v) {
				switch ($v) {
					case 'large':
						$_vmask |= 0x1;
						break;
					case 'listimg':
						$_vmask |= 0x2;
						break;
					case 'detail':
						$_vmask |= 0x4;
						break;					
					default:
						# code...
						break;
				}
			}
		}
				
		//默认视图
		$m2 = Factory::GetModel('catalog');
		$cataloginfo = $m2->get($cid);
		if ($cataloginfo) {
			!$vmask && $vmask = $cataloginfo['viewmode'];
			$this->assign('vmask', $_vmask);
			$this->assign('defaultviewtype', 1<<$cataloginfo['viewtype']);
		} else {
			$this->assign('vmask', $_vmask);
			$this->assign('defaultviewtype', 1);
		}	
		
				
		$this->assign('sfdb', $sfdb);
		$this->assign('col', $col);
		$this->assign('viewname', 'view'.$cid);
		$this->assign('_keyword', $keyword);
		$this->assign('modname', $modname);
		$this->assign('notabhead', $notabhead);
				
		
		return true;
	}	
}