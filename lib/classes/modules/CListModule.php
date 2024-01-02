<?php

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class CListModule extends CModule
{
	function __construct($name, $attribs)
	{
		parent::__construct($name, $attribs);
	}
	
	function CListModule($name, $attribs)
	{
		$this->__construct($name, $attribs);
	}
	
	public function show(&$ioparams=array())
	{
		$res = parent::show($ioparams);
		
		$flags = isset($this->_attribs['flags'])?intval($this->_attribs['flags']):0;
		$num = isset($this->_attribs['num'])?intval($this->_attribs['num']):12;
		$cid = isset($this->_attribs['cid'])?intval($this->_attribs['cid']):0;
		$mid = isset($this->_attribs['mid'])?$this->_attribs['mid']:'';
			
	}	
}