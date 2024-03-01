<?php

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

define ('AT_RCAPP',	4);
define ('AT_RCTPL',	5);
define ('AT_RCTHE',	6);


define ('AE_SN_NOT_FOUND',	-2);
define ('AE_SN_NO_UID',   	-3);
define ('AE_SN_UID_INVALID',   	-3);
define ('AE_USER_FORBIDDEN',-4);
define ('AE_USER_NO_BEAN',  -5);
define ('AE_APP_NOT_ORDER', -6);
define ('AE_APP_NOT_PAYED', -7);
define ('AE_NO_RKEY', 		-8);

class CAppModel extends CTableModel
{
	
	public function __construct($name, $options=null)
	{
		parent::__construct($name, $options);
	}
	
	public function CAppModel($name, $options=null)
	{
		$this->__construct($name, $options);
	}
	
	protected function _initFieldEx(&$f)
	{
		parent::_initFieldEx($f);
		
		switch ($f['name']) {
			case 'type':
				$f['input_type'] = "selector";	
				$f['searchable'] = 2;	
				break;				
			case 'id':
			case 'name':
				$f['searchable'] = true;	
				break;
			case 'uninstall':
			case 'installed':
			case 'remote':
			case 'local':
				$f['input_type'] = 'selector';	
				$f['selector'] = 'yesno';	
				break;
								
			case 'remote_version':
			case 'installdir':
			case 'copyright':
			case 'remote_download_url':
			case 'embeded':
			case 'logo':
			case 'platform':
			case 'language':
			case 'url':
				$f['show'] = false;	
				break;				
			case 'appid':
				$f['show'] = false;	
				$f['edit'] = false;	
				break;				
			case 'cuid':
				$f['input_type'] = 'UID';
				$f['readonly'] = true;
				$f['edit'] = false;				
				break;
			case 'uid':
				$f['input_type'] = 'model';
				$f['model'] = "user";	
				$f['default'] = true;	
				break;		
			case 'ctime':
				$f['readonly'] = true;
				$f['edit'] = false;
				//case 'taxis':
				$f['show'] = false;
				$f['input_type'] = 'TIMESTAMP';
				break;			
			case 'ts':
				$f['input_type'] = 'TIMESTAMP';
				$f['edit'] = false;
				$f['sortable'] = true;
				break;
			case 'status':
				$f['input_type'] = 'selector';
				//$f['edit'] = false;
				$f['sortable'] = true;
				break;
			default:
				break;
		}
		return true;
	}

	public function formatForView(&$row, &$ioparams = array())
	{
		parent::formatForView($row, $ioparams);
		if(empty($row['title'])) 
			$row['title'] = $row['name'];
		
		if (!empty($row['logo'])) {
			$row['previewUrl'] = $row['logo'];
		} else {
			$row['previewUrl'] = ($row['type'] >3)?$ioparams['_dstroot'].'/img/'.'rcapp'.$row['type'].'.png':$ioparams['_dstroot'].'/img/app.png';			
		}
	}

	protected function newID(&$params=array())
	{
		$id = parent::newID($params);
		if (!isset($params['appid']))
			$params['appid'] = md5($id.$params['name'].'_'.$params['type'].time());		
		return $id;
	}	
}