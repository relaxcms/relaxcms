<?php
/**
 * @file
 *
 * @brief 
 * BannerModule 模块
 *
 */
class Banner2Module extends CContentModule
{
	function __construct($name, $attribs)
	{
		parent::__construct($name, $attribs);
	}
	
	function Banner2Module($name, $attribs)
	{
		$this->__construct($name, $attribs);
	}	

	public function show(&$ioparams=array()) 
	{
		$cid = isset($this->_attribs['cid'])?intval($this->_attribs['cid']):0;
		$photo = isset($this->_attribs['photo'])?$this->_attribs['photo']:'';
		
		$m = Factory::GetModel('catalog');
		$params = $m->get($cid);
		if (empty($params['photo']))
			$params['photo'] = $photo;

		$this->assign('params', $params);
	}
}