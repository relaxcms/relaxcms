<?php
/**
 * @file
 *
 * @brief 
 * 
 * 角色管理类
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );
class CRoleModel extends CTableModel
{
	
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);
	}
	
	public function CRoleModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}

	protected function _initFieldEx(&$f)
	{
		switch ($f['name']) {
			case 'type':
				$f['input_type'] = 'selector';
				//$f['show'] = false;	
				$f['edit'] = false;								
				break;
			case 'level':
				$f['edit'] = false;
				$f['show'] = false;									
				break;
			default:
				break;
		}

		return true;
	}
	
	/*protected function formatOperate($row, &$ioparams=array())
	{
		$id = $row[$this->_pkey];
		
		$opt = "";
		if (hasPrivilegeOf($ioparams['component'], 'edit')) 
			$opt .= "<button type='button' class='btn green btn-xs btn-circle tlink tooltips ' data-original-title='修改' data-id='$id' data-task='edit'>
				<i class='fa fa-edit' ></i></button> ";
		if (hasPrivilegeOf($ioparams['component'], 'del') && $row['type'] != 1) 	
			$opt .= "<button type='button' class='btn red btn-xs btn-circle delete tooltips ' data-original-title='删除' data-id='$id'>
				<i class='fa  fa-trash-o' ></i></button>"; //icon-wrench / icon-trash 
		
		return $opt;
	}*/
		
	public function get($id)
	{
		$res = parent::get($id);
		if (!$res) {
			return false;
		}
				
		$m = Factory::GetModel('group2role');
		$params = array('rid'=>$id);
		$udb = $m->select($params);		
		
		$gdb = array();
		foreach($udb as $key =>$v) {
			$gdb[] = $v['gid'];
		}		
		$res['gdb'] = $gdb;	
				
		return $res;	
	}
	
	public function formatForView(&$row, &$ioparams = array())
	{
		$res =  parent::formatForView($row, $ioparams);
		
		//previewUrl for Listview
		$row['previewUrl'] = $ioparams['_dstroot']."/img/group.png";
		
	}

	public function set(&$params, &$ioparams=array())
	{
		$res = parent::set($params);
		if ($res) {
			$rid = $params['id'];
						
			$m = Factory::GetModel('group2role');
			$filter = array('rid'=>$rid);
			$m->clean($filter);			
			if (isset($params['gdb'])) {
				$gdb = $params['gdb'];
				foreach($gdb as $key=>$v) {
					$params = array('rid'=>$rid, 'gid'=>$v);
					$res = $m->set($params);
				}
			}
		}
		return $res;
	}
	
	protected function add(&$params=array(), &$ioparams=array())
	{
		$params['type'] = 2;
		$res = parent::add($params);
		
		return $res;
	}
	
	public function getRoleTitleById($id)
	{
		$res = parent::getRowById($id);
		return $res['title'];
	}


	/**
     * 组表
	 */
	protected function buildInputForGdb($params, &$field, &$ioparams=array())
	{
		$gids = isset($params['gdb'])?$params['gdb']:array();

		$m = Factory::GetGroup();
		$gdb = $m->select();
						
		//生新checkbox.
		$group_checkbox = "<div class='group'>\n";
		foreach ($gdb as $key=>$v)
		{
			$checked = '';
			$gid = $v['id'];
			
			if (in_array($gid, $gids))
				$checked = 'checked';
			
			$group_checkbox .= "<label class='checkbox-inline'>\n";
			$group_checkbox .= "<input type='checkbox' name='params[gdb][]' value='$gid' $checked /> {$v['name']} \n";
			$group_checkbox .= "</label>\n";
			
		}	
		
		$group_checkbox .= "</div>\n";		

		return $group_checkbox;
	}
	
	public function getFieldsforInput($params=array(), &$ioparams=array())
	{
		$fdb = parent::getFieldsforInput($params, $params);
		
		//加 gdb
		$name = 'gdb';
		$newfield = $this->newField($name, array('input_type'=>'gdb','sort'=>99));
		$newfield['input'] = $this->buildInputForGdb($params, $newfield, $ioparams);
		
		$fdb[$name] = $newfield;
		
		array_sort_by_field($fdb, "sort", false);
		
		return $fdb;
	}
	
	
	public function del($id)
	{
		$info = $this->get($id);
		if (!$info)
			return false;
			
		if ($info['type'] == 1)
			return false;
			
		$res = parent::del($id);
		return $res;
		
	}	
}