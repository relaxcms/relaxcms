<?php

/**
 * @file
 *
 * @brief 
 * 开放认证
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class COAuth
{
	protected $_name = null;
	protected $_options = null;
		
	//清求Token的URL
	protected $_token_request_url = '';
	
	//清求UserInfo 的URL
	protected $_userinfo_request_url = '';
	
	public function __construct($name, $options=array())
	{
		$this->_name = $name;
		$this->_options = $options;
			
		$this->_init();	
	}
	
	public function COAuth($name, $options=array())
	{
		$this->__construct($name, $options);
	}
	
	protected function _init()
	{
		//notfiyurl
		if (!empty($this->_options['notifyurl']))
			$this->_options['notifyurl'] .= '/'.$this->_options['id'];

				
	}
		
	static function &GetInstance($name, $options=array())
	{
		static $instances;
		
		if (!isset( $instances )) {
			$instances = array();
		}
		
		$sig = serialize($name);		
		if (empty($instances[$sig])) {	
			require_once(RPATH_OAUTH.DS.$name.'.php');
			$class = ucfirst($name)."OAuth";
			if(!class_exists($class)) {
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "no class '$class'");
				return null;
			}
			
			$instance	= new $class($name, $options);
			$instances[$sig] =&$instance;
		}
		
		return $instances[$sig];
	}

	public function getDefaultIcon($ioparams=array())
	{
		return '<i class="fa fa-money"></i>';
	}

	public function getDefaultBgColor($ioparams=array())
	{
		return 'btn-default';
	}

	
	public function getConfigInfo(&$params=array())
	{
		return $params;
	}

	public function getUserInfo($params=array())
	{
		return false;
	}	

	public function payOrder($orderinfo, &$ioparams=array())
	{
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "NOT IMPLEMENT!");
		return false;
	}


	public function checkPayNotify(&$params)
	{
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "NOT IMPLEMENT!");
		return false;
	}
}