<?php

/**
 * @file
 *
 * @brief 
 * 
 * ������ģ��
 *
 */

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class CDataModel extends CModel
{
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);
	}
	
	public function CDataModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}
		
	protected function _init()
	{
		$this->_initDB();
		return false;
	}	
	
	public function get($id)
	{
		return false;
	}
	
	public function findOne($params, $sort=array())
	{
		return false;
	}
	
	public function getCount($params=array())
	{
		return 0;
	}
	
	public function group($params=array())
	{
		return false;
	}
	
	
	protected function add(&$params=array(), &$ioparams=array())
	{
		return false;
	}
	
	protected function edit(&$params=array(), &$ioparams=array())
	{
		return false;
	}
	
	protected function delete($params=array())
	{
		return false;
	}
	
	/**
	 * ����
	 * �кܴ��ƻ��飬���ã�
	 *
	 * @return mixed This is the return value description
	 *
	 */
	public function truncate()
	{
		return false;	
	}
}
