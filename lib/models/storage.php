<?php

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class StorageModel extends CStorageModel
{
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);
	}
		
	public function StorageModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}	
	protected function _init()
	{
		parent::_init();
		$this->_default_sort_field_mode = 'asc';
	}
}

