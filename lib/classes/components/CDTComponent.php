<?php

class CDTComponent extends CUIComponent
{
	protected $_default_vmask = 5;
	protected $_default_viewtype = 4;
	
	/* tools menu item*/
	protected $_tmi_tools = array(		
		'add'=>array(
			'name'=>'add',
			'icon'=>'fa fa-plus',
			'title'=>'新建',
			'action'=>'link',
			'sort'=>1,
			'class'=>'green',
			'enable'=>true,
			'tmask'=>array('show', 'edit'),
			),
		'edit'=>array(
			'name'=>'edit',
			'icon'=>'fa fa-pencil',
			'title'=>'编辑',
			'sort'=>3,
			'enable'=>true,
			'action'=>'linkbutton',
			'tmask'=>array('show', 'detail'),
			),
		'del'=>array(
			'name'=>'del',
			'icon'=>'fa fa-trash-o',
			'title'=>'删除',
			'action'=>'submit',
			'sort'=>10,
			'enable'=>true,
			'class'=>'btn-danger needconfirm',
			'msg'=>'确认删除吗？',
			'tmask'=>array('show', 'detail'),
			),
		'show'=>array(
			'name'=>'show',
			'icon'=>'fa fa-list',
			'title'=>'列表',
			'action'=>'link',
			'sort'=>7,
			'enable'=>true,
			'tmask'=>array('show','listview', 'edit'),
			),
		'pub'=>array(
			'name'=>'pub',
			'icon'=>'fa fa-share',
			'title'=>'发布',
			'action'=>'tmbox',
			'class'=>'blue',
			'sort'=>19,
			'enable'=>false,
			'tmask'=>array('show'),
			),
		'treeview'=>array(
			'name'=>'treeview',
			'icon'=>'fa fa-list',
			'title'=>'树视图',
			'sort'=>9,
			'action'=>'link',
			'enable'=>false,
			'tmask'=>array('show','treeview', 'edit'),
			),
		'listview'=>array(
			'name'=>'listview',
			'icon'=>'fa fa-list',
			'title'=>'普通视图',
			'sort'=>9,
			'action'=>'link',
			'enable'=>false,
			'tmask'=>array('show','listview', 'edit'),
			),
		
		'exportpdf'=>array(
			'name'=>'exportpdf',
			'icon'=>'fa fa-file-pdf-o',
			'title'=>'Export PDF',
			'action'=>'linkbutton',
			'sort'=>9,
			'enable'=>false,
			'tmask'=>array('show'),
			),
		'import'=>array(
			'name'=>'import',
			'icon'=>'fa fa-upload',
			'title'=>'导入',
			'action'=>'file',
			'sort'=>11,
			'enable'=>false,
			'tmask'=>array('show'),
			),
		
		'upload'=>array(
			'name'=>'upload',
			'icon'=>'fa fa-upload',
			'title'=>'上传',
			'action'=>'file',
			'sort'=>13,
			'enable'=>false,
			'tmask'=>array('show'),
			),
		
		'reset'=>array(
			'name'=>'reset',
			'icon'=>'fa fa-retweet',
			'title'=>'重置',
			'action'=>'button',
			'sort'=>15,
			'enable'=>false,
			'tmask'=>array('show', 'edit'),
			),
		
		'cache'=>array(
			'name'=>'cache',
			'icon'=>'fa fa-a',
			'title'=>'缓存',
			'action'=>'button',
			'sort'=>17,
			'enable'=>false,
			'tmask'=>array('show'),
			),
		
		'release'=>array(
			'name'=>'release',
			'icon'=>'fa fa-share',
			'title'=>'发布',
			'action'=>'submit',
			'class'=>'green needconfirm',
			'msg'=>'确认发布吗？',
			'sort'=>19,
			'enable'=>false,
			'tmask'=>array('show'),
			),
		'unrelease'=>array(
			'name'=>'unrelease',
			'icon'=>'fa fa-minus-circle',
			'title'=>'撤回',
			'action'=>'submit',
			'class'=>'btn-danger needconfirm',
			'msg'=>'确认撤回吗？',
			'sort'=>21,
			'enable'=>false,
			'tmask'=>array('show'),
			),
		'taxis'=>array(
			'name'=>'taxis',
			'icon'=>'fa fa-sort',
			'title'=>'排序',
			'action'=>'button',
			'class'=>'blue',
			'tmask'=>array('show'),
			'sort'=>23,
			'enable'=>false,
			),
		'move'=>array(
			'name'=>'move',
			'icon'=>'fa fa-move',
			'title'=>'移动',
			'action'=>'button',
			'class'=>'btn-danger needconfirm',
			'msg'=>'确定移动吗？',
			'tmask'=>array('show'),
			'sort'=>24,
			'enable'=>false,
			),
		'pubrelease'=>array(
			'name'=>'pubrelease',
			'icon'=>'fa fa-share',
			'title'=>'发布',
			'action'=>'linkbutton',
			'class'=>'blue',
			'msg'=>'确认发布吗？',
			'sort'=>19,
			'enable'=>false,
			'tmask'=>array('show'),
			),
		);
	
	
	function __construct($name, $options)
	{
		parent::__construct($name, $options);
	}
	
