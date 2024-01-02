<?php
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );


class CParamsModel extends CDataModel
{
	protected $_paramsfile;
	protected $_params = array();  
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);
		$this->_paramsfile = RPATH_CONFIG.DS.'params_'.$name.'.php';
	}
	
	public function CParamsModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}
	
	protected function initDefaultParams(&$params=array())
	{
		return $params;				
	}
	
	protected function initParams()
	{
		$file = $this->_paramsfile;
		if (is_file($file)) {
			require $file;
		} 
		$this->initDefaultParams($params);
		
		$this->_params = $params;	
	}
	
	public function get($id=0)
	{
		if (!$this->_params) 
			$this->initParams();
		return $this->_params;
	}
	
	
	public function set(&$params=array(), &$ioparams=array())
	{
		return cache_array('params', $params, $this->_paramsfile);
	}	
	
	public function getParams($params=array())
	{
		return $this->get(0);
	}
	public function getConfig($params=array())
	{
		return $this->get(0);
	}
	
	public function setParams(&$params=array())
	{
		return $this->set($params);
	}

	public function reset(&$params=array())
	{
		@unlink($this->_paramsfile);
		return true;
	}	
}
