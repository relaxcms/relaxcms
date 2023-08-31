<?php

defined( 'RMAGIC' ) or die( 'Restricted access' );

class PositionModule extends CModule
{
	function __construct($name, $attribs)
	{
		parent::__construct($name, $attribs);
	}
	
	function PositionModule($name, $attribs)
	{
		$this->___construct($name, $attribs);
	}
	
	protected function show(&$ioparams=array())
	{
		$tid = isset($this->_attribs['tid'])?intval($this->_attribs['tid']):0;
		$cid = isset($this->_attribs['cid'])?$this->_attribs['cid']:'';
		
		$content_title = '';
		if ($tid > 0) {
			$m = Factory::GetModel('content');
			$row = $m->get($tid);	
			$content_title = $row['name'];	
			$cid = $row['cid'];	
		}				
		$m2 = &Factory::GetModel('catalog');				
		$position = "";		
		if ($cid > 0) {
			$position .= $m2->postion($cid, $ioparams);		
		} else {
			$position .= "<li> <span>搜索</span>  </li> ";
		}

		if ($content_title) 
			$position .= "<li> <span>$content_title</span>  </li> ";
		
		
		$this->assign('position', $position);
		$this->assign('content_title', $content_title);
		
		return true;
	}	
}

?>