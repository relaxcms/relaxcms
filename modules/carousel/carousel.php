<?php
/**
 * @file
 *
 * @brief 
 * 播放大图
 *
 */
class CarouselModule extends CContentModule
{
	function __construct($name, $attribs)
	{
		parent::__construct($name, $attribs);
	}
	
	function CarouselModule($name, $attribs)
	{
		$this->__construct($name, $attribs);
	}
			
	public function show(&$ioparams = array())
	{			
		$udb = parent::show($ioparams);
		
		$id = isset($this->_attribs["id"])? $this->_attribs["id"]:'iflash_'.rand();
		$width = isset($this->_attribs['width'])?intval($this->_attribs['width']):280;
		$height = isset($this->_attribs['height'])?intval($this->_attribs['height']):455;
		
		$show_num = isset($this->_attribs['show_num'])?true:false;
		
		$this->_attribs['width'] = $width;
		//$this->_attribs['height'] = $height;
		
		//图片高度
		$iheight = $height - 26;
		
		if ($show_num) {
			$this->_attribs['show_num'] = 'false';
			$iheight = $height;		
		} else {
			$this->_attribs['show_num'] = 'true';
		}
		
		$this->_attribs['iheight'] = $iheight;
		$this->_attribs['id'] = $id;		
		
		$nr = 0;
		$_udb = array();
		foreach($udb as $key=>$v) {
			if ($nr == 0) 
				$v['active'] = 'active';
			else
				$v['active'] = '';
			
			$_udb[] = $v;	
			$nr ++;
		}
		
				
		$this->set_var('udb', $_udb);
		$this->set_var('nr_item', $nr);
		$this->set_var('height', $height);
		
		return true;		
	}
	
}