<?php
/***
 * @file
 * 
 * 内容管理
 * 
 * */

defined( 'RMAGIC' ) or die( 'Restricted access' );

class CContentComponent extends CDTFileComponent
{
	function __construct($name, $options=null)
	{
		parent::__construct($name, $options);
	}
	
	function CContentComponent($name, $options=null)
	{
		$this->__construct($name, $options);
	}
	
	protected function show(&$ioparams=array())
	{
		$this->enableMenuItem('taxis');
		
		$res = parent::show($ioparams);
		
		return $res;
	}
	
	
	protected function setTabFdb($fdb=array(), $isdetail=false)
	{
		$tabs = $this->initActiveTab(3);
		
		$fdb1 = array();
		$fdb2 = array();
		
		foreach ($fdb as $key=>$v) {
			if ($isdetail && !$v['detail']) 
				continue;  
			if  (!$isdetail && !$v['edit']) 
				continue;  			
				
			switch ($key) {
				case 'name':
					$v['sort'] = 1;
					$fdb1[] = $v;
					break;
				case 'cid':
					$v['sort'] = 3;
					$fdb1[] = $v;
					break;
				case 'aids':
					$v['sort'] = 6;
					$fdb1[] = $v;
					break;
				case 'content':
					$v['sort'] = 5;
					$fdb1[] = $v;
					break;
				case 'flags':				 
					$v['sort'] = 2;
					$fdb1[] = $v;
					break;
				case 'status':
					$v['sort'] = 4;
					$fdb1[] = $v;
					break;
				/*case 'photo':
					$v['tabid'] = 1;
					break;
				case 'taxis':
					$v['tabid'] = 2;
					break;*/
				default:
					$fdb2[] = $v;
					break;
			}
		}
		$res = array_sort_by_field($fdb1, "sort", false);	
		
				
		$this->assign('fdb1', $fdb1);
		$this->assign('fdb2', $fdb2);
		
		
		
		
	}
	
	protected function probCID()
	{
		$cid = $this->_id;					
		!$cid && $cid = isset($_SESSION['__cookie_last_cid'])?intval($_SESSION['__cookie_last_cid']):0;
		if (!$cid) {
			$m = Factory::GetModel('catalog');
			$cid = $m->get_first_cid();
		}
		return $cid;		
	}
	
	
	protected function getPlayUrl($fileinfo)
	{
		$m = Factory::GetModel('storage');
		$storageinfo = $m->get($fileinfo['sid']);
		$playurl = $storageinfo['webpath'].'/'.$fileinfo['path'];
		
		$convert_id = $fileinfo['convert_id'];
		$m2 = Factory::GetModel('file');
		$convertfileinfo = $m2->get($convert_id);
		if ($convertfileinfo) {
			$playurl = $storageinfo['webpath'].'/'.$convertfileinfo['path'];
		} 
		
		return $playurl;		
	}
	
	
	protected function probIDS(&$params, &$ioparams=array())
	{
		$cid = $this->probCID();
		$params['cid'] =$cid;
		
		
		$_fids = $this->request('aids');
		//if (!$_fids)
		//	return $this->proLiveID($params, $ioparams);
		
		$fids = explode(',', $_fids);
		
		$m = Factory::GetModel('file');
		$fdb = array();		
		$aids = array();
		
		foreach ($fids as $key=>$fid) {
			$fileinfo = $m->get($fid);
			if ($fileinfo) {
				if ($fileinfo['status'] != 1) {
					continue;
				}
				
				if ($fileinfo['type'] == 4) { //图片
					$photourl = $ioparams['_webroot'].'/f/'.$fileinfo['id'].'/'.$fileinfo['name'];
				} else if ($fileinfo['type'] == 1) {
						$videourl = $this->getPlayUrl($fileinfo);
						if ($fileinfo['snap_id'] > 0) {//截图
							$fileinfo2 = $m->get($fileinfo['snap_id']);
							if ($fileinfo2)
								$photourl = $ioparams['_webroot'].'/f/'.$fileinfo2['id'].'/'.$fileinfo2['name'];
						}
						$aids[] = $fid;
					} elseif ($fileinfo['type'] == 2) { //视，音
						$aids[] = $fid;
					} else {
						$aids[] = $fid;
					}
				
				if (!$photourl && $fileinfo['isdir']) {
					$photourl = $ioparams['_theroot']."/global/img/folder.png";					
				}
			}
		}
		
		
		
		$params['name'] = $fileinfo['name'];
		$params['photo'] = $photourl;
		$params['video'] = $videourl;
		$params['aids'] = implode(',', $aids);
		
		$params['from_model_name'] = 'file';
		$params['from_model_id'] = $_fids;
		
	}
	
	
	
	
	protected function initParams(&$params, &$ioparams=array())
	{
		$res = parent::initParams($params, $ioparams);
		
		$emid = $this->request('emid');
		if ($emid) {
			$modinfo = $this->deModelInfo($emid);
			if ($modinfo) {
				$params['modname'] = $modinfo['modname'];
				$params['mid'] = $modinfo['mid'];
				if (isset($modinfo['cid'])) {
					$params['id'] = $modinfo['cid'];
				}
				//fdb3 模型
				$m = Factory::GetModel('content');
				$fdb3 = $m->getModelFieldsForInput($params, $ioparams);
				$this->assign('fdb3', $fdb3);
			}			
		}
				
		//$mid = isset($_SESSION['__cookie_last_mid'])?intval($_SESSION['__cookie_last_mid']):0;
		//$params['mid'] = $mid;
				
		//$this->assign('mid', $mid);		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $params);
		return $res;
		
	}
	protected function initParamsForAdd(&$params, &$ioparams=array())
	{
		parent::initParamsForAdd($params, $ioparams);
		$this->probIDS($params, $ioparams);		
	}
	
