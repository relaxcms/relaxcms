<?php

defined('RPATH_BASE') or die();

class IplayerModule extends CContentModule
{
	function __construct($name, $attribs)
	{
		parent::__construct($name, $attribs);
	}
	
	function IplayerModule($name, $attribs)
	{
		$this->__construct($name, $attribs);
	}
	
	public function show(&$ioparams=array())
	{
		$rows = parent::show($ioparams);
		
		$id = isset($this->_attribs["id"])? $this->_attribs["id"]:'iflash_'.rand();
		$width = isset($this->_attribs['width'])?intval($this->_attribs['width']):280;
		$height = isset($this->_attribs['height'])?intval($this->_attribs['height']):190;
				
		$show_num = isset($this->_attribs['show_num'])?true:false;
		
		$this->_attribs['width'] = $width;
		$this->_attribs['height'] = $height;
		
		//ͼƬ�߶�
		$iheight = $height - 26;
		
		if ($show_num) {
			$this->_attribs['show_num'] = 'false';
			$iheight = $height;		
		} else {
			$this->_attribs['show_num'] = 'true';
		}
				
		$this->_attribs['iheight'] = $iheight;
		$this->_attribs['id'] = $id;		
		
	}	
}

?>