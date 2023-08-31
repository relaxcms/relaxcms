<?php
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

/**
 * 应用基类
 *
 */
class CApplication extends CObject
{
	protected $_name = null;
	//! 应用类型
	protected $_apptype = null;

	protected $_options = array();
	
	
	/**
	 * 系统内部名称（version.php定义）
	 *
	 * @var mixed 
	 *
	 */
	protected $_sys_name = null;
	/**
	 * 版本标识
	 *
	 * @var mixed 
	 *
	 */
	protected $_sys_version = null;
	
	//基于RC构建的不同产品，名称与版本不同，升级RC是一样，特分离RC版本与软件产品版本：
	protected $_product_name = null;
	protected $_product_version = null;
	
	//! 配置
	protected $_appcfg = array();
	//! 默认配置文件
	protected $_appcfgfile = null;
	
	protected $_cfgloaded = false;
	//! 动态可配置运行配置
	//protected $_cfg = array();
	
	/**
	 * 默认app 所有目录
	 *
	 * @var mixed 
	 *
	 */
	protected $_default_appdir;
	
	
	/**
	 * 默认app模板目录
	 *
	 * @var mixed 
	 *
	 */
	protected $_default_tdir;
	
	
	
	/**
	 * 当前会话
	 *
	 * @var mixed 
	 *
	 */
	protected $_session = null;
	//! _islogin
	protected $_islogin = false;
	
	/**
	 * 引导APP所在目录
	 * 
	 */
	protected $_appdir;
	/** 执行 APP 所在目录, eg: a=<APPNAME> */
	protected $_rundir;
	
	
	/**
	 * 默认缓存目录
	 *
	 * @var mixed 
	 *
	 */
	protected $_cachedir = '';
	
	
	protected $_appbase = null;
	
	protected $_thename = null;
	protected $_tplname = null;
	
	protected $_aname = null;
		
	//指定APP
	protected $_appdb = null;
	protected $_app = null;
	protected $_appinfo = null;
	
	
	//国际化
	protected $_i18ns = array();
	protected $_lang;
	
	protected $_activeComponent = null;
	//菜单
	protected $_menus = array();
	protected $_default_menus = array();
	protected $_allmenus = array();
	
	
	/**
	 * $_default_flags_mask 用户登录掩码
	 *
	 * 默认用户登录掩码，只能用户flags在掩码内才允登录
	 * @var mixed 
	 *
	 */
	protected $_default_flags_mask = 0xffff;
	
	
	/**
	 * This is variable _default_level_mask description
	 *
	 * 默认左菜单栏掩码，菜单项的过滤使用，level小于此项值才显示
	 *  
	 * @var mixed 
	 *
	 */
	protected $_default_level_mask = 0xffff;
	
	
	/**
	 * 全局请求对象（参数解析过滤
	 *
	 * @var mixed 
	 *
	 */
	protected $_request;
	
	/** 已经缓存的APP菜单 */
	protected $_appmenus = array();
	
	/** 已经缓存的API接口 */
	protected $_apidb = array();
	
	
	public function __construct($name, $options = array())
	{
		$this->_name	= $name;
		$this->_options	 = $options;		
		$this->_appdir = $options['appdir'];
		$this->_rundir = $options['appdir'];
		$this->_cachedir = RPATH_CACHE.DS.$name;
		
		$this->_init();	
	}

