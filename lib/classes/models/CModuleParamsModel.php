<?php
/**
 * @file
 *
 * @brief 
 * 
 * 模块参数管理
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class CModuleParamsModel extends CModel
{
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);
	}
		
	public function CModuleParamsModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}	
	
	
	protected function _init_field(&$f)
	{
		switch ($f['name']) {
			case 'flags':
				$f['input_type'] = 'varmulticheckbox';
				$f['sortable'] = true;
				$f['selector'] = 'content_flags';
				break;			
			case 'cid':
				$f['input_type'] = 'model';
				$f['model'] = 'catalog';
				$f['default'] = true;
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
