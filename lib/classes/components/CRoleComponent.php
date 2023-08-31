<?php

/**
 * @file
 *
 * @brief 
 *  用户角色管理
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class CRoleComponent extends CDTComponent
{
	function __construct($name, $options)
	{
		parent::__construct($name, $options);
		
	}
	
	function SystemRoleComponent($name, $options)
	{
		$this->__construct($name, $options);
	}
	
	protected function _init()
	{
		parent::_init();
		$this->_modname = 'role';
	}
	
	
	
	
	protected function setParams ($params=array())
	{
		$gids = $params['gdb'];
		if (!$gids) 
			$gids = array();
			
		$g = Factory::GetGroup();
		$gdb = $g->getRows();
						
		//生新checkbox.
		$group_checkbox = "<div class='group'>\n";
		foreach($gdb as $key=>$v)
		{
			$checked = '';
			if (in_array($v['gid'], $gids))
				$checked = 'checked';
			
			$group_checkbox .= "<span>\n";
			$group_checkbox .= "<input type='checkbox' name='gdb[]' value='{$v['gid']}' $checked /> {$v['groupname']} \n";
			$group_checkbox .= "</span>\n";
			
		}	
		
		$group_checkbox .= "</div>\n";		
		$this->set_var('group_checkbox', $group_checkbox);
		parent::setParams($params);
	}
	
	/*protected function checkParams(&$params)
	{
		$gdb = get_var("gdb", "");
		
		if (!$params["title"]) {
			set_error("str_role_title_empty");
			return false;
		}
		
		if (!$gdb) {
			set_error("str_role_gdb_empty");
			return false;
		}
				
		$params['gdb'] = $gdb;
		return true;
	}*/
	
	
	protected function add2(&$ioparams=array())
	{
		$m = Factory::GetModel('role');
		
		$params = array();
		if ($this->_sbt) {
			if (!$this->getParams($params)) {
				showStatus(-1);
				return false;
			}			
			$res = $m->set($params);
			showStatus($res);
		}
		
		$params['type'] = 2;
		$ioparams['params'] = $params;
		
		return true;
	}
	
	
	//编辑
	protected function edit2(&$ioparams=array())
	{
		$rid = $this->_id;
		if (!$rid) {
			show_error("str_parameter_error");
			return false;
		}
				
		$m = Factory::GetModel('role');
		$params = array();
		if ($this->_sbt) {
			if (!$this->getParams($params)) {
				showStatus(-1);
				return false;
			}			
			$res = $m->set($params);
			showStatus($res);
		}
		return true;
	}
	
	protected function delete(&$ioparams=array())
	{
		$db = Factory::GetDBO();
		$rid = get_var("rid","");
		if (!$rid) {
			show_error("str_parameter_error");
			return false;
		}
		
		$sql = "select * from cms_role where rid=$rid";
		$res = $db->get_one($sql);
		
		if (!$res) {
			show_error("str_parameter_error");
			return false;
		}
		
		if ($res['type'] == 1) {
			show_error('str_role_delete_system_error');
			return false;
		}
		
		//用户角色是否使用
		$sql = "select * from cms_user where rid=$rid";
		if ($db->exists($sql)) {
			show_error("str_role_not_empty");
			return false;
		}
				
		$sql = " delete from cms_group2role where rid=$rid ";
		$db->exec($sql);
		$sql = "delete from cms_role where rid=$rid";		
		$db->exec($sql);		
		rlog('str_role_delete_ok', $sql);
		
		//缓存
		cache_table('role', 'order by rid');
		
		show_message("str_role_delete_ok", $this->_base);
	}
	
}

?>