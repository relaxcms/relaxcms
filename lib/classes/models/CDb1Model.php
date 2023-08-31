<?php

/**
 * @file
 *
 * @brief 
 * 
 * DB1Ä£ÐÍ
 *
 */

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class CDb1Model extends CModel
{
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);
	}
	
	public function CDb1Model($name, $options=array())
	{
		$this->__construct($name, $options);
	}
	
	protected function getDBO($name='db0')
	{
		$db = Factory::GetDBO('db1');		
		return $db;
	}
}
