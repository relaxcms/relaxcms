<?php

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class AnimatedModule extends CContentModule
{
	function __construct($name, $attribs)
	{
		parent::__construct($name, $attribs);
	}
	
	function AnimatedModule($name, $attribs)
	{
		$this->__construct($name, $attribs);
	}
	
	public function show(&$ioparams=array())
	{
		$udb = parent::show($ioparams);
		
		$nr = count($udb);
		
		return $udb;
		
	}	

	protected function getCols()
	{
		return isset($this->_attribs['cols'])?intval($this->_attribs['cols']):4;		
	}
}