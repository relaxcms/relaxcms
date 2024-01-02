<?php

defined( 'RMAGIC' ) or die( 'Restricted access' );

class CPubComponent extends CDTFileComponent
{
	function __construct($name, $options)
	{
		parent::__construct($name, $options);
	}
	
	function CPubComponent($name, $options)
	{
		$this->__construct($name, $options);
	}
	
	protected function show(&$ioparams=array())
	{
		$this->enableMenuItem('pub');
		$res = parent::show($ioparams);

		return $res;
	}
}