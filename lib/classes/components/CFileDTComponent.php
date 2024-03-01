<?php

class CFileDTComponent extends CDTComponent
{
	function __construct($name, $options)
	{
		parent::__construct($name, $options);
	}
	
	function CFileDTComponent($name, $options)
	{
		$this->__construct($name, $options);
	}		
	
	protected function show(&$ioparams=array())
	{
		parent::show($ioparams);
		//$this->enableJSCSS(array('bgallery'));
		$this->setTpl('file');
	}
	
	protected function f(&$ioparams=array())
	{
		//http://localhost/rc4/lib/themes/system/php/t.php/file/62/0/3.mp4
		$vpath = $ioparams['vpath'];
		$action = '';
		$id = 0;	
		$w = 0;
		$h = 0;
		$l = 0;
		
		foreach ($vpath as $key=>$v) {
			
			if (method_exists($this, $v)){
				$action = $v;
				continue;
			}
			
			if (!$id && is_numeric($v)) {
				$id = intval($v);
				continue;
			}	
			
			if (!$w && is_numeric($v)) {
				$n = intval($v);
				if ($n <= 2)
					$l = $n;
				else 
					$w = $n;
				continue;
			}
			
			if (!$h && is_numeric($v)) {
				$h = intval($v);
				continue;
			}	
		}
		
		if ($action && $action != 'show') {
			$res = $this->$action($ioparams, $id, $l, $w, $h);
		} else {
			$m = Factory::GetModel('file');
			$res = $m->read($id, $ioparams);	
		}
		return $res;
	}
	
	protected function fileselectorForSelected(&$ioparams=array())
	{
		$mid = $this->requestInt('mid');
		$name = $this->request('name');
		$aids = $this->request('aids');
		$m = Factory::GetModel($this->_modname);		
		$res = $m->getGalleryForSelected($mid, $name, $aids, $ioparams);		
		if (!$res)
			showStatus(-1);
		else
			showStatus(0, $res);
		
		exit;
	}
	
