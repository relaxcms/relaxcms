<?php

class CDTFileComponent extends CFileDTComponent
{
	function __construct($name, $options)
	{
		parent::__construct($name, $options);
	}
	
	function CDTFileComponent($name, $options)
	{
		$this->__construct($name, $options);
	}		
	
	protected function show(&$ioparams=array())
	{
		parent::show($ioparams);
		$this->setTpl('dt_show');
	}
}