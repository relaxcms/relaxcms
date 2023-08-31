<?php

defined('RPATH_BASE') or die();
class LinkcontentModel extends CContentModel
{
	public function __construct($name, $options=null)
	{
		$options['modname'] = 'content';
		parent::__construct($name, $options);
	}
	
	public function LinkcontentModel($name, $options=null)
	{
		$this->__construct($name, $options);
	}

	public function selectForListview(&$params, &$ioparams=array())
	{
		$params['filterfieldcfg'] = array('id'=>true,'name'=>true);

		$data = parent::selectForListview($params, $ioparams);
		$data['hasOptmenu'] = false;

		return $data;
	}
}