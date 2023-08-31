<?php
/**
 * @file
 *
 * @brief 
 * 文件视图
 *
 */
class FileviewModule extends CModule
{
	function __construct($name, $attribs)
	{
		parent::__construct($name, $attribs);
	}
	
	function FileviewModule($name, $attribs)
	{
		$this->__construct($name, $attribs);
	}
	

	protected function show(&$ioparams = array())
	{
		$nosidebar = isset($this->_attribs['nosidebar'])?intval($this->_attribs['nosidebar']):false;	
		//最大上传限制
		$uploadmaxsize = get_upload_max_filesize();

		$uploadmaxsize -= 4*1024*1024; //减小1M
		
		$tablename = 'file';
		$params = get_var("params", array());		
		
		if (isset($ioparams['params'])) 
			$params = $ioparams['params'];
		$m = Factory::GetModel($tablename);
		$modinfo = $m->getModelInfo();
		
		$fields = $m->getFieldsForSearch($params, $ioparams);
		/*foreach ($fields as $key => &$v) {
			$v['sortable'] = $v['sortable']?'true':'false';
		}*/		
		$pkey = $modinfo['pkey'];
		$this->assign('pkey', $fields[$pkey]);
		
		$this->assign('tablename', $tablename);
		$mi18n  = get_i18n('mod_'.$tablename);
		$this->assign('mi18n', $mi18n);
				
		//$mi18n[modelname]
		!$table_title && $table_title = $mi18n['modelname'];
		$this->assign('table_title', $table_title);
		
		$this->assign('fields', $fields);
				
		$this->assign('uploadmaxsize', $uploadmaxsize);
		$this->assign('nosidebar', $nosidebar?"nosidebar":"");	
	}
}
