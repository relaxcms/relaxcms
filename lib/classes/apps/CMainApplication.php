<?php

/**
 * 主应用基类
 *
 */
class CMainApplication extends CApplication
{
		
	public function __construct($name, $options = array())
	{
		parent::__construct($name, $options);
	}
	
	public function CMainApplication($name, $options = array())
	{
		$this->__construct($name, $options);
	}
	
	public function isPublicItem($component, $tname)
	{
		$menus = $this->getMenus();
		
		if (!isset($menus[$component])) {
			return false;
		}
		
		$m = $menus[$component];
		if (!$m) {
			
			return false;
		}
		
		if (!$m['pid']) {
			return true;
		}
		
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, 'component='.$component, 'tname='.$tname, $m);		
		if ($tname && isset($m['task']) && isset($m['task'][$tname]) && $this->isPermistionPublic($m['task'][$tname])) {
			//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "open task '$tname'!!!!!!");
			return true;
		}
			
		return false;
	}
	
	
	/**
	 * 任务分配, 处理选项
	 *
	 * @return mixed This is the return value description
	 *
	 */
	public function dispatch(&$ioparams=array())
	{
		$ss = $this->getSession();

		$component = $ioparams['cname'];
		$tname = $ioparams['tname'];
		
		if (!$ss->isLogin()) {
			$component = $this->switchIfTop($component, $tname);			
			if (!$this->isPublicItem($component, $tname)) {
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__, 'no login of "'.$component.'"');
				$component = 'login';	
			}
			$isLogin = false;
		} else {
			$component = $this->switchIfTop($component, $tname);			
			//检查是不是首次登录
			if ($ss->isNeedChangePassword($component) && $component != 'logout') {
				//set_error('str_user_first_login_must_change_passwd');
				$component = 'my_password';
			}
			$isLogin = true;
		}
				
		$ioparams['component'] = $component;	
		$ioparams['isLogin'] = $isLogin;	
		
		return false;
	}
	
	protected function initSession()
	{
		$this->_session = Factory::GetAdmin();
	}
	
	public function hasMenu()
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "in CMainApplication::hasMenu");
		if (!$this->_user)
			return false;
		if (!$this->_islogin)
			return false;
		return true;	
	}
	
	//检查权限, 越权操作，直接退出
	public function hasPrivilegeOf($component, $task='')
	{
		$ss = $this->getSession();
		
		$menus = $this->getMenus();
		if (!isset($menus[$component])) {
			return true;
		}
			
		$menuitem = $menus[$component];
		$pid = $menuitem['pid'];
		if (!$pid) {//不用授权即可访问
			//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "pid=$pid, component=$component,task=$task");
			return true;
		}
			
		//不用授权即可访问	
		if ($task && isset($menuitem['task']) 
				&& isset($menuitem['task'][$task]) && $menuitem['task'][$task] === 'i') {
			return true;
		}
		
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $menuitem);
		
		$perm = 0;
		if ($task && !empty($menuitem['task'][$task]) && $menuitem['task'][$task]) {
			$perm = $this->getPermistionIdByName($menuitem['task'][$task]);
		}
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "############### component=$component, task=$task, perm=$perm ###############");
		
		if ($ss->hasPrivilegeOf($pid, $perm))
			return true;
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, 'no privilege of "'.$perm.'", $component='.$component.', $task='.$task.',pid='.$pid);
		//rlog('permision='.$permision);
		
		return false;
	}
		
	public function install($ioparams=array())
	{
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "IN");
		
		$dir = $this->_appdir;
				
		//标准SQL
		$dbtype = 'sql';
		
		//table
		$db = Factory::GetDBO();		
		$sql = $dir.DS.'database'.DS.$dbtype.DS."create_table.sql";
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, $sql);
		if (file_exists($sql)) {
			if (!$db->import($sql)) {
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "call import '$sql' error.");
				return false;
			}
		}
		
		//procedure
		$db = Factory::GetDBO();		
		$sql = $dir.DS.'database'.DS.$dbtype.DS."create_procedure.sql";
		if (file_exists($sql)) {
			if (!$db->exec_procedure_file($sql)) {
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "call exec_procedure '$sql' error.");
				return false;
			}
		}
				
		
		//初始化
		$sql = $dir.DS.'database'.DS.$dbtype.DS."init_table.sql";
		if (file_exists($sql)) {
			if (!$db->import($sql)) {
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "call exec_script: '$sql' error.");
				return false;
			} else {
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "exec_script: '$sql' ok.");
			}
		} else {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "no '$sql'");
		}
		
		
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "OUT");
		return true;
	}
	
	public function uninstall()
	{
		//卸载数据库
		$dir = $this->_appdir;
		
		//安装数据库
		$cf = get_config();
		$dbtype = 'sql';
		
		$db = Factory::GetDBO();		
		$sql = $dir.DS.'database'.DS.$dbtype.DS."drop_table.sql";
		if (file_exists($sql)) {
			if (!$db->import($sql)) {
				rlog(RC_LOG_DEBUG, __FILE__,__LINE__,"call exec_script: $sql, error.");
				return false;
			}
		}		
		return true;
	}
		
	/*protected function _initModels()
	{
		if (!file_exists(RPATH_CACHE.DS.'models.php')) 
			$this->cacheModels();		
	}*/
	
	
	
	
	protected function runlocalwebservice()
	{
		if ($this->_client != "127.0.0.1")
			exit("forbidden");
		
		$apps = Factory::GetApps();
		foreach ($apps  as $key=>$v) {
			$app = Factory::GetApp($key);
			$app->localwebservice();
		}	
	}
	
	public function login($params=array())
	{
		$ss = $this->getSession();		
		if (!$ss->isLogin()) {
			if (($res = $ss->login($params)) !== true) {		
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "user login failed! res=$res");		
				return $res;
			}
		}
		return true;
	}
	
	
	public function logout()
	{
		$ss = $this->getSession();
		$ss->logout();
		
		setMsg("str_user_logout_ok");
	}
		
		
	/* ============================================================================
	 * menu
	 * 菜单
	 *
	 * ===========================================================================*/
	
	protected function formatMenus($allmenus)
	{
		$mdb = array();		
		if ($allmenus) {
			foreach($allmenus as $k=>$appmenu) {	
				if (!$appmenu)
					continue;	
				if ($k == 'defmenu') {
					$app = $this->_app;
					$k = $this->_app->getAppName();
				}
				else 		
					$app = Factory::GetApp($k);
				
				if (!$app)
					$app =$this->_app;
				$lang = $app->getI18n();		
				
				foreach ($appmenu as $key=>$m) {
					
					if (isset($lang['menu_'.$key]))				
						$title = $lang['menu_'.$key];
					else 
						$title = $key;
					
					$m['title'] = $title;
					if (isset($lang['priv_'.$key]))
						$privtitle = $lang['priv_'.$key];
					else 
						$privtitle = $m['title'];
					$m['privtitle'] = $privtitle; // 权限名称					
					$m['app'] = $k;
					
					//level
					if (!isset($m['level']))
						$m['level'] = 1;
					
					$mdb[$key] = $m;
				}
			}
		}
		//register and find api
		$apidb = array();
		foreach ($mdb as $key => &$v) {
			if (isset($v['task'])) {
				foreach ($v['task'] as $k2=>$v2) {
					if (strpos($v2, 'i') !== false) {
						rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "API:$k2=$v2");
						$apiname = $v2;
						$apidb[$apiname] = array('apiname'=>$apiname, 'cname'=>$v['component'], 'aname'=>$v['app']);
					}
				}
			}			
			if (isset($v['pid']) && $v['pid'] == 0)
				continue;
			if (!isset($v['sort']))
				$v['sort'] = 0;
			$v['pid'] = $this->registerPrivilege($v);
			if (!$v['pid']) {
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "register privilege failed!", $v);
			}			
		}	
		//rlog($apidb);	
		
		//cache apidb
		$file = $this->_cachedir.DS.'api.php';
		$cache = Factory::GetCache();
		$cache->cache_array('apidb', $apidb, $file);
		
		
		return $mdb;
	}
	
	protected function formatMenuItem($appname, $name, $m, &$mdb)
	{
		$i18n = $this->getI18n();	
		
		if (isset($i18n['menu_'.$name]))				
			$title = $i18n['menu_'.$name];
		else 
			$title = $name;
		
		$m['title'] = $title;
		if (isset($i18n['priv_'.$name]))
			$privtitle = $i18n['priv_'.$name];
		else 
			$privtitle = $m['title'];
		
		$m['privtitle'] = $privtitle; // 权限名称					
		$m['app'] = $appname;
		//level
		if (!isset($m['level'])) {
			$pname = $m['parent'];
			$m['level'] = isset($mdb[$pname])?$mdb[$pname]['level']:1;
		}
		
		if (isset($mdb[$name])) {
			$oldmenu = $mdb[$name];
			$m['app'] = $oldmenu['app'];
			$newmenu = array_merge($oldmenu, $m);
			//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "fixed menu", $oldmenu, $m, $newmenu);
			
			$mdb[$name] = $newmenu;
		} else {				
			$mdb[$name] = $m;
		}
	}
	
	
	protected function cacheMenus($lang)
	{
		$appname = $this->_name;
		
		//common
		//common menus
		//合并APP菜单
		$mdb = array();		
		$file = RPATH_INCLUDES.DS."menus.php";
		if (is_file($file)) {
			require $file;
			$sysmenus = $menus;
		} else {
			$sysmenus = array();
		}
		foreach ($sysmenus as $key=>$v) {//默认
			$this->formatMenuItem('', $key, $v, $mdb);			
		}
				
		
		//当前应用菜单, 默认都在 <appname>/include/menus.php中
		$defmenus = $this->getAppMenus();		
		foreach ($defmenus as $key=>$v) {//默认
			$this->formatMenuItem('', $key, $v, $mdb);			
		}
		
		$apps = Factory::GetApps();		
		if ($apps) {
			foreach ($apps as $key=>$appcfg) {
				$app = Factory::GetApp($key);
				if (!$app) {
					rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "no app '$key'!");
					continue;
				}
				
				if ($key == $this->_name) {
					rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "skip me '$key'!");
					continue;
				}
				
				$appmenus = $app->getAppMenus();
				
				foreach ($appmenus as $name=>$v) {
					$this->formatMenuItem($key, $name, $v, $mdb);			
				}
			}
		}
		
		
		
		//register and find api
		$apidb = array();
		foreach ($mdb as $key => &$v) {
			if (isset($v['task']) && is_array($v['task'])) {
				foreach ($v['task'] as $k2=>$v2) {
					if (strpos($v2, 'i') !== false) {//找到
						//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "cacheMenus API:$k2=$v2");
						$apiname = $k2;
						$apidb[$apiname] = array('apiname'=>$apiname, 'cname'=>$v['component'], 'aname'=>$v['app']);
					}
				}
			}
			if (isset($v['pid']) && $v['pid'] == 0)
				continue;
			if (!isset($v['sort']))
				$v['sort'] = 0;
			$v['pid'] = $this->registerPrivilege($v);
			if (!$v['pid']) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "cacheMenus register privilege failed!", $v);
			}			
		}	
		//rlog($apidb);	
		
		//cache apidb
		$file = $this->_cachedir.DS.'api.php';
		$cache = Factory::GetCache();
		$cache->cache_array('apidb', $apidb, $file);
		
		$file = $this->_cachedir.DS.'menu_'.$this->_lang.'.php';
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "cache menus '$file'");
		$cache = Factory::GetCache();
		$cache->cache_array('menus', $mdb, $file);
		
		return $mdb;
	}
	
	protected function initMenus()
	{
		$lang = $this->_lang;
		$file = $this->_cachedir.DS."menu_".$lang.".php";
		//rlog(RC_LOG_ERROR, __FILE__, __LINE__,$file);
		
		if (!file_exists($file)) 
			$this->cacheMenus($lang);
		if (file_exists($file)) {
			require_once($file);
			$this->_menus = $menus;					
		}	
	}
	
}