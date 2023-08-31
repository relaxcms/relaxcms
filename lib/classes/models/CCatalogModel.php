<?php
/**
 * @file
 *
 * @brief 
 * 目录
 *
 * Copyright (c), 2014, relaxcms.com
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );


define ('CTF_CHECKED',	0x1);
define ('CTF_RELEASE',	0x2);
define ('CTF_CATALOG',  0x4);
define ('CTF_NAV',		0x8);
define ('CTF_HOME',		0x10);


class CCatalogModel extends CTableModel
{
	var $_catalog = array();
	var $_select_cate = "";
	var $_position;
	
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);
		$this->_default_sort_field_mode = 'asc';
	}
		
	public function CCatalogModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}

	protected function _initFieldEx(&$f)
	{
		switch ($f['name']) {			
			
			case 'viewmode':
				$f['input_type'] = 'multicheckbox';
				$f['show'] = false;		
				break;
			case 'viewtype':
				$f['input_type'] = 'selector';
				$f['show'] = false;		
				break;
			case 'icon':
				$f['input_type'] = 'faselector';
				$f['show'] = false;		
				break;
			case 'description':
				$f['input_type'] = 'sneditor';
				$f['show'] = false;
				break;			
			case 'photo':
				$f['input_type'] = 'image';
				$f['show'] = false;
				break;
			case 'linkurl':
				$f['input_type'] = 'link';
				$f['show'] = false;
				break;
			case 'pid':
				$f['input_type'] = 'treemodel';
				$f['show'] = false;		
				$f['sort'] = 6;			
				break;
			case 'cuid':
				$f['readonly']= true;
			case 'uid':
				$f['input_type']="UID";
				$f['show'] = false;
				$f['edit'] = false;
				break;
			case 'ctime':
				$f['readonly']= true;
			case 'ts':
				$f['input_type'] = "TIMESTAMP";
				$f['show'] = false;
				$f['edit'] = false;
				break;
			case 'tpl_list_root':
			case 'tpl_list':
			case 'tpl_content_root':
			case 'tpl_content':
				$f['input_type'] = 'custom';
				$f['show'] = false;
				break;
			case 'depth':
			case 'cached':
				$f['edit'] = false;		
				
			case 'metakeyword':
			case 'metadescrip':
				$f['show'] = false;		
				break;
			case 'status':
				$f['input_type'] = 'ONOFF';
				$f['edit'] = false;		
				break;
			case 'flags':
				$f['input_type'] = 'varmulticheckbox';
				//$f['vid'] = 6;
				//$f['show'] = false;
				break;			

			default:
				break;
		}
		return true;
	}

	protected function buildInputForModel2($params, $field, $ioparams = array(), $has_default=false, $names=array())
	{
		return 	parent::buildInputForModel($params, $field, $ioparams, false, array('title'));
	}

	protected function get_tpl_select($params, $field, $def_site_tpl='default')
	{
		$m = Factory::GetModel('site_template');
		
		$name = $field['name'];
		$tpl = isset($params[$name])?$params[$name]:$def_site_tpl;
		
		$selector = "<select class='form-control form-filter filter-select ' name='params[$name]' id='param_$name' >";
		$selector .= $m->get_tpl_select($tpl);
		$selector .= "</select>";
		return $selector; 
	}

	protected function get_child_template_select($child, $root, $params, $field, $permit_select_index=false)
	{
		$m = Factory::GetModel('site_template');
		$name = $field['name'];

		$tpl = 'default';
		if (isset($params[$name]))
			$tpl = $params[$name];

		$selector = "<select class='form-control form-filter filter-select ' name='params[$name]' id='param_$name' >";
		$selector .= $m->get_child_template_select($child, $root, $tpl, $permit_select_index);
		$selector .= "</select>";
		return $selector; 
	}


	
	protected function buildInputCustom2($params, &$field, &$ioparams=array())
	{
		$scf = Factory::GetSiteConfiguration();
		$def_site_tpl = isset($scf['template'])?$scf['template']:'default';		

		switch ($field['name']) {
			case 'pid':
				return $this->buildInputForTree($params, $field);
			case 'tpl_list_root':
			case 'tpl_content_root':
				return  $this->get_tpl_select($params, $field, $def_site_tpl);
			case 'tpl_list':
				return $this->get_child_template_select('list', isset($params['tpl_list_root'])?$params['tpl_list_root']:$def_site_tpl, $params, $field, true);
			case 'tpl_content':
				return $this->get_child_template_select('content', isset($params['tpl_content_root'])?$params['tpl_content_root']:$def_site_tpl, $params, $field);
			default:
				return $this->buildInputTextArea($params, $field);
		}		
	}
	
	protected function get_catalog_var_table()
	{
		$m = Factory::GetModel('var');
		return $m->get_var_table(RVAR_CATALOG);
	}
	
	
	
	public function formatForView(&$row, &$ioparams = array())
	{
		parent::formatForView($row, $ioparams);
		
		$row['_taxis'] = "<input type='text' name='params[taxis][$row[id]]' value='$row[taxis]' class='form-control input-xsmall' />";
		
		$id = $row['id'];
		
		//status
		//$row['status'] = $this->formatLabelColorForView($row['_status'], $row['status']);
		
		
		//fixed photo
		$photo = $v['photo'];
		if (!$photo) {
			$v['photo'] = $ioparams['_dstroot'].'/img/nopic.jpg';
		}
		
		$photoUrl = $v['photo'];
		if (is_url($photoUrl)) {
			$v['photoUrl'] = $photoUrl;
		} else {
			$v['photoUrl'] = $ioparams['_rooturl'].s_hslash($photoUrl);
		}
		//for listview
		$row['previewUrl'] = $v['photoUrl'];
		
		//url 本页菜单
		$row['url'] = ($row['flags'] & CTF_NAV)?$ioparams['_webroot']."/list/$id":$ioparams['_basename'].'#param_catalog_'.$id;		
	}
	
	
	protected function formatOperate($row, &$ioparams=array())
	{
		$id = $row[$this->_pkey];
		
		$defOpt = parent::formatOperate($row, $ioparams);
		
		$item = array(
				'name'=>'add',
				'title'=>'添加子目录',
				'icon'=>'fa-plus-square',
				'url'=>$ioparams['_base'].'/add?id='.$id,
				);
		$defOpt[] = $item;
		
		$item = array(
				'name'=>'preview',
				'title'=>'预览',
				'icon'=>'fa-search',
				'url'=>$ioparams['_webroot'].'/list/'.$id,
				);
		$defOpt[] = $item;
		
		
		$item = array(
				'name'=>'add',
				'title'=>'添加内容文稿',
				'icon'=>'fa-plus-circle',
				'url'=>$ioparams['_basename'].'/cm_content/add?id='.$id,
				);
		$defOpt[] = $item;
		
		return $defOpt;
		
	}
	
	public function set(&$params, &$ioparams=array())
	{
		$res = parent::set($params, $ioparams);
		if ($res) {
			$this->cache($this->_name, 'order by taxis asc');
		}
		
		return $res;
	}
	
	/*protected function isBusy($id)
	{
		//是否有内容
		$m = Factory::GetModel('site_content');		
		$sql = "select * from cms_content where cid=$id";
		if ($this->_db->exists($sql))
			return true;
		
		//是否有目录		
		$sql = "select * from cms_catalog where pid=$id";
		if ($this->_db->exists($sql))
			return true;
						
		return false;
	}
		
	public function del($id)
	{
		if ($this->isBusy($id)) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "catalog id '$id' is busy!");
			return false;
		}						
		
		$res = parent::del($v['id']);
		return $res;
	}*/
	
	public function taxis($params)
	{
		$res = parent::taxis($params);
		if (!$res)
			return false;
			
		$this->cache($this->_name, 'order by taxis asc');

		return true;
	}
	
	public function cacheCatalog()
	{
		return $this->cache($this->_name, 'order by taxis asc');
	}
	
	protected function initCatalog()
	{
		$udb = $this->select();
		
		$catalogdb = array();
		foreach ($udb as $key=>$v) {
			$id = $v['id'];
			$catalogdb[$id] = $v;
		}
		
		$this->_catalog = $catalogdb;
	}
	
	
	public function getCatalog()
	{
		if (!$this->_catalog)
			$this->initCatalog();
		return $this->_catalog;
	}
	
	public function getCatalogById($id, &$ioparams=array())
	{
		$res =  isset($this->_catalog[$id])?$this->_catalog[$id]:array();
		
		$this->formatForView($res, $ioparams);
		
		return $res;
		
	}
	
	//树型目录栏
	protected function makeTreeCatalog($cid, $flags=0)
	{
		$menu = array();
		
		$catalogdb = $this->getCatalog();
		
		foreach ($catalogdb as $key=>$c)
		{
			if ($c['status'] != 1)  //发布
				continue;
			
			if ($c['pid'] != $cid) 
				continue;
				
			if ($flags > 0 && ($c['flags'] & $flags) != $flags)
				continue;
			
			$menu[$key] = $c;
		}
		
		foreach ($menu as $key=>&$v) {			
			$v['submenu'] = $this->makeTreeCatalog($v['id'], $flags);
		}
		
		return $menu;
		
	}
	
	
	//目录栏
	public function menu($cid=0, $tree=true, $flags=0)
	{
		if ($tree)
			return $this->makeTreeCatalog($cid, $flags);
		
		$catalogdb = $this->getCatalog();
		
		$menu = array();			
		foreach ($catalogdb as $key=>$v)
		{
			if ($v['status'] != 1)  //发布
				continue;	
			
			if ($flags > 0 && (intval($v['flags']) & $flags) != $flags) {
				continue;
			}
							
			$menu[$key] = $v;
		}
		return $menu;
	}
	
	
	
	//生成树型结构	
	public function tree()
	{
		$this->_select_cate = '';
		$this->_tree($this->_catalog);
		return $this->_select_cate;
	}
	
	public function _tree2($catedb, $cid='')
	{
		if ($catedb == null) return;
		
		foreach ($catedb as $key=>$cate)
		{
			if ($cate['linkurl'] != '') continue;
			
			if ($cid=='' && $cate['pid'] != 0)
			{
				continue;
			}
			elseif ($cid && $cate['pid'] != $cid)
			{
				continue;
			}
			
			$add = '';
			if ($cate['depth'] == 0 && $cate['pid'] == 0)
			{
				$add = "&raquo;";
			}
			else
			{
				
				$repeatnum = ($cate['depth']-1);
				$str = "&nbsp;&nbsp;";
				for($i=0; $i<$repeatnum; $i++)
				{
					$str .= "&nbsp;&nbsp;";
				}
				$add = $str.'|--';
				//$add .= str_repeat('&nbsp;&nbsp;', $repeatnum);		
				//$add .= str_repeat('--', $repeatnum);		
			}
			
			$disabled = '';
			
			$this->_select_cate .= "<option value='$cate[cid]' $disabled>$add$cate[title]</option>";
			if(count($catedb) == 0)
			{
				return ;
			}
			$this->_tree($catedb, $cate['cid']);
		}
	}

	public function digest($sb)
	{
		$mask = 0x1 << $sb;
		
		$i=0;
		$arr = array();
		foreach ($this->_catalog as $key=>$v)
		{
			if (($mask & $v['flags']) !== $mask) continue;
			
			$v['direct'] = $i%2 == 0?"left":"right";
			$v['i'] = $i++;
			
			$arr[$key] = $v;			
		}
		
		return $arr;
	}
	
	protected function formatForView2(&$row, &$fields, $ioparams=array())
	{
		//fixed photo
		$photo = $row['photo'];
		if (!$photo) {
			$row['photo'] = $ioparams['_dstroot'].'/img/nopic.jpg';
		}
		
		$photoUrl = $row['photo'];
		if (is_url($photoUrl)) {
			$row['photoUrl'] = $photoUrl;
		} else {
			$row['photoUrl'] = $ioparams['_rooturl'].s_hslash($photoUrl);
		}
		
		//url
		$row['url'] = $ioparams['_webroot'].'/list/'.$row['id'];
		
	}
	
	public function getDigest($flags, $limit=0, &$ioparams=array())
	{
		$i=0;
		$cdb = array();

		foreach ($this->_catalog as $key=>$v)
		{
			if (($flags & $v['flags']) !== $flags) 
				continue;
			
			$v['direct'] = $i%2 == 0?"left":"right";
			$v['i'] = $i++;

			//fixed for view
			$this->formatForView($v, $ioparams);

			$cdb[] = $v;			

			if ($limit > 0 && $i >= $limit)
				break;
		}
		
		return $cdb;
	}


	public function getSubCatalog($pid, $limit, &$ioparams=array())
	{
		$i=0;
		$arr = array();

		foreach ($this->_catalog as $key=>$v)
		{
			if ($v['pid'] != $pid) continue;
			
			$v['direct'] = $i%2 == 0?"left":"right";
			$v['i'] = $i++;

			//fixed photo
			$photo = $v['photo'];
			if (!$photo) {
				$v['photo'] = $ioparams['_dstroot'].'/img/nopic.jpg';
			}

			$photoUrl = $v['photo'];
			if (is_url($photoUrl)) {
				$v['photoUrl'] = $photoUrl;
			} else {
				$v['photoUrl'] = $ioparams['_rooturl'].s_hslash($photoUrl);
			}

			//url
			$v['url'] = $ioparams['_webroot'].'/list/'.$v['id'];

			$arr[] = $v;			

			if ($i >= $limit)
				break;
		}
		
		return $arr;
	}
	
		
	//提示导航
	public function postion($cid, $ioparams=array(), $type=1)
	{
		$position = "";
		if ($cid)
			$this->_find_parent($cid, $ioparams, $position, $type);
		
		return $position;
	}	
	
	
	public function nav($cid, $ioparams=array())
	{
		return $this->getPosition($cid, $ioparams, 0);
	}
	
	public function _find_parent($cid, $ioparams, &$position, $type=1)
	{
		$catalog = $this->getCatalog();
		
		$curl = $ioparams['_webroot'].'/list/'.$cid;
		if ($type == 1) {
			$position = " <li> <a href='".$curl."' > ".$catalog[$cid]['name']."</a>  </li>" . $position;
		} else {
			$position = "  > <a href='".$curl."' > ".$catalog[$cid]['name']."</a> " . $position;
		}
		
		$pid = $catalog[$cid]['pid'];
		if ($pid)
		{
			$this->_find_parent($pid, $ioparams, $position, $type);
		}
	}
	
	//所有子结点
	public function childs($cid, $exclude_not_search=true) 
	{
		$res = "";		
		if (!$cid)
			return false;
		
		foreach ($this->_catalog as $key=>$v) {
			if ($v['pid'] == $cid) {
				if ($exclude_not_search && !is_search($v['flags']))
					continue;
				$res = $v['cid'];	
				if ($cid = $this->childs($v['cid'], $exclude_not_search)) {
					$res .= ','.$cid;
				}			
			}
		}		
		return $res;
	}
	
	//所有当前及祖先结点
	public function parents($cid, &$parentdb=array()) 
	{
		$res = "";		
		if (!$cid)
			return true;
		$catalogdb = $this->getCatalog();
		
		$cataloginfo = $catalogdb[$cid];
		$parentdb[] = $cataloginfo;
		
		$cid = $cataloginfo['pid'];		
		return $this->parents($cid, $parentdb);
	}
	
	public function getPosition($cid, $ioparams) 
	{
		$this->parents($cid, $parentdb);
		
		if (!$parentdb)
			return '';
			
			
		$nr = count($parentdb);
		
		$pos = '';
		for($i=$nr-1; $i>=0; $i--) {
			$cataloginfo = $parentdb[$i];
			$pos .= "<i class='fa fa-angle-right'></i> <a href='#'>$cataloginfo[name]</a> ";
		}
		
		return $pos;
	}
	
	
	public function get_first_cid()
	{
		$catalogdb = $this->getCatalog();
		
		foreach ($catalogdb as $key=>$v) {
			return $v['id'];
		}
		return 0;
	}
	
	
	public function get_catalog()
	{
		$file = RPATH_CACHE.DS."catalog.php";
		if (file_exists($file)) {
			require $file;
			return $catalog;
		}				
		return array();
	}


	public function getSiteMenu()
	{
		$menus = $this->makeTreeCatalog(0);
		return $menus;
	}

	public function getMainmenu($flags=0, $limit=9)
	{
		$flags |= 3;
		
		$catalog = $this->getCatalog();
		
		$mmdb = array();
		$i=0; 
		foreach ($catalog as $key=>$v) {
			if ($v['status'] != 1)
				continue;
			
			if (($v['flags']&$flags) != $flags) //审，发，主菜单
				continue;

			$mmdb[$key] = $v;
			if (++$i>=$limit)
				break;
		}
		return $mmdb;
	}
	
	public function formatForTV($params, $ioparams)
	{
		$tinfo = array();
		$tinfo['id'] = $params['id'];
		$tinfo['name'] = $params['name'];
		$tinfo['description'] = $params['description'];
		$tinfo['tpl_list'] = $params['tpl_list'];
				
		$photoUrl = $params['photoUrl'];
		if (is_url($photoUrl)) {
			$tinfo['photoUrl'] = $photoUrl;
		} else {
			$tinfo['photoUrl'] = $ioparams['_rooturl'].s_hslash($photoUrl);
		}
		
		
		return $tinfo;
	}
	
	public function getMainMenuByOid($oid, $limit=9)
	{
		$mmdb = array();
		
		$params = array();
		
		$params['status'] = 1; //发布
		$params['flags'] = 11; //审，发，主菜单
		$params['orderby'] = 'order by taxis asc';
		
		$udb = $this->getListByOid($oid, $params, $limit, $ioparams);	
		foreach ($udb as $key=>$v) {
			$mmdb[] = $this->formatForTV($v, $ioparams);
		}
		
		return $mmdb;
	}
	public function getSubCatalogByOid($oid, $pid, $limit, &$ioparams=array())
	{
		$mmdb = array();
		
		$params = array();
		
		$params['status']=1; //发布
		$params['pid'] = $pid; //PID
		$params['orderby'] = 'order by taxis asc';
		
		$udb = $this->getListByOid($oid, $params, $limit, $ioparams);	
		foreach ($udb as $key=>$v) {
			$mmdb[] = $this->formatForTV($v, $ioparams);
		}
		
		return $mmdb;
	}
	

	public function mck($id, $flagsMask, $fieldname='')
	{
		$res = parent::mck($id, $flagsMask, $fieldname);		
		if ($res) {
			$this->cacheCatalog();
		}
		return $res;
	}


	public function genCatalog($params)
	{
		$_params = array();
		$_params['name'] = $params['name'];

		$res = $this->getOne($_params);
		if ($res)
			return $res['id'];
		
		$res = $this->set($params);

		return $params['id'];
	}
	
	
}
