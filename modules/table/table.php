<?php
/**
 * @file
 *
 * @brief 
 *
 * Table 模块
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class TableModule extends CModule
{
	function __construct($name, $attribs)
	{
		parent::__construct($name, $attribs);
	}
	
	function TableModule($name, $attribs)
	{
		$this->__construct($name, $attribs);
	}
	
	protected function show(&$ioparams=array())
	{
		$modname = isset($this->_attribs['modname'])?$this->_attribs['modname']:'';
		$vmask = isset($this->_attribs['vmask'])?$this->_attribs['vmask']:4;
		$keyword = isset($this->_attribs['keyword'])?$this->_attribs['keyword']:'';
		$notoolbar = isset($this->_attribs['notoolbar'])?intval($this->_attribs['notoolbar']):0;
		$notabhead = isset($this->_attribs['notabhead'])?intval($this->_attribs['notabhead']):0;
		$col = isset($this->_attribs['col'])?$this->_attribs['col']:6;
		$hidefields = isset($this->_attribs['hidefields'])?$this->_attribs['hidefields']:'';
		$comname = isset($this->_attribs['comname'])?$this->_attribs['comname']:$modname;
		
		//hiddenfields="invest_account_id,sum1"


		if ($col >12 || $col < 1)		
			$col = 6;

		$params = array();
		$params['__keyword'] = $keyword;
		
		$m = Factory::GetModel($modname);
		$modinfo = $m->getModelInfo();

		$fdb = $modinfo['fdb'];
		$hidden_filter_fields = array();
		foreach ($fdb as $key => $v) {
			$__key = '__'.$key;
			if (isset($this->_attribs[$__key])) {
				$hidden_filter_fields[$key] = $this->_attribs[$__key];				
			}
		}

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
		$this->assign('vmask', $_vmask);
		$this->assign('defaultviewtype', 1);
					
		$this->assign('sfdb', $sfdb);
		$this->assign('col', $col);
		$this->assign('viewname', 'view'.$cid);
		$this->assign('_keyword', $keyword);
		$this->assign('modname', $modname);
		$this->assign('notabhead', $notabhead);
		$this->assign('notoolbar', $notoolbar);
		$this->assign('hidden_filter_fields', $hidden_filter_fields);
		
		$this->assign('hidefields', $hidefields);
		$this->assign('comname', $comname);
		
		
		return true;
	}	
}