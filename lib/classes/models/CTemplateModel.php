<?php
/**
 * @file
 *
 * @brief 
 * 
 * template model
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class CTemplateModel extends CTableModel
{
	protected $_templates = array();
	
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);
	}
		
	public function CTemplateModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}
	
	protected function _init_field(&$f)
	{
		switch ($f['name']) {
			case 'isdir':
				$f['input_type'] = "yesno";
				break;
			case 'status':
				$f['input_type'] = "onoff";
				break;
			case 'pid':
				$f['input_type'] = "treemodel";
				break;
			default:
				break;
		}
		return true;
	}
	
	private function get_all_templates()
	{
		$templates = array();
		
		$dir = RPATH_TEMPLATES;
		$udb = s_readdir($dir);
		
		$hdb = array('.svn');	
		$id = 1;	
		foreach ($udb as $key=>$v) {
			
			if (in_array($v, $hdb))
				continue;
			
			$cfile = $dir.DS.$v.DS.'config.php';
			if (file_exists($cfile)) {
				require $cfile;				
				$appcfg['id'] = $id++;
				$templates[$appcfg['name']] = $appcfg;
			}
		}
		
		return $templates;
	}
	
	
	protected function getcfg($name)
	{
		$tcfginfo = array();		
		$cfile = RPATH_TEMPLATES.DS.$name.DS.'config.php';
		if (file_exists($cfile)) {
			require $cfile;				
			$tcfginfo = $appcfg;
		}
		return $tcfginfo;
	}
			
	public function gets($params=array(), &$ioparams=array())
	{
		$udb = $this->get_all_templates();
		
		$tpldb = $this->get_tpls();
		
		foreach ($tpldb as $key=>$v) {
			if (isset($v['enable']) && $v['enable'] == true) {
				$udb[$key]['checked'] = "checked";
			} else {
				$udb[$key]['checked'] = "";
			}
		}
		
		return $udb;
	}
	
	protected function initDigestForTpl($digests)
	{
		$m = Factory::GetModel('var');
		foreach ($digests as $key=>$v) {
			$params = array(
				'id'=>$key,
				'title'=>$v
				);
			$m->set($params);
		}
	}

	protected function fixedForLocal($tplname, &$params, $ioparams)
	{
		$photo = isset($params['photo'])?$params['photo']:'';
		if ($photo && !is_url($photo) && !is_start_slash($photo)) {
			$photo = $ioparams['_webroot'].'/themes/'.$tplname.'/'.$photo;
			$params['photo'] = $photo;
		}

		$icon = isset($params['icon'])?$params['icon']:'';
		$pos = strrpos($icon, '.');
		if ($icon && $pos !== false && !is_url($icon) && !is_start_slash($icon)) {
			$icon = $ioparams['_webroot'].'/themes/'.$tplname.'/'.$icon;
			$params['icon'] = $icon;
		}
		
		//content
		if (isset($params['content'])) {
			$params['content'] = str_replace("\n", "<p>", $params['content']);
		}
		if (isset($params['description'])) {
			$params['description'] = str_replace("\n", "<p>", $params['description']);
		}
		
		$logo = isset($params['logo'])?$params['logo']:'';
		if ($logo && !is_url($logo) && !is_start_slash($icon)) {
			$logo = $ioparams['_webroot'].'/themes/'.$tplname.'/'.$logo;
			$params['logo'] = $logo;
		}
		
	}
	
	

	protected function initContentModel($tplname, $modinfo, $allparams, $ioparams, $cid)
	{
		$modname = $modinfo['name'];
		$extmodname = isset($modinfo['extmodel'])?$modinfo['extmodel']:false;

		$mid = 0;
		$exm = null;
		if ($extmodname) {
			$mod = Factory::GetModel('model');
			$extmodelinfo = $mod->getOne(array('model'=>$extmodname)) ;
			if ($extmodelinfo) {
				$mid = $extmodelinfo['id'];
				$exm = Factory::GetModel($extmodname);
				if ($ioparams['cleandata'] == 1) {
					$exm->truncate();
				}
			}
		}

		$contentdb = $allparams[$modname];

		$m = Factory::GetModel('site_content');
		foreach($contentdb as $k=>$v) {
			$params = $v;
			//fixed for local
			$this->fixedForLocal($tplname, $params, $ioparams);
			$params['name'] = isset($params['name'])?$params['name']:$params['title'];
			$title = $params['title'];
			$name = $params['name'];

			$params['title'] = $title;
			$params['name'] = $name;
			if (!($res = $m->getOne(array('title'=>$title)))) {
				$params['id'] = 0;
				$params['cid'] = $cid;
				
				$flags = isset($params['flags'])?intval($params['flags']):0;
				$params['flags'] = $flags|0x3;
				$params['status'] = 1;

			} else {
				$params['id'] = $res['id'];				
			}

			$params['mid'] = $mid;
			if (!$m->set($params)) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "set model '$modname' failed!");
				return false;
			}
		}
		return true;
	}



	protected function initSubCatalog($tplname, $ioparams, $tinfo, $subcatalogdb, $catalog)
	{
		foreach ($subcatalogdb as $key => $v) {
			$res = $this->initCatalog($tplname, $ioparams, $tinfo, $v, $catalog);
			if (!$res) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, 'initCatalog failed!');
				//return false;
			}
		}	

		return true;	
	}


	protected function initCatalogSingle($tplname, $params, $ioparams)
	{
		$name = trim($params['name']);
		if (!$name) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no name!");
			return false;
		}
		
		$m = Factory::GetModel('catalog');
		if (($res = $m->getOne(array('name'=>$name)))) {
			$params['id'] = $res['id'];
		}
		
		$tpl_list = isset($params['tpl_list'])?$params['tpl_list']:'list';
		$tpl_content  = isset($params['tpl_content'])?$params['tpl_content']:'content';
		$pid = isset($params['pid'])?$params['pid']:0;


		$flags = isset($params['flags'])?$params['flags']:1;
		if (!is_numeric($flags)) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, 'flags='.$flags, $params);
			$m3 = Factory::GetModel('var');
			$res = $m3->getMaskByTitle('catalog_flags', $flags);
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, 'res='.$res);
			if ($res)
				$flags = $res;
		}

		
		//ifxed photo
		$this->fixedForLocal($tplname, $params, $ioparams);
		
		$params['tpl_list'] = $tpl_list;
		$params['tpl_content'] = $tpl_content;
		$params['pid'] = $pid;
		
		$params['flags'] = $flags;
		$params['status'] = 1;
		
		if (!($res = $m->set($params))) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "set catalog failed!");
			return false;
		}
		
		return $res;		
	}
	
	protected function initCatalog($tplname, $ioparams)
	{
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, 'IN');
		
		
		$catalogdb = get_cache_array('catalogdb', RPATH_TEMPLATES.DS.$tplname.DS.'catalogdb.php');
		if (!$catalogdb) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, 'no catalogdb failed!');
			return false;
		}
		
		$res = false;
		foreach ($catalogdb as $key=>$v) {
			$res = $this->initCatalogSingle($tplname, $v, $ioparams);
		}		
		
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, 'OUT');
		return $res;		
	}
	
	
	protected function initContentSingle($tplname, $params, $ioparams, $catalogdb)
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, 'IN');
		
		$cid = $params['cid'];		
		$m1 = Factory::GetModel('catalog');
		foreach ($catalogdb as $key=>$v) {
			if ($cid == $v['name'] || $cid == $v['cid']) {
				$cinfo = $m1->getOne(array('name'=>$v['name']));
				if ($cinfo) {
					$cid = $cinfo['id'];
					break;
				}
			}
		}
		
		//FIXED cid
		$m2 = Factory::GetModel('content');
				
		$info = $m2->getOne(array('name'=>$params['name'], 'cid'=>$cid));
		if ($info) {
			$params['id'] = $info['id'];
		}
		
				
		$params['cid'] = $cid;		
		$params['status'] = 1;

		$this->fixedForLocal($tplname, $params, $ioparams);
				
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $params);		
		if (($res = $m2->set($params))) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, 'set content failed!');
		}
				
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, 'OUT');
		
		return false;
	}
	
	
	protected function initContent($tplname, $ioparams)
	{
		$catalogdb = get_cache_array('catalogdb', RPATH_TEMPLATES.DS.$tplname.DS.'catalogdb.php');
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $catalogdb);
		
		$contentdb = get_cache_array('contentdb', RPATH_TEMPLATES.DS.$tplname.DS.'contentdb.php');
		if (!$contentdb) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, 'no contentdb failed!');
			return false;
		}
		
		$res = false;
		foreach ($contentdb as $key=>$v) {
			$res = $this->initContentSingle($tplname, $v, $ioparams, $catalogdb);
		}		
		return $res;		
	}
	

	protected function initModelCatalog($tplname, $name, $params, $ioparams)
	{
		$m = Factory::GetModel('catalog');
		$m2 = Factory::GetModel('content');
		if ($ioparams['cleandata'] == 1) {
			$m->truncate();
			$m2->truncate();
		}
		
		if ($ioparams['installdata'] != 1) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no installdata!");
			return false;
		}
		
		foreach ($params as $k3=>$v3) {
			if (!($res = $this->initCatalog($tplname, $ioparams, $v, $v3))) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call initCatalog failed!");
				return false;
			}
		}
		
		return true;
	}

	protected function initModel($modname, $params, $ioparams)
	{			
		$m = Factory::GetModel($modname);
		
		if ($ioparams['cleandata'] == 1) {
			$m->truncate();
		}
		if ($ioparams['installdata'] != 1) {
			return false;
		}
		
		foreach ($params as $key => $v) {
			$name = $v['name'];
			if (!($res = $m->getOne(array('name'=>$name)))) {
				$v['status'] = 1;
			}  else {
				$v['id'] = $res['id'];
			}
			$res = $m->set($v);
			if (!$res) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call set failed! modname=$modname");
				return false;
			}
		}

		return true;
	}

	protected function initModelUser($modname, $udb, $ioparams)
	{
		$m = Factory::GetModel($modname);
		
		$m2 = Factory::GetModel('group');
		$m3 = Factory::GetModel('role');


		foreach ($udb as $key => $v) {
			$params = $v;
			$name = $params['name'];

			$userinfo = $m->getOne("name='$name'");
			if (!$userinfo) {				
				$params['name'] = $name;
				$params['password'] = $name;
				$params['password2'] = $name;
				$params['status'] = 1;
				$res = $m->set($params);

				if (!$res) {
					rlog(RC_LOG_ERROR, __FILE__, __LINE__, "init user failed!");
					break;
				}				
			}

			//check role
			$id = $params['rid'];
			$role = $m3->get($id);
			if (!$role) {
				$role = array();
				$role['rid']	= $rid;
				$role['name'] = $params['description'];
				$m3->set($role);
			}

			//check group
			$id = $params['rid'];
			$group = $m2->get($id);
			if (!$group) {
				$group = array();
				$group['gid']	= $rid;
				$group['name']	= $params['description'].'组';
				$m2->set($group);
			}			
		}
		

		return $res;
	}
	
				
	protected function setTplModule($params)	
	{
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $params);
		
		$m = Factory::GetModel('module');
		$res = $m->getOne(array('mid'=>$params['mid']));
		if ($res)
			$params['id'] = $res['id'];
		
		$res = $m->set($params);										
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "set TPL module failed!", $params);
			return false;
		}

		//处理cid
		$cid = $params['cid']; //可能是个名称
		if (!is_numeric($cid)) {
			$m2 = Factory::GetModel('catalog');
			$cid = $m2->genCatalog(array('name'=>$cid));
		}
		
		//处理FLAGS
		$flags = $params['flags']; //可能是个名称
		if (!is_numeric($flags)) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, 'flags='.$flags, $params);
			$m3 = Factory::GetModel('var');
			$res = $m3->getMaskByTitle('content_flags', $flags);
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, 'res='.$res);
			if ($res)
				$flags = $res;
		}
		
		//参数
		$_params = array();
		$_params['flags'] = $flags;
		$_params['maxnum'] = $params['num'];		
		$_params['cid'] = $cid;		
		$_params['tags'] = $params['tags'];		
		$res = $m->setModuleParams($params['id'], $_params);		
		
		return $res;
	}
	
	protected function parseTemplateFile($tplfile, $tinfo, $ioparams)
	{
		$tplfileinfo = $tinfo['tplfileinfo'];
		$tid = $tplfileinfo['id'];
		$tplname = $tinfo['name'];
		
		$res = parseTplFile($tplfile);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "parse TPL file '$tplfile' failed!");
			return false;
		}
		
		$nr_success = 0;
		
		//links
		if (isset($res['links'])) {
			foreach ($res['links']['mdb'] as $key=>$v) {
				$params = $v;
				$params['tid'] = $tid;
				$params['tplname'] = $tplname;
				
				$res2 = $this->setTplModule($params);
				if (!$res2) {
					rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "set TPL module failed!", $params);
					continue;
				}
				$nr_success ++;
			}
		}
		
		//modules		
		if (isset($res['modules'])) {
			foreach ($res['modules']['mdb'] as $key=>$v) {
				$params = $v;
				$params['tid'] = $tid;				
				$params['tplname'] = $tplname;
				$res3 = $this->setTplModule($params);
				if (!$res3) {
					rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "set TPL module failed!", $params);
					continue;
				}
				$nr_success ++;
			}
		}
		
		return true;
	}	
				
	protected function doInstall($tinfo, &$ioparams=array())
	{
		$name = $tinfo['appname'];
		
		$tcfg = $this->getcfg($name);
		
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $tinfo, $tcfg);
		
		
		$tinfo['tcfg'] = $tcfg;
		
		$tpldir = RPATH_TEMPLATES.DS.$name;
		if (!is_dir($tpldir)) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "no TPL dir '$tpldir'!");
			return false;
		}
		$tinfo['tpldir'] = $tpldir;
		
		//入表
		$params = array();
		$params['name'] = $name;
		$params['title'] = $tinfo['title'];
		$params['description'] = $tinfo['description'];
		$params['filename'] = $name;
		$params['pid'] = 0;
		$params['isdir'] = 1;
		
		$res = $this->getOne(array('filename'=>$name, 'pid'=>0));
		if ($res) {
			$params['id'] = $res['id'];
		}
				
		$res = $this->set($params);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "set template failed!", $params);
			return false;
		}		
		$tinfo['id'] = $params['id'];
		
		//安装CATALOG
		$this->initCatalog($name, $ioparams);
		//安装CONTENT
		$this->initContent($name, $ioparams);
				
		
		$udb = s_readdir($tpldir, "files");
		
		$res = false;
		$nr_total = 0;	
		$nr_errors = 0;	
		$nr_success = 0;	
		
		$hdb = array('.svn');	
		foreach ($udb as $key=>$v) {
			$name = $v;			
			if (in_array($name, $hdb))
				continue;	
			
			$extname = s_fileext($name);
			if ($extname != 'htm')
				continue;
			
			$nr_total ++;
			
			$tplfile = $tpldir.DS.$name;
			if (is_dir($tplfile))
				continue;
			
			$tinfo['tplfile'] = $tplfile;
			
			//template file
			$params = array();
			$params['name'] = $name;
			$params['filename'] = $name;
			$params['pid'] = $tinfo['id'];
			$params['isdir'] = 0;
			
			$res = $this->getOne(array('filename'=>$name, 'pid'=>$tinfo['id']));
			if ($res) {
				$params['id'] = $res['id'];
			}
			$res = $this->set($params);
			if (!$res) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "set template failed!", $params);
				return false;
			}
			
			$tinfo['tplfileinfo'] = $params;
			
			$res = $this->parseTemplateFile($tplfile, $tinfo, $ioparams);
			if (!$res) {
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "parse template file '$tplfile' failed!");
				$nr_errors ++;
				continue;
			}
			
			$nr_success ++;
		}
		rlog('$nr_success='.$nr_success);
		return $nr_success > 0;
	}
	
	public function install($tinfo, &$ioparams=array())
	{
		$res = $this->doInstall($tinfo, $ioparams);
		
		return $res;
	}
	
	
	public function setup($id, &$ioparams=array())
	{
		$res = $this->get($id);
		if (!$res) {
			return false;
		}
		
		$tinfo = $res;
		$tinfo['appname'] = $tinfo['name'];
		$res = $this->doInstall($tinfo, $ioparams);
		
		return $res;
	}
	
	public function get_tpls($key=null)
	{
		$params = array('status'=>1,'pid'=>0);
		$udb = $this->selectForView($params);
		return $udb;
	}
	
	
	public function preview($name)
	{
		$tinfo = $this->getOne(array('name'=>$name));
		
		if (!empty($tinfo['preview'])) {
			exit($tinfo['preview']);
		}
			
		$imgtypes = array('png', 'jpg', 'gif');
		foreach ($imgtypes as $key=>$v) {
			$file = RPATH_TEMPLATES.DS.$name.DS.'preview.'.$v;
			if (file_exists($file)) {
				header("Content-Type: image/".$v);
				$res = readfile($file);
				break;
			}
		}
		exit;
	}
		
	public function get_tpl_select($tpl)
	{
		$res = "";
		$defaultTitle = i18n('Default');
		$res .= "<option value='default'>$defaultTitle</option>";
		$udb = $this->get_tpls();
		foreach ($udb as $key=>$v) {
			$name = $v['name'];
			$selected = $name == $tpl ? 'selected': '';
			$res .= "<option value='$name' $selected>$v[title]</option>";
		}		
		return $res;
	}
		
	
	public function get_child_template_select($child, $root, $tpl, $permit_select_index=false)
	{
		!$root && $root = 'default';
		$res = "";
		$templates = $this->get_tpls();
		$template= $templates[$root];	
		if ($permit_select_index) {
			$childs = $template['index'];
			if ($childs) {
				foreach ($childs as $key=>$v) {
					$selected = $key == $tpl ? 'selected': '';
					$res .= "<option value='$key' $selected>$v</option>";
				}
			}
		}
		
		$childs = $template[$child];
		if ($childs) {
			foreach ($childs as $key=>$v) {
				$selected = $key == $tpl ? 'selected': '';
				$res .= "<option value='$key' $selected>$v</option>";
			}
		}		
		return $res;
	}
	
	
	public function get2($key=null)
	{
		if (!$this->_templates) {
			$file = RPATH_CONFIG.DS.'templates.php';
			if (file_exists($file)) {
				require $file;
				if ($templates) {
					$this->_templates = $templates;					
				}
			}
		}
		
		
		if ($key) {
			return isset($this->_templates[$key])?$this->_templates[$key]:array();
		} else {
			return $this->_templates;
		}
	}	
	
	
	protected function doUninstall($tinfo)
	{
		$res = true;
		
		$name = $tinfo['name'];
		
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "TODO ...");
		
		$tcfg = $this->getcfg($name);
		
		return $res;
	}
	
	public function uninstall($tinfo)
	{
		$res = $this->doUninstall($tinfo);
		return $res;
	}
}
