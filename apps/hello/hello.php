<?php

class HelloApplication extends CMainApplication
{
	public function __construct($name, $options=null)
	{
		parent::__construct($name, $options);
	}
		
	public function HelloApplication($name, $options=null)
	{
		$this->__construct($name, $options);
	}
}