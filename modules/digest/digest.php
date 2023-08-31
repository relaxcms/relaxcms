<?php

defined('RPATH_BASE') or die();

class DigestModule extends CModule
{
	function __construct($name, $attribs)
	{
		parent::__construct($name, $attribs);
	}
	
	function DigestModule($name, $attribs)
	{
		$this->__construct($name, $attribs);
	}
	
	public function show(&$ioparams=array())
	{
		$flags = $this->_attribs["flags"];
		$num = $this->_attribs['num'];
		$title = isset($this->_attribs['title'])?$this->_attribs['title']:'';
		$tpl = isset($this->_attribs['tpl'])?$this->_attribs['tpl']:'';
		$maxlen = isset($this->_attribs['maxlen'])?intval($this->_attribs['maxlen']):128;
		$cid = isset($this->_attribs['cid'])?intval($this->_attribs['cid']):0;
		$cols = isset($this->_attribs['cols'])?intval($this->_attribs['cols']):2;
		$notitle = isset($this->_attribs['notitle'])?true:false;
		
		$m = Factory::GetModel('content');		
		$params = array();
		
		$params['flags'] = $flags;
		if ($cid > 0) {
			$params['cid'] = $cid;
		}
		
		$params['flags'] = $flags;
		
		$params['orderby'] = " order by ts desc";
		$ioparams['maxlen'] = $maxlen;
		$udb = $m->getListForFrontend($params, $num, $ioparams); 
		
		$moreurl='';
		$_udb = array();
		foreach ($udb as $key=>$v) {
			if ($v['cid'] > 0 && !$moreurl) {
				$moreurl = $ioparams['_webroot'].'/list/'.$v['cid'];
			}
			$_udb[] = $v;
		}
		
		if ($cid > 0) {
			$m2 = Factory::GetModel("catalog");
			$catalog = $m2->getCatalogById($cid);
			!$title && $title = $catalog['name'];
			$this->set_var('catalog', $catalog);
		}
		
		$col_width = 12/$cols;
				
		$this->set_var('title', $title);	
		$this->set_var('moreurl', $moreurl);	
		$this->set_var('udb', $udb);	
		$this->set_var('_udb', $_udb);	
		$this->set_var('cols', $cols);	
		$this->set_var('col_width', $col_width);	
		$this->set_var('notitle', $notitle);	
		if ($tpl)
			$this->_template = $tpl;	
		
	}	
}