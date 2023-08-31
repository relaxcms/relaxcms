<?php

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

/**
 * CComponent
 * 
 * 组件基类
 *
 */
class CComponent  extends CObject
{
	protected $_name;				
	protected $_options=array();

	/** 默认模型　*/
	protected $_modname = null;
	
	/**
	 * 方法
	 *
	 * @var mixed 
	 *
	 */
	protected $_task = '';
	
	
	/**
	 * 输出变量，模板直接使用
	 *
	 * @var mixed 
	 *
	 */
	protected $_var = array();
	
	/**
	 * ID标识
	 *
	 * @var mixed 
	 *
	 */
	protected $_id;
		
	public function __construct($name, $options=array())
	{
		$this->_name = $name;		
		$this->_modname = $name;		
		$this->_options = $options;
		
		$this->_init();			
	}
	
	public function CComponent($name=null, $options=array())
	{
		$this->__construct($name, $options);
	}
	
	
	protected function _initModel()
	{
		if (isset($this->_options['modname'])) {
			$modname = $this->_options['modname'];
		} else {
			$pos = strpos($this->_name, 'com_'); //组件名称去除前缀
			if ($pos !== false) {
				$modname = substr($this->_name, $pos + 4);	
			}	
		}
		if (isset($modname))
			$this->_modname = $modname;	
			
	}
	
	protected function _init()
	{
		$this->_initModel();			
		return true;
	}
		
	//创建
	static function &GetInstance($name, $options=array())
	{
		static $instances;
		
		if (!isset( $instances )) 
			$instances = array();
		
		$sig = md5($name.serialize($options));		
		if (empty($instances[$sig])) {
			if (isset($options['appdir']))  {
				$appname = $options['appname'];
				$classfile  = $options['appdir'].DS.'components'.DS.$name.'.php';				
				if (file_exists($classfile)) {
					require_once($classfile);		
				} else {
					//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "no classfile '$classfile'!");
					$classfile = RPATH_APPCOMPONENTS.DS.$name.'.php';
					if (file_exists($classfile)) {
						require_once($classfile);
					} else {
						//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "no classfile '$classfile'!");
						$classfile = RPATH_COMPONENTS.DS.$name.'.php';
						if (file_exists($classfile)) {
							require_once($classfile);
						} else {
							//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "no classfile '$classfile'!");
						}
					}
				}
			} else {				
				$appname = APPNAME;
				$classfile = RPATH_COMPONENTS.DS.$name.'.php';
				if (file_exists($classfile))
					require_once($classfile);
				
			}
			
			
			$class = "";
			$arrs = explode('_', $name);
			
			foreach($arrs as $key=>$v) {
				$class .= ucfirst($v);
			}
			$class = $class.'Component';			
			if (!class_exists($class)) {
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "no class '$class' use default 'CComponent'!");
				$class = 'CComponent';				
			} 
			
			$options['class'] = $class;
			$options['classfile'] = $classfile;
			$options['appname'] = $appname;
			
			$instance	= new $class($name, $options);		
			$instances[$sig] = $instance;
		}
		