	function CDTComponent($name, $options)
	{
		$this->__construct($name, $options);
	}	
	
	protected function init(&$ioparams=array())
	{
		parent::init($ioparams);
		$ioparams['dlg'] = $this->requestInt('dlg');
	}
	
	protected function enableMenuItem($midb, $enable=true)
	{
		if (!is_array($midb)) {
			$midb = explode(',', $midb);
		}		
		foreach ($midb as $key=>$v) {
			if (isset($this->_tmi_tools[$v])) {
				$this->_tmi_tools[$v]['enable'] = $enable;
				$this->_tmi_tools[$v]['tmask'][] = $this->_task;
			}
		}
	}
	
	
	protected function disableMenuItemAll()
	{
		foreach ($this->_tmi_tools as $key=>&$v) {
			$v['enable'] = false;
		}
		
		
	}
	
	protected function setMenuItem($name, $key, $val)
	{
		$this->_tmi_tools[$name]['enable'] = true;
		$this->_tmi_tools[$name][$key] = $val;
	}
	protected function addMenuItem($item)
	{
		$item['enable'] = true;		
		$this->_tmi_tools[$item['name']] = $item;
	}
	
	protected function getTools($activetminame, $tools = array())
	{
		if (!$tools) 
			$tools = array('add','show','edit', 'del');
		
		$tmidb = $this->_tmi_tools;
		
		$_tools = array();
		foreach ($tmidb as $key => $v) {
			if (!$v['enable'])
				continue;
			
			//check privilege
			if (!hasPrivilegeOf($this->_name, $v['name']))
				continue;
			
			$item = $v;
			
			//title				
			$item['title'] = i18n($item['title']);
			
			if (!isset($item['class'])) {
				$item['class'] = 'btn-primary';
			}
			if (!isset($item['msg'])) {
				$item['msg'] = '';
			}
			if (!isset($item['sort'])) {
				$item['sort'] = 255;
			}
			
			
			if ($activetminame) {
				if (is_array($item['tmask']) && in_array($activetminame, $item['tmask']))
					$_tools[$key] = $item;
			} else {
				$_tools[$key] = $item;
			}
		}
		
		array_sort_by_field($_tools, "sort", false);
		
		return $_tools;
	}
	
	
	protected function initTools($activetminame, $tools = array())
	{
		$_tools = $this->getTools($activetminame, $tools);
		
		$this->assign('toolmenuitems', $_tools);
		if (isset($_tools[$activetminame]))
			$this->assign('activetmi', $_tools[$activetminame]);		
	}
	
	protected $_treeview = false;
	protected $_cardview = false;
	protected function enableCardView($enable=true)
	{
		$this->_cardview = $enable;
	}
	
	protected function initParamsForShow(&$params, &$ioparams=array())
	{
		return true;
	}
	

	protected function initRequestParams()
	{
		$this->assign('requestparams', $_REQUEST);
	}	
	
