<?php
/**
 * @file
 *
 * @brief 
 * 模块类
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class CModule
{
	protected $_name;
	protected $_task;
	protected $_attribs;
	protected $_template;
	protected $_tdir;
	
	protected $_cssdb = array();
	protected $_jsdb = array();
		
	public function __construct($name, $options)
	{
		$this->_name = $name;
		$this->_attribs = $options;
		if (isset($options['tpl']))
			$this->_template = $options['tpl'];
		else 
			$this->_template = $options['tpl'] = $name;
		
		$this->_tdir = dirname($options['classfile']);
		
		$this->init();
	}
	
	public function CModule($name, $options)
	{
		$this->__construct($name, $options);
	}
	
	//创建
	static function &GetInstance($name, $options)
	{
		static $instances;
		
		if (!isset( $instances )) 
		{
			$instances = array();
		}
		
		$sig = serialize(array($name, $options));		
		if (empty($instances[$sig]))
		{	
			$class = "";
			$cn = explode('_', $name);
			foreach($cn as $key=>$v) {
				$class .= ucfirst($v);
			}
			$class = $class.'Module';
			
			$classfile = RPATH_MODULES.DS.$name.DS.$name.".php";
			if (file_exists($classfile)) {				
				require_once $classfile;	
			}
									
			if (!class_exists($class)) {
				$class	= "CModule";
			}
			
			$options['class'] = $class;
			$options['classfile'] = $classfile;
			
			$instance	= new $class($name, $options);
			$instances[$sig] =& $instance;
		}
		
		return $instances[$sig];
	}
	
	protected function _init()
	{
		return false;
	}
	
	protected function show(&$ioparams=array())
	{
		return false;
	}

	protected function setMessageBox($options, $level=null)
	{
		//!$options['msg_backurl'] =  $options['msg_backurl'] =  $this->_base;
		foreach($options as $key=>$v) 
			$this->_attribs[$key] = $v;
		
		$this->_template = 'messagebox';
	}

	protected function showMessageBox($msg, $backurl=null, $target="_self", $ext=null, $type="error")
	{
		$msg = i18n($msg);
		$msg_alert_types = get_i18n('msg_alert_types');
		
		$options = $msg_alert_types[$type];
		$options['msg_text'] = $msg;
		$options['msg_backurl'] =  $backurl;
		$options['msg_target' ] = $target;
		$options['msg_ext' ] = $ext;
		$options['msg_type' ] = $type;

			
		$this->setMessageBox($options);
	}


	protected function showError($msg, $backurl="", $target="_self", $ext=null)
	{
		$this->showMessageBox($msg, $backurl, $target, $ext, "error");
	}
	
	protected function loadTemplate($ioparams = array())
	{
		$i18n = get_i18n();
		$T = $i18n;
		//t
		/*if (isset($i18n['t_'.$this->_name]))
			$t = $i18n['t_'.$this->_name];	
		else
			$t = array();*/
			
		$t = $ioparams['_i18ndb'];
			
		$task = $this->_task;	
		
		if (isset($i18n['str_'.$task]))
			$str_task = $i18n['str_'.$task];
		else	
			$str_task = $task;	
		
		//IO参数
		extract($ioparams);
		
		//展开数组
		extract($this->_attribs);
		
		$tpl_filename = $this->_template.'.htm';
		$tpl_pathname = $this->_tdir.DS.$tpl_filename;
		if (!file_exists($tpl_pathname)) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "WARNING :  no tpl '$tpl_pathname' of module '{$this->_name}'!");
			$tpl_pathname = RPATH_MODULES.DS.'default'.DS.$tpl_filename;
		}
		
		$tpl = Factory::GetTemplate();		
		$cpl_file = $tpl->compileTemplate($tpl_pathname, $ioparams, $tpl_filename);
		
		$data = "";
		
		ob_start();
		require $cpl_file;
		$data = ob_get_contents();
		$data = strim_bom($data);
		
		ob_end_clean();
		return $data;
	}
	
	protected function initI18nDB(&$ioparams=array())
	{
		$i18nfile = RPATH_MODULES.DS.$this->_name.DS."i18n".DS.$ioparams['_lang'].DS."i18n.php";
		if (file_exists($i18nfile)) {
			require $i18nfile;			
			$ioparams['_i18ndb'] = array_merge($ioparams['_i18ndb'], $i18n);			
		}
		return false;
	}
	
	protected function init(&$ioparams=array())
	{
		return false;		
	}
	
	//展现
	public function render($ioparams=array())
	{
		$this->init();
		
		//加载模块 i18n
		$this->initI18nDB($ioparams);
		
		$task = $ioparams['task'];
		if (method_exists($this, $task)){
			$this->$task($ioparams);
		} else {
			$task = 'show';
			$this->show($ioparams);
		}
		$this->_task = $task;
		
		//加载模板
		$content = $this->loadTemplate($ioparams);
		
		return $content;
	}		
	
	
	//设置变量	
	public function set_var($key, $v)
	{
		$this->_attribs[$key] = $v;	
	}

	
	//设置数组
	protected function set_array($arr)
	{
		if (!is_array($arr)) 
			return false;
		
		foreach($arr as $key=>$v){
			$this->_attribs[$key] = $v;
		}
	}
	
	protected function get_var($key)
	{
		return $this->_attribs[$key];	
	}

	
	public function assign($k, $v)
	{
		$this->set_var($k, $v);
	}
	
	public function assigns($av)
	{
		foreach ($av as $k=>$v)
			$this->set_var($k, $v);
	}
	
	public function getName()
	{
		return $this->_name;
	}
	
	protected function setTpl($tname)
	{
		$this->_template = $tname;
	}
}