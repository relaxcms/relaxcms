<?php

/**
 * @file
 *
 * @brief 
 * 
 * file model
 *
 */

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class MyfileModel extends CFileModel
{
	public function __construct($name, $options=array())
	{
		$options['modname'] = 'file';
		parent::__construct($name, $options);
	}
		
	public function FileModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}

	protected function initAddParams(&$params=array(), &$ioparams=array())
	{
		$params['flags'] = 2;
		$params['flags_disablemask'] = ~FF_MYFLAGS;
	}
	
	protected function initEditParams(&$params=array(), &$ioparams=array())
	{
		$params['flags_disablemask'] = ~FF_MYFLAGS;
	}
	

	protected function maskStatus($newStatus, $oldStatus)
	{
		return ($newStatus&FF_MYFLAGS)|($oldStatus&~FF_MYFLAGS);		
	}

	protected function checkParams(&$params, &$ioparams=array())
	{
		$res = parent::checkParams($params, $ioparams);
		if (!$res)
			return false;

		if (isset($params['flags'])) {
			$fileinfo = $this->get($params['id']);
			if ($fileinfo) {			
				$params['flags'] = $this->maskStatus($params['flags'], $fileinfo['flags']);	
			}
		}
		return true;
	}
}