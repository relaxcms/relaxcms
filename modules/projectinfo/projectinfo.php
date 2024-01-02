<?php
/**
 * @file
 *
 * @brief 
 * ProjectInfo 模块
 *
 */
class ProjectinfoModule extends CModule
{
	function __construct($name, $attribs)
	{
		parent::__construct($name, $attribs);
	}
	
	function ProjectinfoModule($name, $attribs)
	{
		$this->__construct($name, $attribs);
	}	

	protected function show(&$ioparams=array()) 
	{
		$id = !empty($this->_attribs['id'])?$this->_attribs['id']:1;
		if (!$id) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "no id!");
			return false;
		}

		//版本库

		//id = 'svnroot|name|id';
		$params = is_numeric($id)? array('id'=>$id):array('svnroot'=>$id);
		$m = Factory::GetModel('pm_project');
		$res = $m->getInfoForView($params, $ioparams);

		//最新发布
		$_params = array('project_id'=>$res['id']);
		$m2 = Factory::GetModel('apm_app');
		$res2 = $m2->getInfoForView($_params, $ioparams);

		$stableinfo = !empty($res2['stableVersion'])?$res2['stableVersion']:$res2['lastVersion'];

		$this->assign('params', $res);
		$this->assign('stableinfo', $stableinfo);
	}
}