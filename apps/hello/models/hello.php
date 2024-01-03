<?php

/**
 * @file
 *
 * @brief 
 * 
 * Hello 模型
 *
 */

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );
class HelloModel extends CModel
{
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);		
	}
		
	public function HelloModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}
	
	protected function _init_field(&$f)
	{
		switch ($f['name']) {
			case 'status':
				$f['input_type'] = "selector";	
				break;
			case 'photo':
				$f['input_type'] = "image";	
				break;
			case 'video':
				$f['input_type'] = "video";	
				break;
			case 'cuid':
				$f['readonly'] = true;
			case 'uid':
				$f['input_type'] = "UID";	
				$f['show'] = false;	
				break;
			case 'ctime':
				$f['readonly'] = true;
			case 'ts':
				$f['input_type'] = "TIMESTAMP";		
				$f['show'] = false;		
				break;
			default:
				break;
		}
		
		return true;
	}
}