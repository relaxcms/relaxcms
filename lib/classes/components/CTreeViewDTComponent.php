<?php

/***
 * 
 * @file
 *
 * @brief 
 *  
 * */

defined( 'RMAGIC' ) or die( 'Restricted access' );

class CTreeViewDTComponent extends CDTComponent
{
	function __construct($name, $options=null)
	{
		parent::__construct($name, $options);
	}
	
	function CTreeViewDTComponent($name, $options=null)
	{
		$this->__construct($name, $options);
	}
	
	protected function show(&$ioparams=array()) 
	{
		parent::show($ioparams);
		
		$id = $this->_id;
		$tablename = $this->_modname;
		$table_id = $tablename.rand();
		
		
		$sortName = isset($_COOKIE['sortName'])?$_COOKIE['sortName']:'';
		$sortOrder = isset($_COOKIE['sortOrder'])?$_COOKIE['sortOrder']:'';
		
		$m = $this->getModel();
		
		//²éÑ¯
		$parentdb = array();
		$m->getParents($id, $parentdb);
		
		$params = array('pid'=>$id, 'order'=>$sortName, 'dir'=>$sortOrder);
		$this->initParamsForShow($params, $ioparams);
		
		$rows = $m->selectForView($params, $ioparams);
		$tabledata = $params['rows'];
		
		$modinfo = $m->getModelInfo();
		$fdb = $modinfo['fdb'];
		$pkey = $modinfo['pkey'];
		
		$_base = $ioparams['_base'];
		foreach($tabledata as $key=>&$v)  {
			$name = $v['name'];
			$id = $v['id'];
			if ($m->hasChildren($id))
				$v['name'] = "<a href='$_base?id=$id'>$name</a>";
		}
		
		
		//array_reverse($parentdb);
		//nav
		$nav = '';
		foreach ($parentdb as $k2 => $v2) {
			$nav = "<i class='fa fa-angle-right'> </i> <a href='$_base?id=$v2[id]'> $v2[name] </a>".$nav;
		}
		
		$this->assign('sortName', $sortName);
		$this->assign('sortOrder', $sortOrder);			
		$this->assign('nav', $nav);			
		$this->assign('table_id', $table_id);			
		$this->assign('tablename', $tablename);
		$mi18n = get_i18n('mod_'.$tablename);
		$this->assign('mi18n', $mi18n);		
		$this->assign('fdb', $fdb);
		$this->assign('pkey', $fdb[$pkey]);
		$this->assign('tabledata', $tabledata);		
		
		$this->setTpl('treeview');
		return $res;	
	}
}