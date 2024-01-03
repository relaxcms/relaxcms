<?php
/**
 * @file
 *
 * @brief 
 * Copyright (c), 2023, relaxcms.com
 */

class HelloComponent extends CDTFileComponent
{
	function __construct($name, $options)
	{
		parent::__construct($name, $options);
	}
	
	function HelloComponent($name, $options)
	{
		$this->__construct($name, $options);
	}	
}