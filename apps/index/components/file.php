<?php

defined( 'RMAGIC' ) or die( 'Restricted access' );

class FileComponent extends CFileDTComponent
{
	function __construct($name, $options)
	{
		parent::__construct($name, $options);
	}
	
	function FileComponent($name, $options)
	{
		$this->__construct($name, $options);
	}	
	
	public function show(&$ioparams=array())
	{
		$res = $this->f($ioparams);
		return $res;	
	}	
	
	protected function upload(&$ioparams=array())
	{
		$m = Factory::GetModel('file');
		$res = $m->upload($ioparams);
		showStatus($res?0:-1, $res);
	}	
	
	protected function delete(&$ioparams=array())
	{
		showStatus(-1);
	}
}