	protected function show(&$ioparams=array())
	{
		parent::show($ioparams);
		
		$m = $this->getModel();
		$modinfo = $m->getModelInfo();
				
		$this->getParams($params);
		$fdb = $m->getFieldsForTable($params, $ioparams);
		$sfdb = $m->getFieldsForSearch($params, $ioparams);
		
		//变量
		$modname = $modinfo['name'];
		$table_id = 'mod_table_'.$modname;
		
		$treeview = $this->requestBool('treeview', $this->_treeview);
		$vmask = $this->requestInt('vmask', $this->_default_vmask);
		$viewtype = $this->requestInt('defaultviewtype', $this->_default_viewtype);
		
		//$showToggle = $this->requestBool('showToggle', $cardview);
		$pageSize = $this->requestInt('pageSize', $cardview?12:10);
		//$this->assign('pageList', $cardview?"[12, 24, 60, 120]":"[10, 20, 50, 100, 500,1000]");
		//$this->assign('pageList', "[10, 20, 50, 100]");
		
		//$this->assign('pageList', "[10, 20, 50, 100]");
		$this->assign('pkey', $modinfo['pkey']);		
		$this->assign('fdb', $fdb);		
		$this->assign('sfdb', $sfdb);	
		$this->assign('table_id', $table_id);	
		$this->assign('_modname', $this->_modname);		
		
		//sort 
		$default_sort_field = isset($ioparams['sort'])?$ioparams['sort']:$modinfo['pkey'];
		$this->assign('default_sort_field', $default_sort_field);
		$this->assign('disabledeleteall', isset($ioparams['disabledeleteall'])?"true":"false");
		$default_sort_field_order = isset($ioparams['sort_order'])?$ioparams['sort_order']:$modinfo['default_sort_field_mode'];
		$this->assign('default_sort_field_order', $default_sort_field_order);
		
		$this->initTools('show');
		$this->initRequestParams();
		
			
		//模板
		$this->setTpl('dt_show');
		return true;
	}
	
	protected function getCol()
	{
		return 2;
	}
	
	protected function setColumns($fields, $isdetail=true)
	{
		//columns
		$columns = $this->getCol();
		$column_width = 12/$columns; 
		$fdb = array();
		
		$i = 0; 
		foreach($fields as $key=>$v) { 
			if ($isdetail && !$v['detail']) 
				continue;  
			if (!$isdetail && !$v['edit']) 
				continue;  			
			$fdb[$i++] = $v; 
		}
		
		$this->assign('column_width', $column_width);
		$this->assign('fdb', $fdb);
		$this->assign('nr_field', $i);
		$this->assign('columns', $columns);
		
		return $fdb;
	}
	
	
	/**
	 * 详细
	 *
	 * @param mixed $ioparams This is a description
	 * @return mixed This is the return value description
	 *
	 */
	protected function detail(&$ioparams=array())
	{
		$id = $this->get_id();
		
		$m = $this->getModel();
		$ioparams['detail'] = true;
		$params = $m->getForView($id, $ioparams);
		
				
		$tablename = $this->_modname;
		$table_id = 'mod_table_'.$tablename;
		
		$fields = $m->getFieldsForDetail($params, $ioparams);
		
		$mi18n  = get_i18n('mod_'.$tablename);
		
		//columns
		$this->setColumns($fields);
		
		$this->setTpl('dt_detail');
		
		$this->initTools('detail');
		
		
		$this->assign('fields', $fields);		
		$this->assign('mi18n', $mi18n);
		$this->assign('edit', false);
		$this->assign('table_id', $table_id);
		$this->assign('params', $params);
		
		return $params;
	}
	
	protected function initParams(&$params, &$ioparams=array())
	{
		return true;		
	}
	
	
	protected function initParamsForAdd(&$params, &$ioparams=array())
	{
		return true;
	}
	
	
	protected function initParamsForEdit(&$params, &$ioparams=array())
	{
		$m = $this->getModel();
		$id = $this->get_id();		
		$_params = $m->get($id);
		
		$params = is_array($params)?array_merge($params, $_params):$_params;
		
		return $params;
	}
	
	
	
