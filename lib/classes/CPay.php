<?php

/**
 * @file
 *
 * @brief 
 * 开放认证
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class CPay
{
	protected $_name = null;
	protected $_options;
	
	public function __construct($name, $options=array())
	{
		$this->_name = $name;	
		$this->_options = $options;	
	}
	
	public function CPay($name, $options=array())
	{
		$this->__construct($name, $options);
	}
	

	static function &GetInstance($name, $options=array())
	{
		static $instances;
		
		if (!isset( $instances )) {
			$instances = array();
		}
		
		$sig = serialize($name);		
		if (empty($instances[$sig])) {	
			require_once(RPATH_PAY.DS.$name.'.php');
			$class = ucfirst($name)."Pay";
			if(!class_exists($class)) {
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "no class '$class'");
				return null;
			}
			
			$instance	= new $class($name, $options);
			$instances[$sig] =&$instance;
		}
		
		return $instances[$sig];
	}
	
	public function getDefaultLogo($ioparams=array())
	{
		return '';
	}

	public function getDefaultIcon($ioparams=array())
	{
		return '<i class="fa fa-money"></i>';
	}

	public function getDefaultBgColor($ioparams=array())
	{
		return 'btn-default';
	}
}