		return $instances[$sig];
	}
	
	
	
	
	/* ==============================
	 * Utility Helper functions
	 * =============================*/
	protected function getModel()
	{
		$modname = $this->request('modname', $this->_modname);
		return Factory::GetModel($modname);
	}
	
	protected function setTpl($tpl)
	{
		$this->_tpl = $tpl;
	}
	protected function resetTpl()
	{
		$this->_tpl = $this->_name;
	}
	
	
	protected function getTpl($tpl)
	{
		return $this->_tpl;
	}
	
	protected function setActiveTab($nr, $force_active_id=-1, $selector='')
	{
		$tabs = initActiveTab($nr, $force_active_id);
		foreach ($tabs as $key => $v) {
			$this->assign('navtab'.$v['id'], $v);
		}		
		
		if ($selector) {
			$sdb = get_i18n($selector);
			foreach ($tabs as $key => &$v) {
				if (isset($sdb[$v['id']]))
					$v['title'] = $sdb[$v['id']];
			}
		}
		
		$this->assign('navtabs', $tabs);
		
		return $tabs;
	}
	
	public function getActiveTab()
	{
		return $this->_var['navtabs'];
	}
	
	/* ==============================
	 * Params Helper functions
	 * =============================*/
	protected function assign($key, $v=null)
	{
		$old = $this->_var[$key];
		if ($v !== null)		
			$this->_var[$key] = $v;	
			
		return $old;
	}
	
	public function assigns($av)
	{
		foreach ($av as $k=>$v)
			$this->assign($k, $v);
	}
	
	protected function assignArray($arr)
	{
		if (!is_array($arr)) 
			return false;
		
		foreach($arr as $key=>$v){
			$this->_var[$key] = $v;
		}
	}
	
	protected function assignSession($key, $v)
	{
		$this->assign($key, $v);	
		$_SESSION[$key] = $v; 
	}
	
	
	protected function assignSelectEnable($name, $val=0)
	{
		$this->assign($name.'_select', get_common_select('enable', $val));
	}
	
			
	protected function setParams($params=array())
	{
		$this->assign("params", $params);
	}
	
	protected function getParams(&$params=array())
	{
		$params = get_var("params", array());
		if (!$this->checkParams($params)) {
			$this->setParams($params);
			return $params;
		}			
		return $params;
	}
		
	protected function checkParams(&$params) 
	{
		return true;
	}
	
	protected function request($key, $default='')
	{
		$val = get_var($key, $default);
		$this->_var[$key] = $val;
		return $val;
	}
	
	protected function requestInt($key, $default=0)
	{
		$v = $this->request($key, $default);
		return intval($v);
	}
	
	protected function requestBool($key, $default=true)
	{
		$v = $this->request($key, $default?'true':'false');
		return $v == 'true' || $v === true;
	}	
		
	protected function get_int($key, $default=0)
	{
		return $this->requestInt($key, $default);
	}
		
	protected function get_bool($key, $default=true)
	{
		return $this->requestBool($key, $default);
	}	
	
	
	
	
	/* ==============================
	 * task functions
	 * =============================*/
	protected function show(&$ioparams=array())
	{		
		$this->_tpl = $this->_name.'_show';
		return false;
	}
	
	protected function ajaxSystemTime(&$ioparams=array())
	{
		showStatus(0,array('systime'=>tformat_cstdatetime(time())));
	}
	
	protected function ajaxLunar(&$ioparams=array())
	{
		$lunar = Factory::GetLunar();		
		$data = $lunar->getNowForView();		
		showStatus(0, $data);
	}
	
	
	protected function add(&$ioparams=array())
	{
		$this->_tpl = $this->_name.'_add';
		return false;
	}
	
	
	protected function edit(&$ioparams=array())
	{
		$this->_tpl = $this->_name.'_edit';
		return false;
	}
	
	protected function detail(&$ioparams=array())
	{
		$this->_tpl = $this->_name.'_detail';
		return false;
	}

	protected function del(&$ioparams=array())
	{
		return false;
	}
	
	protected function hasPrivilegeOf(&$ioparams=array())
	{
		if (!hasPrivilegeOf($this->_name, $ioparams['tname'])) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "no privilege of '{$this->_name} > {$ioparams['tname']}'!");
			return false;
		}
		
		return true;		
	}
	
	protected function noprivilege(&$ioparams=array())
	{
		rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no privilege!!");
		header("HTTP/1.1 403 No Permistion!");
		$this->setTpl('403');
		return false;	
	}

	protected $_vpath_args_pos = 0;		
	protected function initTask(&$ioparams=array())
	{
		
		//$tname
		$tname = isset($ioparams['tname'])?$ioparams['tname']:'';
		if ($tname && method_exists($this, $tname)) 
			return $tname;			
	
		if (isset($ioparams['vpath'])) {
			$nr = count($ioparams['vpath']);
			for($i=$nr-1; $i>=0; $i--) {
				$tname = $ioparams['vpath'][$i];
				if (method_exists($this, $tname)) {
					$ioparams['vpath_offset'] = $i+1;
					return $tname;				
				}					
			}			
		}
		
		$tname = $this->_task?$this->_task:'show';
		if (method_exists($this, $tname)) 
			return $tname;
		
						
		return 'show';	
	}
	
	protected function initI18n(&$ioparams=array())
	{
		//! 默认语言包名称
		if (isset($_COOKIE['lang']))
			$ioparams['_lang'] = $_COOKIE['lang'];
		!isset($ioparams['_lang']) && $ioparams['_lang'] = 'zh_CN';
	}
		
	protected function init(&$ioparams=array())
	{
		//component name
		$name = $this->_name;
		
		//task
		$tname = $this->initTask($ioparams);
		
		//i18n
		$this->initI18n($ioparams);
				
		//鉴权
		$ioparams['tname'] = $tname;
		if (!$this->hasPrivilegeOf($ioparams)) {
			$tname = 'noprivilege';	
		}
		$ioparams['task'] = $tname;
		$this->_task = $tname;
		
		//id
		if (isset($ioparams['id'])) {
			$this->_id = $ioparams['id'];
		} else {
			$id = $this->requestInt('id');
			$this->_id = $id;			
			$ioparams['id'] = $id;
		}
		
		//rlog($ioparams);
		return true;
	}
	
	protected function fini(&$ioparams=array())
	{
		return false;
	}
	
	
	protected function get_id()
	{
		return $this->_id;
	}
	
	protected function probID($ioparams)
	{
		$id = $this->_id;		
		if (!$id) {
			foreach ($ioparams['vpath'] as $key=>$v) {
				if (is_numeric($v)) {
					$id = intval($v);
					break;
				}
			}			
		}		
		return $id;			
	}
	
	protected function run(&$ioparams=array())
	{
		$task = $ioparams['task'];
		$res = $this->$task($ioparams);
		return $res;
	}
			
	/**
	 * UI 呈现
	 *
	 * @param mixed $params This is a description
	 * @return mixed This is the return value description
	 *
	 */
	public function render(&$ioparams=array())
	{
		$this->init($ioparams);
		$res = $this->run($ioparams);
		$this->fini($ioparams);
		
		return $res;
	}
}