	protected function setgallery(&$ioparams=array())
	{
		//&$ioparams=array()
		$m = Factory::GetModel('file2model');
		
		$modinfo = $m->getModelInfo();
		
		if ($this->_sbt) {
			$this->getParams($params);
			
			//
			$params['modname'] = $this->_modname;
			$params['mid'] = $this->_modname;
			
			$res = $m->set($params, $ioparams);
			
			$data = array();
			if (isset($ioparams['data']))
				$data = $ioparams['data'];
			
			showStatus($res, $data);
			
			return $res;
		}
		
		$modname = $modinfo['name'];		
		$table_id = 'mod_table_'.$modname;
		$this->assign('table_id', $table_id);
		
		$params = $m->get($this->_id);
		$tname = $this->_task;
		if ($this->_id == 0){		
			$fields = $m->getFieldsForInputAdd($params, $ioparams);
		} else {
			$fields = $m->getFieldsForInputEdit($params, $ioparams);
		}
		if (!$fields) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "WARNING: getFieldsForInput$tname failed!", $tname, $modinfo);
			$fields = array();
		}
		
		$this->assign('fields', $fields);		
		
		//$this->initTools('edit');
		$mi18n  = get_i18n('mod_'.$tablename);
		$this->assign('mi18n', $mi18n);
		
		$this->initTools($tname);
		$this->setColumns($fields, false);
		$this->setTpl('dt_edit');
		
		return $fields;
	}
	
	protected function fileselectorForSetGallery(&$ioparams=array())
	{
		$ioparams['task'] = 'fileselectorForSetGallery';
		return $this->setgallery($ioparams);
	}
	
	
	
	protected function gallery(&$ioparams=array())
	{
		$pdb = $ioparams['vpath'];
		//selected
		foreach ($pdb as $key=>$v) {
			switch ($v) {
				case 'selected':
					return $this->fileselectorForSelected($ioparams);				
				//setgallery
				case 'setgallery':
					return $this->fileselectorForSetGallery($ioparams);			
				
				default:
					break;
			}
		}		
		return $this->selectfile($ioparams);
	}
	
	
	
	
	protected function fileselector(&$ioparams=array())
	{
		$pdb = $ioparams['vpath'];
		//selected
		foreach ($pdb as $key=>$v) {
			switch ($v) {
				case 'selected':
					return $this->fileselectorForSelected($ioparams);				
				//setgallery
				case 'setgallery':
					return $this->fileselectorForSetGallery($ioparams);			
				
				default:
					break;
			}
		}		
		return $this->selectfile($ioparams);
	}
	
	
	protected function init(&$ioparams=array())
	{
		parent::init($ioparams);	
		//$this->enableJSCSS(array('jquery_fileupload', 'tileupload', 'videojs'), true);
		$this->enableJSCSS(array( 'video'), true);	
	}
	
	protected function selectForFileView($modname, $params, &$ioparams=array())
	{
		$m = Factory::GetModel('file');
		$modinfo = $m->getModelInfo();
		
		$pid = isset($params['pid'])?$params['pid']:0;
		$positions = $m->getPostions($pid);
		
		//$this->setFilter($params);
		$rows = $m->selectForView($params, $ioparams);	
		$fdb = array();
		foreach ($modinfo['fdb'] as $key=>$v) {
			if (!$v['show'])
				continue;
			$fdb[$key] = $v;
		}
		
		$data = array(
				'fileview'=> array(
					'name'=>$this->_name,
					'fields'=>$fdb,
					'pkey'=>'id',
					'sbt' => "$ioparams[sbt]",
					'positions'=>$positions,
					'total'=>$params['total'],
					'page'=>$params['page'],
					'page_size'=>$params['page_size'],
					'num'=>$params['nr_row'],
					'sort'=>$params['order'],
					'order'=>$params['dir'],
					'rows'=>$params['rows']
					)
				);
				
		return $data;		
	}
	
	
	protected function fileview(&$ioparams=array())
	{
		$params = array();
		$this->getParams($params);
		
		//默认页面大小
		$cf = get_config();
		$default_page_size = $cf['page_size'];
		if ($default_page_size <= 0) 
			$default_page_size = 8;
		
		
		$page = $this->request('page', 1);
		$page_size = $this->request('page_size', $default_page_size);
		$sort = $this->request('sort', '');
		$dir = $this->request('order', '');
		
		$params['page'] = $page;
		$params['page_size'] = $page_size;
		if ($sort)
			$params['order'] = $sort;
		if ($dir)
			$params['dir'] = $dir;
		
		
		$data = $this->selectForFileView('file', $params, $ioparams);
						
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, $data);
		showStatus(0, $data);

	}	
	
	
	
	
	/*protected function delete(&$ioparams=array())
	{
		$id = $this->request('id');		
		$m = Factory::GetModel('file');	
		$res = $m->del($id);
		showStatus($res?0:-1);
		exit;
	}
	*/
			
	protected function preview(&$ioparams=array(), $fid=0, $large=0, $width=0, $height=0)
	{
		$large = $this->requestInt('l');		
		!$large && $large = $this->requestInt('large');
			
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "IN");
		$width = $this->requestInt('x', $large?1920:175);
		$height = $this->requestInt('x', $large?1080:135);
		
		//rlog('$this->_id='.$this->_id);
		//rlog($ioparams['vpath']);
		$fid = $this->probID($ioparams);
		if (!$fid) 	
			exit('error');
		
		$m = Factory::GetModel('file');	
		$m->preview($fid, $width, $height);
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "OUT");
		
		exit;
	}
	
	protected function spreview(&$ioparams=array())
	{
		$fid = $this->probID($ioparams);
		if (!$fid) 	
			exit('error');		
		$m = Factory::GetModel('file');	
		$m->preview($fid, 80, 72);
	}
	
	protected function lpreview(&$ioparams=array())
	{
		$fid = $this->probID($ioparams);
		if (!$fid) 	
			exit('error');		
		$m = Factory::GetModel('file');	
		$m->preview($fid, 1920, 1080);
	}
	
	protected function fileinfo(&$ioparams=array())
	{
		$fid = $this->_id;
		if (!$fid) 	
			exit('error');		
			
		$m = Factory::GetModel('file');	
		$fileinfo = $m->getFileInfo($fid, $ioparams);
		
		showStatus($fileinfo?0:-1, $fileinfo);
	}
	
		
	
	protected function downloadFile($fid, &$ioparams=array())
	{
		if (!$fid)
			exit('error');		
		$f = Factory::GetModel('file');	
		$f->download($fid);
		exit;
	}
	
	protected function download(&$ioparams=array(), $fid=0)
	{
		$fid = $this->probID($ioparams);		
		if (!$fid) 
			exit('error');
		
		$m = Factory::GetModel('file');
		$res = $m->download($fid);	
		
		exit;
	}
	
	
	protected function setdir(&$ioparams=array())
	{
		$this->getParams($params);		
		$m = Factory::GetModel('file');
		$res = $m->newDirectory($params, $ioparams);
		
		showStatus($res?0:-1);		
	}
	
	
	protected function newdir(&$ioparams=array())
	{
		$this->setdir($ioparams);
	}
	
	protected function newfile(&$ioparams=array())
	{
		$this->getParams($params);		
		$m = Factory::GetModel('file');
		$res = $m->newTxtFile($params, $ioparams);
		
		showStatus($res?0:-1);	
	}
	
	protected function upload(&$ioparams=array())
	{
		$ioparams['model'] = isset($_REQUEST['model'])?$_REQUEST['model']:'';
		$ioparams['mid'] = isset($_REQUEST['mid'])?$_REQUEST['mid']:0;
		$ioparams['pid'] = isset($_REQUEST['pid'])?$_REQUEST['pid']:0;
		
		$m = Factory::GetModel('file');
		$fdb = $m->upload($ioparams);
		if (!$fdb) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "upload failed!");
			showStatus(-1);
		}
		
		$baseurl = $ioparams['_base'];		
		foreach ($fdb as $key=>&$v) {
			//no opath
			$v['opath'] = '';	
			
			$m->formatForViewUrl($v, $ioparams);		
			/*
			$v['url'] = $baseurl.'?id='.$v['id'];
			$v['previewUrl'] = $baseurl.'/preview/'.$v['id'];
			$v['deleteUrl'] = $baseurl.'/delete/'.$v['id'];
			$v['downloadUrl'] = $baseurl.'/download/'.$v['id'];*/
		}
				
		CJson::encodedPrint(array('files' => $fdb));		
		exit;		
	}
	
	
	protected function bigupload(&$ioparams=array())
	{
		$ioparams['pid'] = isset($_REQUEST['pid'])?$_REQUEST['pid']:0;
		
		$m = Factory::GetModel('file');
		
		$res = $m->http($ioparams);
		
		showStatus($res?0:-1);
	}
	
	protected function doGetSubDir($pid, $params=array(), &$ioparams=array() )
	{
		$m = Factory::GetModel('file');
		$fdb = $m->getSubDir($pid, $params, $ioparams);
		
		return $fdb;
		
	}
	
	public function jstree(&$ioparams=array())
	{
		$pid = $_REQUEST["parent"];
		
		$params = array();
				
		$data = array();
		if ($pid == '#') {
			$data[] = array(
					'id'=>0,
					'text'=>'顶层根目录',
					'icon'=> "fa fa-folder icon-lg icon-state-warning",
					'children'=>true,
					'state'=>array("disabled"=>false, "opened"=>true, "selected"=>true)
					);
			
		} else {
			$pid = intval($pid);
			
			
			$fdb = $this->doGetSubDir($pid, $params, $ioparams);
			foreach ($fdb as $key => $v) {
				
				$v["text"] = $v['name'];
				$v["icon"] = "fa fa-folder icon-lg icon-state-warning";
				$v["children"] = $v['hasChildren'];
				//$v["type"] = "root";
				//if ($v['pid'] == 0)
				//	$v["state"] = array("disabled"=>false, "opened"=>true, "selected"=>true);
				
				$data[] = $v;
			}
		}
		
		header('Content-type: text/json');
		header('Content-type: application/json');
		echo json_encode($data);
		
		exit;
	}
	
	protected function setImg2text($ioparams = array())
	{
		$id = $this->_id;
		
		$m = Factory::GetModel('file2model');
		
		$params = $m->get($this->_id);
		
		$val = $params['description'];
				
		$this->enableJSCSS('ckeditor');
		
		$name = 'img2text';
		
		$simpleToolBar = "toolbar: [
				[ 'Paste', 'Copy', 'Cut'],
				],";
		
		$var_repconfig = "var repconfig = {
				$simpleToolBar
				toolbarCanCollapse:false,removePlugins:'elementspath',height:'320',  }; ";
		
		$id = "param_$name";
		$res =  "<textarea name='params[$name]' id='$id' class='ckeditor form-control' rows='6' >$val</textarea>";
		
		$res .= "<script> if (typeof(CKEDITOR) != 'undefined') { $var_repconfig CKEDITOR.replace('$id', repconfig);}</script>";
		
		$this->assign('img2text_content', $res);
		
		$this->setTpl('file_img2text');
	}
	
	protected function parseImg2text(&$ioparams=array())
	{
		$id = $this->_id;
		$content = $this->request('content');
		
		$m = Factory::GetModel('file2model');
		
		$params=array();
		$params['id'] = $id;
		$params['description'] = $content;
		
		$m->update($params);
		
		
		$m2 = $this->getModel();
		$res = $m2->parseImg2text($content);
		
		showStatus($res?0:-1, $res);
	}
	
	
	protected function pubcontent(&$ioparams=array())
	{
		$id = $this->request('id');
		$url = $ioparams['_basename'].'/my_content/add/?aids='.$id.'&r='.time();
		redirect($url);
	}
	
	
	protected function filecontent(&$ioparams=array())
	{
		$m = Factory::GetModel('file');
		$fdb = $m->filecontent($ioparams);
		if (!$fdb) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "filecontent failed!");
			showStatus(-1);
		}
		
		CJson::encodedPrint(array('files' => $fdb));		
		exit;		
	}
	
	protected function moveto(&$ioparams=array())
	{
		$params = array();
		$this->getParams($params);
		
		$m = Factory::GetModel('file');
		$res = $m->moveto($params, $ioparams);
		
		showStatus($res?0:-1);
		
	}
}