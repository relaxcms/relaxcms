<?php

/**
 * @file
 *
 * @brief 
 * 
 * 访问历史模型
 *
 */

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );
class HistoryModel extends CHistoryModel
{
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);		
	}
		
	public function HistoryModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}
}