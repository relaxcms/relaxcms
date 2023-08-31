<?php

class CListviewComponent extends CFrontComponent
{
	function __construct($name, $options)
	{
		parent::__construct($name, $options);
	}
	
	function CListviewComponent($name, $options)
	{
		$this->__construct($name, $options);
	}
	protected function _initModel()
	{
		$this->_modname = 'content';
	}
	
}