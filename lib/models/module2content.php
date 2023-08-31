<?php
/**
 * @file
 *
 * @brief 
 * 
 * 模块绑定内容管理
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

define(PT_BANNER, 1);

class Module2contentModel extends CModel
{
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);
	}
		
	public function Module2contentModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}	
	
	
	protected function _init_field(&$f)
	{
		switch ($f['name']) {
			case 'flags':
				$f['input_type'] = 'varmulticheckbox';
				$f['sortable'] = true;
				$f['selector'] = 'site_content_flags';
				break;			
			default:
				break;
		}
		return true;
	}
	
	public function set(&$params=array(), &$ioparams=array())
	{
		$mid = $params['mid'];
		$res = $this->getOne(array('mid'=>$mid));
		if ($res) {
			$params['id'] = $res['id'];			
		}
		
		$res = parent::set($params, $ioparams);
		
		
		return $res;
	}
	
	public function del($id)
	{
		$old = parent::del($id);
		if ($old) {
			$m = Factory::GetModel('content2module');
			$m->delete(array('mid'=>$old['mid']));
		}
		return $old;		
	}
}
