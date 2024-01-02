<?php

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class CContentModule extends CModule
{
	function __construct($name, $attribs)
	{
		parent::__construct($name, $attribs);
	}
	
	function CContentModule($name, $attribs)
	{
		$this->__construct($name, $attribs);
	}
	
	protected function formatTitle(&$row, $maxlen)
	{

		if ($maxlen > 0)
			$row['title'] = utf8_substr($row['name'], 0, $maxlen);	
	}
	
	
	protected function formatDateTime(&$row, $time_format)
	{
		$row['timelong'] = tformat_timelong($row['ts']);
		if ($time_format) {
			$row['time'] = tformat($row['ts'], $time_format);
		} else {
			$vt1 = tformat_vtime($row['ts']);
			$vt2 = tformat_vtime(time());
			$tf = ($vt1['year'] != $vt2['year'])?'Y-m-d':'m-d';
			$row['time'] = tformat($row['ts'], $tf);
		}
	}

	protected function getList($params, $num, $ioparams)
	{
		$m =  Factory::GetModel('content');
		$udb = $m->getList($params, $num, $ioparams); 
		
		return $udb;
	}
	
	public function show(&$ioparams=array())
	{
		$res = parent::show($ioparams);
		
		$flags = isset($this->_attribs['flags'])?intval($this->_attribs['flags']):0;
		$num = isset($this->_attribs['num'])?intval($this->_attribs['num']):12;
		$cid = isset($this->_attribs['cid'])?intval($this->_attribs['cid']):0;
		$mid = isset($this->_attribs['mid'])?$this->_attribs['mid']:'';
		
		!$num && $num = 6;
		
		$this->_attribs["num"] = $num; 
		$this->_attribs["flags"] = $flags;
		
		
		$maxlen = isset($this->_attribs['maxlen'])?intval($this->_attribs['maxlen']):128;
		$notitle = isset($this->_attribs['notitle'])?true:false;
		$time_format = isset($this->_attribs['time_format'])?$ioparams['time_format']:'';
		
		
		$params = array();
		if ($flags > 0)
			$params['flags'] = $flags;
		if ($cid > 0)
			$params['cid'] = $cid;
			
		$params['module_id'] = $mid;
		
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $params);
		
		$udb = $this->getList($params, $num, $ioparams);
		
		$moreurl='';
		$_udb = array();
		foreach ($udb as $key=>&$v) {
			if ($v['cid'] > 0 && !$moreurl) {
				$moreurl = $ioparams['_webroot'].'/list/'.$v['cid'];
			}
			
			$this->formatTitle($v, $maxlen);
			$this->formatDateTime($v, $time_format);
			
			$_udb[] = $v;
		}
		
		if ($cid > 0) {
			$m2 = Factory::GetModel("catalog");
			$catalog = $m2->getForView($cid, $ioparams);
			$this->assign('catalog', $catalog);
		}
		

		$this->setColumn($udb);

		$this->assign('udb', $udb);	
		$this->assign('_udb', $_udb);	

		$this->assign('mid', 'mod'.$mid);	
		
		return $udb;		
	}	

	protected function getCols()
	{
		return isset($this->_attribs['cols'])?intval($this->_attribs['cols']):2;		
	}

	protected function setColumn($udb)
	{
		$nr = count($udb);
		
		$nr_col = $this->getCols();
		$nr_row = ceil($nr/$nr_col);
		$col_width = floor(12/$nr_col);

		$this->assign('rows', $nr_row);	
		$this->assign('cols', $nr_col);	
		$this->assign('col_width', $col_width);	
		$this->assign('nr', $nr);

	}
}