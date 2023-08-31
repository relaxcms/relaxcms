<?php

/**
 * @file
 *
 * @brief 
 * 
 * 用户帐号模型
 *
 */

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );


class User_accountModel extends CTableModel
{
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);
	}
		
	public function User_accountModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}

	protected function _initFieldEx(&$f)
	{
		parent::_initFieldEx($f);
		
		switch ($f['name']) {
			case 'type':
				$f['input_type'] = 'selector';		
				break;
			default:
				break;
		}		
		return true;
	}
}