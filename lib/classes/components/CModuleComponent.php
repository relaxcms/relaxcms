<?php

/**
 * @file
 *
 * @brief 
 *  消息
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class CModuleComponent extends CDTComponent
{
	function __construct($name, $options)
	{
		parent::__construct($name, $options);
	}
	
	function CModuleComponent($name, $options)
	{
		$this->__construct($name, $options);
	}
	
	//detail
	protected function detail(&$ioparams=array())
	{
		$this->initActiveTab(3);
		
		$params = parent::detail($ioparams);
		
		$ctype = $params['ctype'];
		$tplname = $params['tplname'];
		
		$content = htmlspecialchars_decode($params['content']);
		
		switch($ctype) {
			case 1:
				//参数
				if (($modules = parseTplModuleData($content))) {
					$old = $modules['mdb'][0];
					
					$old_attribs = $old['attribs'];
					$attribs = attr2array($old_attribs);
					$attribs['mid'] = $params['mid'];
					$newattrs = array2attr($attribs);
					
					$content = str_replace($old_attribs, $newattrs, $content);
				}
				break;
			case 3://a link
			case 4: //img
			case 5: //video
				if (($res = parseTplLinkData($content))) {
					$old = $res['mdb'][0];					
					//replace content
					if ($old['url'] != $params['url'])
						$content = str_replace($old['url'], $params['url'], $content);
					if ($old['src'] != $params['src'])
						$content = str_replace($old['src'], $params['src'], $content);					
					if ($old['title'] != $params['title'])
						$content = str_replace($old['title'], $params['title'], $content);						
				}
				break;
			default:
				break;
		}
		
		$this->assign('content', $content);
		
		
		//绑定内容
		$mid = $this->_id;
		$m = Factory::GetModel('module_params');
		$res = $m->getOne(array('mid'=>$mid));
		$m2c_params =($res)?$res:array();
				
		$fields = $m->getFieldsForInputEdit($m2c_params, $ioparams);
		if (!$fields) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "WARNING: getFieldsForInputEdit failed!");
			$fields = array();
		}
		
		$fields['mid']['edit'] = false;
		
		$this->assign('fields', $fields);
		
		$this->setTpl('site_module_detail');
				
		
	}
	
	protected function setmoduleparams(&$ioparams=array())
	{
		$this->getParams($params);
		
		$m = Factory::GetModel('module');
		$res = $m->setModuleParams($this->_id, $params);
		
		showStatus($res?0:-1);
	}
}
