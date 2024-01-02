<?php

class CListDTComponent extends CDTComponent
{
	function __construct($name, $options)
	{
		parent::__construct($name, $options);
		$this->_default_vmask = 4;
	}
	
	function CListComponent($name, $options)
	{
		$this->__construct($name, $options);
	}
	
}