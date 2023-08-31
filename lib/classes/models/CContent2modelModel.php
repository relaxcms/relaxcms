<?php

defined('RPATH_BASE') or die();
class CContent2modelModel extends CTableModel
{
	public function __construct($name, $options=null)
	{
		parent::__construct($name, $options);
	}
	
	public function CContent2modelModel($name, $options=null)
	{
		$this->__construct($name, $options);
	}

	protected function _init_field(&$f)
	{
		switch ($f['name']) {
			case 'cid':
				$f['input_type'] = 'CONTENT';
				break;
			case 'modname':
				$f['disable'] = true;
				break;
			case 'mid':
				$f['disable'] = true;
				break;
			default:
				break;
		}
		return true;
	}
	
	
}