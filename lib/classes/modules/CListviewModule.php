<?php

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class CListviewModule extends CModule
{
	function __construct($name, $attribs)
	{
		parent::__construct($name, $attribs);
	}
	
	function CListviewModule($name, $attribs)
	{
		$this->__construct($name, $attribs);
	}
	
	protected function getModel()
	{
		return isset($this->_attribs['modname'])?$this->_attribs['modname']:'content';
	}
	
	public function show(&$ioparams=array())
	{
		$res = parent::show($ioparams);
		
		$modname = $this->getModel();
		$vmask = isset($this->_attribs['vmask'])?$this->_attribs['vmask']:0;
		$keyword = isset($this->_attribs['keyword'])?$this->_attribs['keyword']:'';
		$notabhead = isset($this->_attribs['notabhead'])?intval($this->_attribs['notabhead']):0;
		$col = isset($this->_attribs['col'])?$this->_attribs['col']:6;
		$nosidebar = isset($this->_attribs['nosidebar'])?intval($this->_attribs['nosidebar']):false;	
		
		
		if ($col >12 || $col < 1)		
			$col = 6;
		
		$params['__keyword'] = $keyword;
		
		$m = Factory::GetModel($modname);
		$sfdb = $m->getFieldsForSearch($params, $ioparams);
		$modinfo = $m->getModelInfo();
		$pkey = $modinfo['pkey'];
		$this->assign('pkey', $fields[$pkey]);
		
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
		
		$this->assign('vmask', $_vmask);
		$this->assign('defaultviewtype', 1);
		
		$this->assign('sfdb', $sfdb);
		$this->assign('col', $col);
		$this->assign('viewname', 'view'.$cid);
		$this->assign('_keyword', $keyword);
		$this->assign('modname', $modname);
		$this->assign('notabhead', $notabhead);
		$this->assign('nosidebar', $nosidebar?"nosidebar":"");
		
		//$this->assign('tablename', $modname);
		//$mi18n  = get_i18n('mod_'.$modname);
		//$this->assign('mi18n', $mi18n);
		//$mi18n[modelname]
		//!$table_title && $table_title = $mi18n['modelname'];
		//$this->assign('table_title', $table_title);
		
	}	
}