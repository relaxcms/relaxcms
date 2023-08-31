<?php

class CMenu 
{
	protected $_name;
	protected $_mfile = '';
	
	protected $_menus = array();
	
		
	/**
	 * 当前登录用户
	 *
	 * @var mixed 
	 *
	 */
	protected $_current_user_menus;
	
	protected $_app;
	protected $_langname;
	protected $_cachedir;
	
	public function __construct($app=null)
	{
		if (!$app)
			$app = Factory::GetApp();
		
		$this->_app = $app;
		$this->_cachedir = $app->getAppCacheDir();
		$this->_langname = $app->getLangName();
		
	}
	
	public function CMenu($app=null)
	{
		$this->__construct($app);
	}		
	
	static function &GetInstance($app=null)
	{
		
		static $instances;
		
		if (!isset( $instances )) {
			$instances = array();
		}
		$sig = serialize($app);
		if (empty($instances[$sig])) {	
			$instance = new CMenu($app);
			$instances[$sig] =&$instance;
		}		
		return $instances[$sig];
	}

	
	public function cacheMenus()
	{
		return true;
	}

	

	
	
	/**
	 * 返回顶层菜单
	 *
	 */
	protected function _getTopMenus($menus)
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
	 * 返回子菜单
	 *
	 */
	protected function _getSubMenus($menus, $mkey)
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
			$mdb = $this->_getTopMenus($menus);
		} else if ($pkey == 'all') {
			$mdb = $menus;
		} else {
			$mdb = $this->_getSubMenus($menus, $pkey);
		}
		$user = Factory::GetApp()->getUser();
		$is_super = $user->isSuper();
		
		
		//过滤
		foreach ($mdb as $key=>$v) {
			$name = $v['name'];
			$pid = $v['pid'];
			if (isset($v['hidden']) && $v['hidden'] == true)
				continue;

			if ($ifexclude && isset($v['is_exclude']) && $v['is_exclude'] == true)
				continue;				
			
			if (!$pid || $is_super) {
				$_mdb[$key] = $v;
			} else {
				if ($user->hasPrivilegeOf($pid, 0)) //菜单项不需权限
					$_mdb[$key] = $v;
			}		
		}
		return $_mdb;		
	}
	
	/**
	 * 读取可显
	 *
	 * @return mixed This is the return value description
	 *
	 */
	public function getMenus()
	{
		return $this->_menus;
	}
	
	public function getCurrentMenus($mkey='', $ifexclude=false)
	{
		$menus = $this->getMenus();
		$menus = $this->filterMenus($menus, $mkey, $ifexclude);	
		return $menus;		
	}
	
	public function getNav($name='top')
	{
		$menus = $this->getCurrentMenus('all');
		$mdb = array();
		foreach ($menus as $k=>$v) {
			if (strstr($v['pos'], $name)) {
				$mdb[$k] = $v;
			}			
		}		
		return $mdb;		
	}
	
	public function getAppNav($pos='top', $desc=true)
	{
		!$pos && $pos = 'top';
		$menus = $this->getNav($pos);
		//排序
		array_sort_by_field($menus, "mid", $desc);				
		return $menus;
	}
	
	public function getSubMenus($pkey, $ifexclude=false)
	{
		$m = $this->_menus[$pkey];
		if (!$m)
			return array();
		if ($m['parent'])
			$pkey = $m['parent'];			
		return $this->getCurrentMenus($pkey, $ifexclude);		
	}
		
	public function isPublicItem($component, $tname)
	{
		$menus = $this->getMenus();
		if (!isset($menus[$component]))
			return false;
		$m = $menus[$component];
		if (!$m)
			return false;
		if (!$m['pid'])		
			return true;
	
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, 'component='.$component, 'tname='.$tname, $m);		
		if ($tname && isset($m['task']) && isset($m['task'][$tname]) && $m['task'][$tname] === 'i') {
			//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "open task '$tname'!!!!!!");
			return true;
		}
		return false;
	}
	
	public function isPrivilegeItem($component)
	{
		$menus = $this->getMenus();
		$m = $menus[$component];
		if ($m['pid'])		
			return true;			
		return false;		
	}
	
	
	
	
	public function switchIfTop($component, $tname='')
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "IN component=".$component);
		
		$foundCom = "";
		$m = array();
		if (isset($this->_menus[$component]))
			$m = $this->_menus[$component];
		if (!$m || $m['parent'] || ($m['task'] && $m['task'][$tname]))
			return $component;
		foreach($this->_menus as $k=>$v) {
			if ($v['parent'] == $component) {
				if ($v['hidden'])
					continue;
				if (!$this->hasPrivilegeOf($v['component'])) 
					continue;
				if (!$foundCom)
					$foundCom = $v['component'];
				if ( $v['is_default']) {
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
	
	
	
	public function getParentPid($pid)
	{
		$parent = '';
		foreach ($this->_menus as $key=>$v) {
			if ($v['pid'] == $pid) {
				$parent = $v['parent'];
				break;
			}
		}
		
		if (!$parent)
			return 0;
		if (!isset($this->_menus[$parent]))
			return 0;
		return $this->_menus[$parent]['pid'];
	}
	
	public function getCurrentMenuTree($activeItem='', $options=array())
	{
		
		$menus = $this->getMenus();
		$menus = $this->filterMenus($menus, '');	
		
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
			//子菜单
			$submenus =  $this->getSubMenus($key, true);
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
	
	public function getPids()	
	{
		$pids = array();		
		$menus = $this->_menus;
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

			$pids [$pid] = array('pid'=>$pid, 'permision'=>$permid, 'level'=>$level);		
			//parent
			if ($v['parent']) {
				if (isset($menus[$v['parent']])) {
					$ppid = $menus[$v['parent']]['pid'];
					if (!isset($pids[$ppid])) {
						rlog($pids);
					}
					$pids [$pid]['parent'] = $pids[$ppid];
				}
			} 	
		}
		return $pids;	
	}
} 