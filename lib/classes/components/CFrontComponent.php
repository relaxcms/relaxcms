<?php

class CFrontComponent extends CDTComponent
{
	protected $_cid = 0;
	protected $_tid = 0;
	protected $_tinfo = null;
	
	function __construct($name, $options)
	{
		parent::__construct($name, $options);		
	}
	
	function CFrontComponent($name, $options)
	{
		$this->__construct($name, $options);
	}
	
	
	protected function init(&$ioparams=array())
	{
		parent::init($ioparams);
		
		//videojs
		$this->enableJSCSS('videojs');
		$this->enableJSCSS('admin', false);
		
		//id
		if (!$this->_id) {
			if (isset($ioparams['vpath'])) {
				$nr = count($ioparams['vpath']);
				for($i=$nr-1; $i>=0; $i--) {
					if (is_numeric($ioparams['vpath'][$i])) {
						$this->_id = intval($ioparams['vpath'][$i]);
						break;		
					}					
				}			
			}
		}
	}
	
	
		
	protected function preTask(&$ioparams=array())
	{
		//查询访问者信息
		$_client = $ioparams['_client'];
		$_useragent = $ioparams['_useragent'];
		
		$webid = $_client.'_'.$_useragent;
		
		$m = Factory::GetModel('webclient');
		$this->_tinfo = $m->getInfoByID($webid, $ioparams);		
	}	
	
	/* sh template show */
	protected function loadPortlet($tplinfo, &$ioparams=array())
	{
		$tplname = $tplinfo['name'];
		$m = Factory::GetModel('portlet');
		$udb = $m->gets("where tplname='$tplname'");
		
		$portlet = array();
		foreach ($udb as $key=>$v) {
			$name = 'portlet'.$v['pid'];
			$this->assign($name, $v);
			$portlet[] = $v;
		}
		
		$this->assign('portlet', $portlet);
	}	
	
	
	protected function postTask(&$ioparams=array())
	{
		$scf = Factory::GetSiteConfiguration();
		
		//tpl
		$m = Factory::GetModel('site_template');
		$tpl = isset($scf['template'])?$scf['template']:'default';
		$tplinfo = $m->get($tpl);
		if ($tplinfo) {
			$this->loadPortlet($tplinfo);
		}
	}	
	
	public function loadTemplate2($ioparams=array(), $tdir = '')
	{
		$scf = Factory::GetSiteConfiguration();
		
		$tplname = $scf['template'];
		
		$tdb = get_tpls($tplname);
		if (isset($tdb['portlet'])) {			
			$this->set_var('portlet', $tdb['portlet']);
		}
		
		//LOGO
		!isset($scf['logo']) && $scf['logo'] = $ioparams['_dstroot'].'/'.$tplname.'/img/logo.png';

		$this->set_var('scf', $scf);		
		$this->set_var('cid', $this->_cid);
		$this->set_var('tid', $this->_tid);
		
		return parent::loadTemplate($ioparams);
	}
	
	protected function loadTemplate(&$ioparams = array())
	{
		$scf = Factory::GetSiteConfiguration();
				
		$tplname = $scf['template'];
		
		$tdir = $this->_ptdir.DS.$tplname;
		if (!is_dir($tdir)){
			$tdir = RPATH_TEMPLATES.DS.$tplname;
			if (!is_dir($tdir)){
				$tdir = $this->_default_tdir;
			}
		}
			
		$ioparams['tdir'] = $tdir;
		
		$tdb = get_tpls($tplname);
		if (isset($tdb['portlet'])) {			
			$this->assign('portlet', $tdb['portlet']);
		}
		
		//LOGO
		!isset($scf['logo']) && $scf['logo'] = $ioparams['_dstroot'].'/img/logo.png';
		$this->assign('scf', $scf);		
		$this->assign('cid', $this->_cid);
		$this->assign('tid', $this->_tid);
		
		
		$res = parent::loadTemplate($ioparams);
		
		return $res;
	}
	
	protected function vplay(&$ioparams=array())
	{
		$this->enableJSCSS('videojs');
		$this->_template = 'dt_vplay';		
		
		$id = $this->_id;
		if (!$id) {
			show_error('str_params_error');
			return false;
		}
		
		$fields = array();
		
		$m = Factory::GetModel('content');		
		$params = $m->getForView($id, $ioparams);		
		if (!$params) {
			show_error('str_notfound_error');
			return false;
		}
		
		//mimetype
		if (strstr($params['playurl'], "m3u8")) {
			$params['mimetype'] = "application/x-mpegURL";
		} else {
			$params['mimetype'] = "video/mp4";
		}
		$m->incHits($id);
		
		$this->assign('params', $params);
	}
	
	
	protected function detail(&$ioparams=array())
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
}