	protected function prepSubmitParams(&$params, &$ioparams=array())
	{
		return true;		
	}
	
	
	protected function postSubmitParams(&$params, &$ioparams=array())
	{
		return true;
	}
	
	
	protected function add(&$ioparams=array())
	{
		$m = $this->getModel();		
		$modinfo = $m->getModelInfo();		
		if ($this->_sbt) {
			
			$this->getParams($params);
			
			$this->prepSubmitParams($params, $ioparams);
			
			$res = $m->set($params, $ioparams);
			
			$data = array();
			if (isset($ioparams['data']))
				$data = $ioparams['data'];
			if ($res)				
				$this->postSubmitParams($params, $ioparams);
			
			showStatus($res, $data);
			
			return $res;
		}
		
		$modname = $modinfo['name'];		
		$table_id = 'mod_table_'.$modname;
		$this->assign('table_id', $table_id);
		
		$this->initParams($params, $ioparams);
		$tname = $this->_task;
		$this->initParamsForAdd($params, $ioparams);
		$fields = $m->getFieldsForInputAdd($params, $ioparams);
		if (!$fields) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "WARNING: getFieldsForInput$tname failed!", $tname, $modinfo);
			$fields = array();
		}
		
		$this->assign('fields', $fields);		
		
		//$this->initTools('edit');
		$mi18n  = get_i18n('mod_'.$tablename);
		$this->assign('mi18n', $mi18n);
		$this->assign('params', $params);
		
		$this->initTools($tname);
		$this->setColumns($fields, false);
		$this->setTpl('dt_add');
		
		return $fields;		
	}
	
	
	protected function edit(&$ioparams=array())
	{
		$m = $this->getModel();
		
		$modinfo = $m->getModelInfo();
				
		if ($this->_sbt) {
			$this->getParams($params);
			
			$this->prepSubmitParams($params, $ioparams);
							
			$res = $m->set($params, $ioparams);
			
			$data = array();
			if (isset($ioparams['data']))
				$data = $ioparams['data'];
			if ($res)				
				$this->postSubmitParams($params, $ioparams);
			
			showStatus($res, $data);
			
			return $res;
		}
		
		$modname = $modinfo['name'];		
		$table_id = 'mod_table_'.$modname;
		$this->assign('table_id', $table_id);
		
		$this->initParams($params, $ioparams);
		$this->initParamsForEdit($params, $ioparams);
		$fields = $m->getFieldsForInputEdit($params, $ioparams);
		if (!$fields) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "WARNING: getFieldsForInput$tname failed!", $tname, $modinfo);
			$fields = array();
		}
				
		$this->assign('fields', $fields);		
		$this->assign('params', $params);
		
		//$this->initTools('edit');
		$mi18n  = get_i18n('mod_'.$tablename);
		$this->assign('mi18n', $mi18n);
		
		$this->initTools($tname);
		$this->setColumns($fields, false);
		$this->setTpl('dt_edit');
		
		return $fields;
	}
	
	
	
	protected function doEdit($modname, &$ioparams=array())
	{
		$this->_modname = $modname;
		$this->assign('modname', $modname);
		$this->edit($ioparams);		
	}
	
	protected function del(&$ioparams=array())
	{
		$ids = $this->request('id');		
		if (!$ids)
			showStatus(-1);
		
		if (!is_array($ids)) 
			$ids = explode(',', $ids);
		
		$m = $this->GetModel();
		foreach ($ids as $key => $id) {
			$id = intval($id);
			$res = $m->del($id);
			if (!$res) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "del failed! id=$id!");
				break;
			}
		}
		
		showStatus($res?0:-1);		
		return $res;
	}
	
	protected function delete(&$ioparams=array())
	{
		return $this->del($ioparams);
	}
	
	protected function mck(&$ioparams=array())
	{
		$id = $this->_id;
		$fieldname = $this->request('fieldname');
		$key = get_int('key');				
		$mask = 0x1 << $key;
		
		$m = $this->getModel();		
		$res = $m->mck($id, $mask, $fieldname);
		
		showStatus($res?0:-1);
	}
		
	protected function onoff(&$ioparams=array())
	{
		$id = $this->_id;
		$modname = $this->request('modname');
		$field = $this->request('field');
		
		$m = Factory::GetModel($modname);
		$res = $m->onoff($id, $field);
			
		showStatus($res?0:-1);
	}
	
	
	/**
	 * autocomplete 自动补全请求处理
	 *
	 * @param mixed $ioparams This is a description
	 * @return mixed This is the return value description
	 *
	 */
	protected function autocomplete(&$ioparams=array())
	{
		$query = $_REQUEST["q"];
		
		$modname = $ioparams['vpath'][0];
		$fieldname = $ioparams['vpath'][1];
		
				
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, 'modname='.$modname.', fieldname='.$fieldname);
		
		$m = $this->getModel();
		$udb = $m->select(array('__keyword'=>$query));
		
		$results = array();		
		foreach($udb as $key=>$v) {
			$desc = $v['name'].'|'.$v['ano'];
			$results[] = array(
				"value" => $v['name'],
					"desc" => $desc,
				//"img" => "http://lorempixel.com/50/50/?" . (rand(1, 10000) . rand(1, 10000)),
				"tokens" => array($query, $query . rand(1, 10))
			);
		}
		echo json_encode($results);
		exit;

	}
	
	protected function listview(&$ioparams=array())
	{
		$cf = get_config();
		$default_page_size = $cf['page_size'];
		if ($default_page_size <= 0)
			$default_page_size = 10;
		
		$page = isset($_REQUEST['page'])?intval($_REQUEST['page']):1;
		$page_size = isset($_REQUEST['page_size'])?intval($_REQUEST['page_size']):$default_page_size;
		$order = $this->request('order');
		$dir = $this->request('dir');
		$treeview = isset($_REQUEST['treeview'])?intval($_REQUEST['treeview']):0;
		$pid = isset($_REQUEST['pid'])?intval($_REQUEST['pid']):($treeview == 1?0:-1);
		
		$params = $this->getParams(); //isset($_REQUEST['params'])?$_REQUEST['params']:array();	
		if (!empty($params['__keyword'])) {
			$treeview = 0;
			$pid = -1;
		}
				
		$params['page'] = $page;
		$params['page_size'] = $page_size;
		//$params['dir'] = $dir;
		if ($order)
			$params['__orderby'] = array($order=>$dir);
			
		$params['treeview'] = $treeview;
		if ($pid >= 0)
			$params['pid'] = $pid;
		$this->initParamsForShow($params, $ioparams);
		
				
		$m = $this->getModel();
		$res = $m->selectForListview($params, $ioparams);
			
		showStatus($res?0:-1, $res);
	}
	
	protected function vplay(&$ioparams=array())
	{
		$this->_template = 'dt_vplay';		
		
		$id = $this->probID($ioparams);
		if (!$id) {
			show_error('str_params_error');
			return false;
		}
		
		$fields = array();
		
		$m = Factory::GetModel($this->_modname);		
		$params = $m->getForView($id, $fields, $ioparams);		
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
	
	
	//更新排序与列表页
	protected function taxis(&$ioparams=array())
	{
		$params = get_var('params');
		$m = Factory::GetModel($this->_modname);
		$res = $m->taxis($params);		
		showStatus($res?0:-1);		
	}
	
	protected function map(&$ioparams=array())
	{
		$this->setTpl('map');
	}
	
	
	protected function enModelInfo($params)
	{
		return base64_encode(serialize($params));
	}
	
	protected function deModelInfo($modinfo)
	{
		return unserialize(base64_decode($modinfo));
	}
	
	
	//发布
	protected function pub(&$ioparams=array())
	{
		$cid = $this->requestInt('cid');
		$id = $this->get_id();
		$m = Factory::GetModel('content2model');
		$params = array('modname'=>$this->_modname, 'mid'=>$id);
		$udb = $m->select($params);
		$nr = count($udb);
		
		if ($this->_sbt || $cid > 0) {
			if ($cid > 0) {
				$params['cid'] = $cid;
			} else {	
				$this->getParams($_params);
				if ($_params)
					$params['cid'] = $_params['cid'];
			}
			//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $params);		
					
			$emid = $this->enModelInfo($params);			
			$url = $ioparams['_basename'].'/site_content/pub?emid='.$emid.'&dlg=1';			
			redirect($url);
			exit;
		}
		
		//一个模型内容对应多个发布，需要选择一个
		$ioparams['dlg'] = 1;
		//'content2model'
		$this->setTpl('content2model');
		$m = Factory::GetModel('content2model');
		$rows = $m->selectForView($params, $ioparams);
		
		$fdb = $params['fdb'];
		
		//模型
		$m2 = Factory::GetModel($params['modname']);
		$info = $m2->get($params['mid']);
		
		$fdbedit = $m->getFieldsForInputAdd($params, $ioparams);
		
		$fdb2 = array();
		foreach ($fdbedit as $key=>$v) {
			if (!$v['edit'])
				continue;
			$fdb2[$key] = $v;
		}
		$this->assign('fdb', $params['fdb']);
		$this->assign('rows', $rows);
		$this->assign('fdb2', $fdb2);
	}
	
	protected function delpub(&$ioparams=array())
	{
		//删除
		$m = Factory::GetModel('content2model');
		$res = $res = $m->del($this->_id);
		
		showStatus($res?0:-1);
	}
	
}