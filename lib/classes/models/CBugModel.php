<?php

/**
 * @file
 *
 * @brief 
 * 
 * BUG Ä£ÐÍ
 *
 */

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class CBugModel extends CTableModel
{
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);
	}
	
	public function CBugModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}	
	
	
	protected function _initFieldEx(&$f)
	{
		switch ($f['name']) {
			case 'attachs':	
				$f['input_type'] = 'gallery';
				break;
			case 'resolution':	
			case 'severity':			
			case 'priority':
			case 'status':
				$f['input_type'] = 'selector';
				$f['searchable'] = 'true';
				break;
			case 'cuid':
				$f['readonly'] = true;
			case 'uid':
				$f['input_type'] = 'UID';	
				break;
			case 'ctime':
				$f['readonly'] = true;
			case 'ts':
				$f['input_type'] = 'datetime';	
				$f['edit'] = false;
				break;	
			default:
				break;
		}
		
		return true;
	}
	
}