	public function CApplication($name, $options = array())
	{
		$this->__construct($name, $options);
	}
	
	
	/* ========================================================
	 * 初始化
	 * =======================================================*/
	protected function _init()
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "IN appname=".$this->_name);
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "OUT");
		return false;
	}
	
	/**
	 * 单实例创建
	 *
	 * @param mixed $name 类名
	 * @param mixed $options 配置选项信息
	 * @return mixed This is the return value description
	 *
	 */
	static function &GetInstance($name, $options = array())
	{
		static $instances;
		$cname = strtolower($name);
		$classname = ucfirst($name)."Application";
		
		if (!isset( $instances )) 
			$instances = array();
		
		if (empty($instances[$name]))	{
			if (file_exists(RPATH_ROOT.DS.$name.DS.$name.".php")) {	
				$appdir = RPATH_ROOT.DS.$name;
				$file = $appdir.DS.$name.".php";
			} else if (file_exists(RPATH_APPS.DS.$name.DS.$name.".php")) {
					$appdir = RPATH_APPS.DS.$name;
					$file = $appdir.DS.$name.".php";
			} else {				
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__,"no app '$name'!");
				return null;
			}
			
			$options['appdir'] = $appdir;
		
			require_once $file;
			
			$instance = new $classname ($cname, $options);				
			$instances[$name] = &$instance;
		}
		
		return $instances[$name];
	}

	/* ========================================================
	 * Utility functions
	 * =======================================================*/
	
	public function getAppName()
	{
		return $this->_name;
	}
	
	
	protected function isApp($name)
	{
		if (!$this->_appdb)
			$this->_appdb = Factory::GetApps();
		
		if (isset($this->_appdb[$name]))
			return true;
		return false;
	}
	
	protected function isComponent($cname)
	{
		return false;
	}
	
	protected function initApi()
	{
		$file = $this->_cachedir.DS."api.php";		
		if (is_file($file)) {
			require $file;
			if (isset($apidb)) {
				$this->_apidb = $apidb;
			}
		}		
	}
	
	protected function isApi($name)
	{
		if (!$this->_apidb)
			$this->initApi();
		
		if (isset($this->_apidb[$name]))
			return true;
		return false;
	}
	
	
	
	protected function getDefaultComponent()
	{
		return 'main';
	}
	
	public function setMsg($level, $msg) 
	{
		$this->setMessage($msg, $level);
	}
	
	public function setLastMsg($key, $msg) 
	{
		$this->_activeComponent->assign('sys_message', $msg);		
		$this->_activeComponent->assign('sys_status', 'failed');
	}	
	
	
	//检查权限, 越权操作，直接退出
	public function hasPrivilegeOf($component, $task='')
	{
		return false;
	}
	
	
	/**
	 * 获取错误信息，以HTML的div格式返回
	 *
	 * @return mixed This is the return value description
	 *
	 */
	public function get_error_html()
	{
		if ($this->_errors == null) {
			return false;
		}
		
		$res = "<div class='errstr'><ul>";		
		foreach ($this->_errors as $key=>$v) {
			$res .= "<li>$v</li>";			
		}				
		$res .= "</ul></div>";		
		return $res;
	}
	
	
	/**
	 * 显示系统消息
	 *
	 * @param mixed $msg This is a description
	 * @param mixed $backurl This is a description
	 * @param mixed $target This is a description
	 * @param mixed $ext This is a description
	 * @param mixed $type This is a description
	 * @return mixed This is the return value description
	 *
	 */
	public function showMessage($msg, $backurl=null, $target="_self", $ext=null, $type="error")
	{
		$msg = i18n($msg);
		
		$options['msg_text'] = $msg;
		$options['msg_backurl'] =  $backurl;
		$options['msg_target' ] = $target;
		$options['msg_ext' ] = $ext;
		$options['msg_type' ] = $type;
		
		if ($type == 'error') {
			$options['msg_alert_type' ] = 'danger';
			$options['msg_alert_btn' ] = 'red';
			$options['msg_title'] = i18n('str_failed');
			$status = -1;
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, $msg);
			
		} else {
			$options['msg_alert_type' ] = 'success';
			$options['msg_alert_btn' ] = 'green';	
			$options['msg_title'] = i18n('str_success');
			$status = 0;
		}
		if ($this->_oname == 'json') 
			showStatus($status);
		
		$this->_activeComponent->setMessage($options);
	}
	
	
	/**
	 * 查询运行时间
	 *
	 * @return mixed This is the return value description
	 *
	 */
	public function get_spends()
	{
		return time() - $this->_ts;
	}
	
	/**
	 * 重定向
	 *
	 * @param mixed $url This is a description
	 * @return mixed This is the return value description
	 *
	 */
	public function redirect( $url)
	{
		if (headers_sent()) {
			echo "<script>document.location.href='$url';</script>\n";
		} else {
			//@ob_end_clean(); // clear output buffer
			header( 'HTTP/1.1 301 Moved Permanently' );
			header( 'Location: ' . $url );
		}
		
		$this->close();
	}
	
	
	/**
	 * 关闭，退出
	 *
	 * @param mixed $code This is a description
	 * @return mixed This is the return value description
	 *
	 */
	public function close( $code = 0 ) 
	{
		exit($code);
	}
	
	public function getActiveComponent()
	{
		return $this->_activeComponent;
	}
	
	public function getAppbase()
	{
		return $this->_appbase;
	}
	
	public function getWebroot()
	{
		return $this->_request->getWebroot();
	}
	
	public function getWeburl()
	{
		return $this->_request->getWeburl();
	}
	
	public function localwebservice($localservicecfg)
	{
		return false;
	}
	
	public function getDashbordInfo(&$ioparams=array())
	{
		return false;
	}
	
	public function getMyInfo(&$ioparams=array())
	{
		return false;
	}	
	
	/* ==============================================================================================
	 * I18N functions
	 *	
	 * ============================================================================================*/
	
	private function cacheI18n($lang)
	{
		//LIB
		$lib_i18n_array = array();
		$app_i18n_array = array();
		
		$lib_i18n_file = $this->_appdir.DS."i18n".DS.$lang.DS."i18n.php";
		if (file_exists($lib_i18n_file)) {
			require $lib_i18n_file;
			$lib_i18n_array = $i18n;
		} 
		
		
		//全局
		$g_i18n_file = array();
		$g_i18n_file = RPATH_I18N.DS.$lang.DS."i18n.php";
		if (file_exists($g_i18n_file)) {
			require $g_i18n_file;
			$g_i18n_array = $i18n;
		}
		
		
		//合并
		$alli18n = $lib_i18n_array;		
		if (isset($g_i18n_array))
			$alli18n = array_merge($alli18n, $g_i18n_array);		
				
		//apps
		$apps = Factory::GetApps();
		if ($apps) {
			foreach ($apps as $key=>$v) {
				$file =RPATH_APPS.DS.$key.DS."i18n".DS.$lang.DS."i18n.php";
				if (file_exists($file)) {
					require $file;
					$alli18n = array_merge($alli18n, $i18n);
				}				
			}
		}
		
		
		$tdir = $this->_cachedir.DS."i18n";
		if (!is_dir($tdir))
			s_mkdir($tdir);
		
		$cache = Factory::GetCache();
		$file = $tdir.DS.$lang.".php";
		$cache->cache_array("i18n", $alli18n, $file);
		
		$this->_i18ns = $alli18n;		
	}
	
	protected function initI81n()
	{
		$lang = $this->_lang;		
		$file = $this->_cachedir.DS."i18n".DS.$lang.".php";
		if (!file_exists($file)) 
			$this->cacheI18n($lang);
		elseif (file_exists($file)) {
			require_once($file);
			$this->_i18ns = $i18n;					
		}
	}
	
	public function getI18n()
	{
		if (!$this->_i18ns) 
			$this->initI81n();
		return $this->_i18ns;
	}
	
	public function i18n($fmtstr, $default='')
	{
		$lang = $this->getI18n();
		$str = $fmtstr;
		if ($str && !empty($lang[$str])) {
			$str = $lang[$str];
			$args = func_get_args();
			if (count($args) > 1) {
				$phrase = array_shift($args);
				$str = vsprintf($str, $args);		
			}
		} else if ($default) {
				$str = $default;
			}	
		return $str;
	}
	
	
	protected function initAppcfg()
	{
		//static config
		$appcfgfile = $this->_appdir.DS.'config.php';
		if (file_exists($appcfgfile)) {
			require($appcfgfile);
		} else {
			$appcfg = array(
					'copyright'=>'RC',
					'description'=>'RELAXCMS',
					'website'=>'https://www.relaxcms.com',
					);
			
		}
		
		$appcfg['appname'] = $this->_name;
				
		$this->_appcfg = $appcfg;
	}
		
	public function getAppcfg()
	{
		if (!$this->_appcfg) 
			$this->initAppcfg();
		return $this->_appcfg;
	}
	
	
	protected function initVersion()
	{
		//static config
		$verfile = RPATH_ROOT.DS.'version.php';
		if (file_exists($verfile)) {
			require($verfile);
			$this->_sys_name = SYS_NAME;
			$this->_sys_version = SYS_VERSION;
		} else {
			$this->_sys_version = "0.0.1";
			$this->_sys_name = "relaxcms";
		}
		
		//
		$productfile = RPATH_ROOT.DS.'product.php';
		if (file_exists($productfile)) {
			require($productfile);
			$this->_product_name = PRODUCT_NAME;
			$this->_product_version = PRODUCT_VERSION;
		} else {
			$this->_product_name = $this->_sys_name;
			$this->_product_version = $this->_sys_version;
		}
	}
	
	public function getSysName()
	{
		if (!$this->_sys_version) 
			$this->initVersion();
		return $this->_sys_name;
	}
	
	public function getSysVersion()
	{
		if (!$this->_sys_version) 
			$this->initVersion();
		return $this->_sys_version;
	}
	
	public function getVersion()
	{
		return $this->getSysVersion();
	}
	
	public function getProductVersion()
	{
		if (!$this->_product_version) 
			$this->initVersion();
		return $this->_product_version;
	}
	
	public function getProductName()
	{
		if (!$this->_product_version) 
			$this->initVersion();
		return $this->_product_name;
	}
	
	/* ==============================================================================================
	 *  MENU functions
	 *	
	 * ============================================================================================*/
	protected function registerPrivilege($menu)
	{
		if (!$menu)
			return false;
		if (!isset($menu['component']))
			return false;
		
		$m = Factory::GetModel('privilege');
		$res = $m->getOne(array('component'=>$menu['component']));
		if ($res)
			return $res['id'];
		else {
			$params = array(
					'name' => $menu['name'],
					'component' => $menu['component']
					);
			
			$res = $m->set($params);
			if (!$res) {
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "set privilege failed!");
				return false;
			}
			
			return $params['id'];
		}
	}
	
	protected function initAppMenus()
	{
		$file = $this->_appdir.DS.'includes'.DS."menus.php";
		if (is_file($file)) {
			require $file;
		} else {
			$menus = array();
		}
		$this->_appmenus = $menus;	
	}
		
	public function getAppMenus()
	{
		if (!$this->_appmenus)	{
			$this->initAppMenus();
		}
		return $this->_appmenus;
	}
	
	protected function initMenus()
	{
		return false;
	}
		
	public function getMenus()
	{
		if (!$this->_menus) 
			$this->initMenus();
		return $this->_menus;
	}	
	
	protected function getMenusInfo($name) 
	{
		$menus = $this->getMenus();
		return $menus[$name];
	}
	
	public function getParentMenuPid($pid)
	{
		$parent = '';
		$menus = $this->getMenus();
		foreach ($menus as $key=>$v) {
			if ($v['pid'] == $pid) {
				$parent = $v['parent'];
				break;
			}
		}
		
		if (!$parent)
			return 0;
		if (!isset($menus[$parent]))
			return 0;
		return $menus[$parent]['pid'];
	}
	
	protected function getPermistionId($permisions) 
	{
		$m = Factory::GetModel('privilege');
		return $m->getPermistionId($permisions);		
	}
	
	protected function getPermistionIdByName($name) 
	{
		$m = Factory::GetModel('privilege');
		return $m->getPermistionIdByName($name);		
	}
	
	protected function isPermistionPublic($name) 
	{
		$m = Factory::GetModel('privilege');
		return $m->isPermistionPublic($name);		
	}
	
	
	public function getMenusPids()	
	{
		$pids = array();		
		$menus = $this->getMenus();
		
		foreach($menus as $key=>$v)
		{
			$pid = $v['pid'];
			if (!$pid)
				continue;
			if (!isset($v['task']))
				continue;
			
			$permid = $this->getPermistionId($v['task']);	
			$level = 0;
			if (isset($v['level']))
				$level = intval($v['level']);			
			
			$pids[$pid] = array('pid'=>$pid, 'permision'=>$permid, 'level'=>$level);		
			//parent
			if ($v['parent']) {
				if (isset($menus[$v['parent']])) {
					$ppid = $menus[$v['parent']]['pid'];
					if (!isset($pids[$ppid])) {
						//rlog($pids);
					}
					$pids [$pid]['parent'] = $pids[$ppid];
				}
			} 	
		}
		return $pids;	
	}
	
	
	/**
	 * 返回子菜单
	 *
	 */
	protected function getSubMenus($menus, $mkey)
	{
		$mdb = array();
		$m = $menus[$mkey];
		
		$parent = $m['name'];
				
		foreach ($menus as $key=>$v) {
			if ($v['parent'] != $parent )
				continue;
			$mdb[$key] = $v;
		}		
		return $mdb;
	}
	
	
	/**
	 * 返回顶层菜单
	 *
	 */
	protected function getTopMenus($menus)
	{
		$mdb = array();
		foreach ($menus as $key=>$v) {
			if ($v['parent'])
				continue;
			$mdb[$key] = $v;
		}		
		return $mdb;
	}
	
	/**
	 * 合并所有apps菜单，按用户权限返回用户菜单
	 *
	 * @param mixed $key This is a description
	 * @return mixed This is the return value description
	 *
	 */
	protected function filterMenus($menus, $pkey='all', $ifexclude=false)
	{
		$cf = get_config();
		
		$_mdb = array();		
		$mdb = array();
		
		if (!$pkey) {
			$mdb = $this->getTopMenus($menus);				
		} else if ($pkey == 'all') {
			$mdb = $menus;
		} else {
			$mdb = $this->getSubMenus($menus, $pkey);
		}
		
		$ss = $this->getSession();
			
		//过滤
		foreach ($mdb as $key=>$v) {
			$name = $v['name'];
			$pid = $v['pid'];
			
			if (isset($v['hidden']) && $v['hidden'])
				continue;
			if (isset($v['level']) && ($v['level'] & $this->_default_level_mask) === 0) {
				continue;
			}
			
			if ($ifexclude && isset($v['is_exclude']) && $v['is_exclude'])
				continue;				

			if ($ss->hasPrivilegeOf($pid, 0)){//菜单项权限
				$_mdb[$key] = $v;				
			}  else {
				//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $v);
			}
				
		}

		return $_mdb;
	}
	
	
	public function getCurrentMenuTree($activeItem='', $options=array())
	{
		
		$allmenus = $this->getMenus();
		
		$menus = $this->filterMenus($allmenus, '');	
		
		//安位置过滤
		if (isset($options['pos'])) {
			$mdb = array();
			$pos = $options['pos'];
			foreach ($menus as $key=>$v) {
				if (!$v['pos']) 
					continue;
				$pdb = explode(',', $v['pos']);
				if (in_array($pos, $pdb))
					$mdb[$key] = $v;
			}
			$menus = $mdb;
		}
		if (isset($options['keys']) && is_array($options['keys'])) {
			$mdb = array();
			$keys = $options['keys'];
			foreach ($menus as $key=>$v) {
				if (in_array($key, $keys))
					$mdb[$key] = $v;
			}
			$menus = $mdb;
		}

		
		//排序
		array_sort_by_field($menus, "sort", false);				

		foreach ($menus as $key=>&$v) {
			//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, 'key='.$key);
		

			//子菜单
			$submenus =  $this->filterMenus($allmenus, $key);


		
			
			//排序
			array_sort_by_field($submenus, "sort", false);				
			
			if ($activeItem && isset($submenus[$activeItem])) { //活动项
				$v['active'] = true;
				$submenus[$activeItem]['active'] = true;				
			}
			
			$v['childen'] = $submenus;	
		}		
		return $menus;		
	}
	
	
	protected function switchIfTop($component, $tname='')
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "IN component=".$component);
		$menus = $this->getMenus();
		
		$foundCom = "";
		$m = array();
		if (isset($menus[$component]))
			$m = $menus[$component];
		if (!$m || $m['parent'] || ($m['task'] && $m['task'][$tname]))
			return $component;
			
		foreach($menus as $k=>$v) {
			if ($v['parent'] == $component) {
				if ($v['hidden'])
					continue;
				if (!$this->hasPrivilegeOf($v['component'])) 
					continue;
				if (!$foundCom)
					$foundCom = $v['component'];
				if ($v['is_default']) {
					$foundCom = $v['component'];
					break;
				}
			}
		}	
		if (!$foundCom)
			$foundCom = $component;
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "foundCom=".$foundCom);
		
		return $foundCom;
	}
	/* ==============================================================================================
	 * cache functions
	 *	
	 * ============================================================================================*/
	
	public function cache()
	{
		$this->cacheI18n($this->_lang);	
		$this->cacheMenus($this->_lang);	
		$this->cacheModels();	
	}
	
	/* ==============================================================================================
	 * session functions
	 *	
	 * ============================================================================================*/
	protected function initSession()
	{
		$this->_session = Factory::GetUser();
	}
	
	protected function getSession()
	{
		if (!$this->_session) 
			$this->initSession();
		return $this->_session;
	}
	
	public function getUserInfo()
	{
		$ss = $this->getSession();
		if (!$ss)
			return false;
			
		$res = $ss->getCurrentUserInfo();
		return $res;
	}
	
	
	/* ==============================================================================================
	 * service functions
	 *	
	 * ============================================================================================*/
	
	protected function get_localwebservice_last_timestamp()
	{
		if (file_exists(RPATH_CACHE.DS."cach_localwebservice_last_timestamp_".$this->_name))
			return file_get_contents(RPATH_CACHE.DS."cach_localwebservice_last_timestamp_".$this->_name);
		else 
			return 0;
	}
	
	protected function set_localwebservice_last_timestamp($ts)
	{
		file_put_contents(RPATH_CACHE.DS."cach_localwebservice_last_timestamp_".$this->_name, $ts);
	}
	
	
	protected function check_localwebservice_timeout($timeout)
	{
		
		$last_ts = intval($this->get_localwebservice_last_timestamp());
		$ts = time();		
		if ($ts - $last_ts < $timeout) {//执行一次
			return false;
		} 		
		$this->set_localwebservice_last_timestamp($ts);
		return true;
	}
	
	protected function get_localwebservice_last_timestamp2()
	{
		if (file_exists(RPATH_CACHE.DS."cach_localwebservice_last_timestamp2_".$this->_name))
			return file_get_contents(RPATH_CACHE.DS."cach_localwebservice_last_timestamp2_".$this->_name);
		else 
			return 0;
	}
	
	protected function set_localwebservice_last_timestamp2($ts)
	{
		file_put_contents(RPATH_CACHE.DS."cach_localwebservice_last_timestamp2_".$this->_name, $ts);
	}
	
	protected function check_localwebservice_timeout2($timeout)
	{
		
		$last_ts = intval($this->get_localwebservice_last_timestamp2());
		$ts = time();		
		if ($ts - $last_ts < $timeout) {//执行一次
			return false;
		} 		
		$this->set_localwebservice_last_timestamp2($ts);
		return true;
	}
	
	
	/* ========================================================
	 * install and uninstall functions
	 * =======================================================*/
	
	////////////////////////////////// app protect methods ///////////////////////////////////////////
	public function install($ioparams=array())
	{
		return true;
	}
	
	public function uninstall()
	{
		return true;
	}
	
	
	public function login($params=array())
	{
		$ss = $this->getSession();
		if (!$ss)
			return false;
					
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
		if (!$ss)
			return false;
		
		
		$ss->logout();
		
		setMsg("str_user_logout_ok");		
	}
	
	
	/* ========================================================
	 * run functions
	 * =======================================================*/
	protected function setRunApp($aname)
	{
		if (!$aname)
			return false;
			
		$this->_aname = $aname;
		$this->_rundir = RPATH_APPS.DS.$aname;
		
		return true;
	}
	
	/**
	 * initAppComponent
	 *
	 * @param mixed $app This is a description
	 * @return mixed This is the return value description
	 *
	 */
	protected function initAppComponent(&$ioparams=array())
	{
		$menus = $this->getMenus();
		
		$aname = '';
		$cname = '';
		$tname = '';		
		$oname = "";
		
		$vpath = $ioparams['vpath'];
		
		//rlog(RC_LOG_DEBUG, __FILE__,__LINE__, $vpath);
		
		$newvpath = array();
		
		//试着探测是否为:/<APP>/<COMPONENT>/<TASK>
		$i = 0;
		$isapi = false;
		$cinfo = array();
		
		foreach ($vpath as $val) {
			$val = trim($val);
			if ($val === "") // "0"?
				continue;
			if ($i++ < 3) {
				if (!$cname) { //是组件
					if (isset($menus[$val])) {
						$cname = $val;
						$m = $menus[$val];
						if (isset($m['app']) && $this->isApp($m['app']) ) {
							$aname = $m['app'];
						}
						continue;	
					}
					if ($this->isComponent($val)) { //是组件
						$cname = $val;
						continue;
					}									
				} 
				
				if ($val == $this->_name)
					continue;
				
				//识别 APP
				if (!$aname && $this->isApp($val)) {
					$aname = $val;
					continue;
				} 
				
				if (!$tname && $this->isApi($val)) {
					$tname = $val;
					$isapi = true;
					continue;
				} 
			}
			$newvpath[] = $val;			
		}
		
		!$aname && $aname = $ioparams['aname'];
		!$cname && $cname = $ioparams['cname'];
		!$tname && $tname = $ioparams['tname'];	
		!$oname && $oname = $ioparams['oname'];	
				
		$defComponentName = $this->getDefaultComponent();
		!$cname && $cname =  $defComponentName;		
		if ($this->isComponent($cname)) 
			$aname = $this->_name;
		if (!$aname) {
			if (isset($menus[$cname])) {
				$appname = $menus[$cname]['app'];
				if ($appname != $this->_name)
					$aname = $appname;
			}
		}			
		
		//API
		if ($isapi) {
			$cname = $this->_apidb[$tname]['cname'];
			$aname = $this->_apidb[$tname]['aname'];
		} 
				
		$ioparams['aname'] = $aname;
		$ioparams['cname'] = $cname;
		$ioparams['tname'] = $tname;
		$ioparams['oname'] = $oname;
		$ioparams['vpath'] = $newvpath;
		
		$ioparams['component'] = $cname;		
		$ioparams['task'] = $tname;	
		$ioparams['_base'] = $ioparams['_basename'].'/'.$cname;
		$ioparams['_baseuri'] = $ioparams['_base'].'/'.$tname;
		
		if (isset($menus[$cname])) { //组件信息
			$ioparams['componentinfo'] = $menus[$cname];	
		} else {
			$ioparams['componentinfo'] = array();	
		}
		
		//rlog($ioparams);
		
		$this->setRunApp($aname);
		
		return true;
	}
		
	protected function initAppTemplate(&$ioparams=array())
	{
		$cf = get_config();		
		$tplname = !empty($cf['tplname'])?$cf['tplname']:'default';
		
		$ioparams['tdir'] = $this->_rundir.DS.'templates'.DS.$tplname;;	
		$ioparams['app_tdir'] = $this->_appdir.DS.'templates'.DS.'default';		
		//$ioparams['system_tdir'] = RPATH_TEMPLATES.DS.'default';	
	}
	
	
	protected function initAppI18n(&$ioparams=array())
	{
		if (isset($ioparams['lang'])) {
			$this->_lang = $ioparams['lang'];
		} else {
			$cf = get_config();
			$lang = $cf['lang'];
			$this->_lang = $lang;
			$ioparams['lang'] = $lang;
		}
	}	
	
	protected function probeModels($appname, &$mdb)
	{
		//编历目录
		$dir = RPATH_APPS.DS.$appname.DS."models";
		$udb = s_readdir($dir);
		if (!$udb)
			return false;
		
		foreach ($udb as $key=>$v) {
			$modname = $v;
			$extname = s_extname($modname);
			$mdb[$modname] = array('appname'=>$appname, 'modname'=>$modname, 'modpath'=>$dir.DS.$v);
		}
	}
	
	
	protected function cacheModels()
	{
		$apps = Factory::GetApps();
		$mdb = array();
		foreach ($apps as $key=>$v) {
			$this->probeModels($key, $mdb);			
		}
		
		$this->probeModels($this->_name, $mdb);
		cache_array('models', $mdb);
	}
	
	protected function initModels()
	{
		if (!file_exists(RPATH_CACHE.DS.'models.php')) 
			$this->cacheModels();		
	}
	
		
	protected function init(&$ioparams=array())
	{
		$this->initModels();
		
		$this->initSession();
		
		$cf = get_config();	
		//语言
		$this->initAppI18n($ioparams);
						
		$this->initAppComponent($ioparams);		
		//模板
		$this->initAppTemplate($ioparams);		
		
		$ioparams['appdir'] = $this->_appdir;
		$ioparams['rundir'] = $this->_rundir;
				
		//logo
		$logo = !empty($cf['logo'])?$cf['logo']:$ioparams['_dstroot'].'/img/logo-default.png';
		
		$ioparams['_logo'] = $logo;
						
		$ioparams['_appname'] = $this->_name;
		$ioparams['_lang'] = $this->_lang;
		$ioparams['_tplname'] = $this->_tplname;
		$ioparams['_thename'] = $cf['thename'];
		
		$ioparams['_layout'] = $cf['layout'];
		$ioparams['_layout_container'] = $cf['layout']=='boxed'?'container':'';
		$ioparams['_enable_simple_layout'] = $cf['enable_simple_layout'];
		
		//dataurl
		if (isset($cf['datauri']) && $cf['datauri'])
			$dataurl = $ioparams['_rooturl'].$cf['datauri'];
		else 
			$dataurl = $ioparams['_weburl'].'/data';						
		$ioparams['dataurl'] = $dataurl;		
		
		if (isset($cf['datadir']) && $cf['datadir'])
			$datadir = $cf['datadir'];
		else 
			$datadir = RPATH_DATA;						
		$ioparams['datadir'] = $datadir;
		
				
		//system variables
		$appcfg = $this->getAppCfg();	
		$ioparams['sys_name'] = $this->getSysName();
		$ioparams['sys_version'] = $this->getSysVersion();
		$ioparams['sys_lang'] = $cf['langname'];
		$ioparams['sys_title']  = isset($cf['title'])? $cf['title'] : i18n('str_system_title', $appcfg['description']);
		$ioparams['sys_app_title'] = i18n($ioparams['_appname']);
		$ioparams['sys_component_name'] = i18n('menu_'.$ioparams['cname']);
		
		//$sys_component_name
		
		$ioparams['sys_copyright_corp'] = i18n('str_system_copyright_corp');
		$ioparams['sys_current_year'] = tformat_current("Y");
		$ioparams['sys_current_date'] = tformat_current("Y-m-d");
		$ioparams['sys_current_time'] = tformat_current("H:i:s");	
		
		$ioparams['sys_copyright'] = i18n($appcfg['copyright']);
		$ioparams['sys_description'] = i18n($appcfg['description']);
		$ioparams['sys_website'] = $appcfg['website'];
		
		//rlog($ioparams);exit;
		
		return true;		
	}


	/**
	 * 任务分配, 处理选项
	 *
	 * @return mixed This is the return value description
	 *
	 */
	protected function dispatch(&$ioparams=array())
	{
		return false;
	}

	
	/**
	 * 呈现，渲染
	 *
	 * @return mixed This is the return value description
	 *
	 */
	protected function render(&$ioparams = array())
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, $ioparams);
		$menus = $this->getMenus();
		$name = $ioparams['component'];
		$params = $menus[$name];
		$params['appname'] = $this->_name;
		$params['appdir'] = $this->_rundir;
		
		rlog(RC_LOG_DEBUG, __CLASS__, __FUNCTION__, __LINE__,  "{$ioparams['method']} {$ioparams['_uri']} | REQU: aname=".$ioparams['aname'].",cname=".$ioparams['cname'].',tname='.$ioparams['tname']);
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $params);
		
		$com = Factory::GetComponent($name, $params);
		
		$this->_activeComponent = $com;
		$data = $com->render($ioparams);
		
		echo $data;
	}


	//////////////////////////////////////////// app public methods /////////////////////////////////
	public function run($options=array())
	{
		//session_start会使响应头加入： 
		//Cache-Control	no-store, no-cache, must-reval…te, post-check=0, pre-check=0
		//session_cache_limiter控制不输出响应头
		session_cache_limiter( "private, must-revalidate" ); 
		session_start();
		
		if (!is_array($options))
			$options = array();
		
		$r = Factory::GetRequest();
		$r->getRequestParams($ioparams);		
		$ioparams = array_merge($ioparams, $options);
		
		
		$this->init($ioparams);
		$this->dispatch($ioparams);					
		$this->render($ioparams);
		
		return true;
	}

	
}