	protected function initParamsForEdit(&$params, &$ioparams=array())
	{
		parent::initParamsForEdit($params, $ioparams);
		
		//fdb3 模型
		if (isset($params['modname'])) {
			$m = Factory::GetModel('content');
			$fdb3 = $m->getModelFieldsForInput($params, $ioparams);
			$this->assign('fdb3', $fdb3);			
		}		
	}
	
	protected function postSubmitParams(&$params, &$ioparams=array())
	{
		$_SESSION['__cookie_last_cid'] = $params['cid'];
		$_SESSION['__cookie_last_mid'] = $params['mid'];
	}
	
		
	protected function add(&$ioparams=array())
	{
		$fdb = parent::add($ioparams);

		//tabs
		$this->setTabFdb($fdb);
		$this->setTpl('site_content_edit');
		return true;
	}
	
	
	
	protected function edit(&$ioparams=array())
	{
		$fdb = parent::edit($ioparams);
		//tabs
		$this->setTabFdb($fdb);
		$this->setTpl('site_content_edit');
	}
	
	protected function detail(&$ioparams=array())
	{
		$id = $this->get_id();
		
		$m = $this->getModel();
		$ioparams['detail'] = true;//
		$params = $m->getForView($id, $ioparams);
		
		
		$tablename = $this->_modname;
		$table_id = 'mod_table_'.$tablename;
		
		$fields = $m->getFieldsForDetail($params, $ioparams);
		
		$mi18n  = get_i18n('mod_'.$tablename);
		
		//columns
		$this->setColumns($fields);
		$this->setTpl('site_content_detail');		
		$this->initTools('detail');
		$this->setTabFdb($fields, true);		
		
		//fdb3
		$fdb3 = $m->getModelFieldsForDetail($params, $ioparams);
		$this->assign('fdb3', $fdb3);
		
		$this->assign('mi18n', $mi18n);
		$this->assign('edit', false);
		$this->assign('table_id', $table_id);
		$this->assign('params', $params);
		
		return $params;
	}
	
	//内容发布
	protected function pub(&$ioparams=array())
	{
		$emid = $this->request('emid');
		if (!$emid) 
			return false;
		$params = $this->deModelInfo($emid);
		$cid = isset($params['cid'])?$params['cid']:0;
		$this->_id = $cid;
		if ($cid > 0) {
			$this->edit($ioparams);
		} else {
			$this->add($ioparams);
		}
	}	
	
	
	//发布
	protected function release(&$ioparams=array())
	{
		$tid = get_var("id","");		
		if (is_array($tid))
			$tid = implode(",", $tid);
		
		//发布状态更新
		$m = Factory::GetModel('content');
		$res = $m->release($tid);
				
		showStatus($res?0:-1);
	}
	
	//取消发布
	protected function unrelease(&$ioparams=array())
	{
		$tid = get_var("id","");		
		if (is_array($tid))
			$tid = implode(",", $tid);
		
		//发布状态更新
		$m = Factory::GetModel('content');
		$res = $m->unrelease($tid);
				
		showStatus($res?0:-1);
	}
		
	//切换内容模型
	protected function switchmodel(&$ioparams=array())
	{
		$id = $this->_id;
		$mid = $this->requestInt('mid');
		$m = Factory::GetModel('content');
		
		$fields = $m->getModelFieldsForInput($id, $mid, $params, $ioparams);
		if (!$fields) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "WARNING: getModelFieldsForInput failed!");
			$fields = array();
		}
		$this->setColumns($fields, false);
		
		$this->setTpl('site_content_edit_model');
	}	


	protected function move(&$ioparams=array())
	{
		//btSelectItem
		//bootrstrap params
		$params = array();
		$this->getParams($params);

		$btSelectItem = $params['btSelectItem'];
		if (!$btSelectItem) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no btSelectItem!");
			showStatus(-1);
		}
		if (!is_array($btSelectItem)) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "Invalid btSelectItem!", $btSelectItem);
			showStatus(-1);
		}

		
		$id = intval($params['cid']);
		if (!$id) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no id!");
			showStatus(-1);
		}

		$m = Factory::GetModel('content');
		$res = $m->moveTo($btSelectItem, $id);

		showStatus($res?0:-1);
	}
	
	protected function doEdit($modname, &$ioparams=array())
	{
		parent::doEdit($modname, $ioparams);
		$this->setTpl('dt_edit');
	}
	
	
}