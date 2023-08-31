<?php
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );


class CManagerConfig extends CConfig
{
	//构造
	public function __construct($name, $options= array())
	{
		parent::__construct($name, $options);
	}	

	function CManagerConfig($name, $options= array()) 
	{
		$this->__construct($name, $options);
	}
	
	public function load($reload=false)
	{
		$cfg = parent::load($reload);
		
		!isset($cfg['manager']) &&  $cfg['manager'] = 'admin';
		!isset($cfg['manager_pwd']) &&  $cfg['manager_pwd'] = md5('123'.time());
		!isset($cfg['manager_email']) &&  $cfg['manager_email'] = 'admin@relaxcms.com';
		
		return $cfg;	
	}
	
	public function save($cfgdb, $over=false)
	{
		$cfgdb["manager_pwd"] = encryptPassword(trim($cfgdb["manager_pwd"]));		
		$res = parent::save($cfgdb, $over);		
		return $res;
	}
}
