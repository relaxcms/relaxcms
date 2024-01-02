<?php

/***
 * @file
 * 
 * ÄÚÈİ
 * 
 * */

defined( 'RMAGIC' ) or die( 'Restricted access' );

class ContentComponent  extends CFrontComponent
{
	protected $tid;
	
	function __construct($name, $options=null)
	{
		parent::__construct($name, $options);
	}
	
	function ContentComponent ($name, $options=null)
	{
		$this->__construct($name, $options);	
	}
	
	protected function _init_template($cid)
	{
		if ($cid) {
			$catalog = &get_catalog();
			$cdb = $catalog[$cid];
			if ($cdb['tpl_content_root']) {
				$this->_tdir = $cdb['tpl_content_root'];
				$this->set_var('tplbase', "templates/$this->_tdir");
			}
		}
	}
	
	public function show(&$ioparams=array(), $tid=0)
	{
		//http://localhost/rc4/content/5
		if (!$tid)
			$tid = $this->_id;
			
		!$tid && $tid = isset($ioparams['id'])?$ioparams['id']:0;
		!$tid && show_error('parameter error');
		
		$this->_tid = $tid;
		
		$m = Factory::GetModel('content');
		$res = $m->get($tid);
		!$res && show_error('NOT FOUND CONTENT!');
		
		//link
		if ($res['link'])
			redirect($res['link']);
		
		$cid = $this->_cid = $res['cid'];	
		$m->incHits($tid);	
		
		$this->assign('tid', $tid);
		$this->assign('_content_title', $res['title']);
		
		$ioparams['_url'] = $ioparams['_weburl'].'/content/'.$tid;

		$m2 = Factory::GetModel('catalog');
		$catalog = $m2->get($cid);
		
		$this->setTpl($catalog["tpl_content"]? $catalog["tpl_content"] : "content");
		$this->assign("params", $res);
		
	}

	protected function tvshow(&$ioparam=array())
	{
		$this->_template = "content_tvshow";

		$id = $this->getId($ioparam);
		$m = Factory::GetModel('content');
		$params = $m->getForWebview($id);
		if (!$params)
			exit("404 error");
		$this->assign('params', $params);

	}
}