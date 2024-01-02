<?php
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class CConfig
{
	protected $_name;
	
	protected $_options;
	
	protected $_cfgfile;
	
	protected $_cfgdb = array();
	
	//构造
	public function __construct($name, $options= array())
	{
		$this->_name = $name;
		$this->_options = $options;
		
		$this->_cfgfile = RPATH_CONFIG.DS.$name.'config.php';
		
		
	}	
	
	function CConfig($name, $options= array()) 
	{
		$this->__construct($name, $options);
	}
	
	static function GetInstance($name="", $options=array())
	{
		static $instances;		
		if (!isset( $instances )) 
		{
			$instances = array();
		}
		
		$sig = serialize(array($name, $options));		
		$instance = null;		
		if (empty($instances[$sig])) {
			$cname = ucfirst($name).'Config';
			if (file_exists(RPATH_CONFIGS.DS.$name.".php")) {
				require_once RPATH_CONFIGS.DS.$name.".php";
				if (!class_exists($cname)) {
					rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "no class '$cname' use default 'CConfig");
					$cname = 'CConfig';
				}					
			} else {
				$cname = 'CConfig';
			}			
			$instance	= new $cname($name, $options);
			$instances[$sig] = $instance;
		} else {
			$instance = $instances[$sig];			
		}		
		return $instance;
	}
	
	
	public function load($reload=false)
	{
		if ($reload || !$this->_cfgdb) {
			if (file_exists($this->_cfgfile)) {
				require($this->_cfgfile);
				$this->_cfgdb = $cfgdb;
				//var_dump($this->_cfgfile);
				//var_dump($cfgdb);
			} 
		}		
		return $this->_cfgdb;
	}
	
	protected function encrypt()
	{
		//加密
		$cf = get_config();
		if ($cf['fencode'] && !is_windows())
			fencode($this->_cfgfile);	
	}
	
	public function save($cfgdb, $over=false)
	{
		if (!$over) {
			$oldcfg = $this->load();
			$cfgdb = array_merge($oldcfg, $cfgdb);		
		}
			
		$cache_data="<?php\n";
		$cache_data .= "\$cfgdb = array(\n";
		
		foreach($cfgdb as $key=>$v)
		{
			$cache_data.="\t'$key'=>\"$v\",\n";
		}
		
		$cache_data .= ");\n?>\n";
		
		if (!is_dir(RPATH_CONFIG))
			s_mkdir(RPATH_CONFIG);
			
		$res = s_write($this->_cfgfile,  $cache_data);
	
		$this->encrypt();			
					
		return $res;
	}
		
	public function get_var($key, $default="")
	{
		if (array_key_exists($key, $this->_cfgdb))
		{
			return $this->_cfgdb[$key];
		}		
		return $default;
	}
		
}
