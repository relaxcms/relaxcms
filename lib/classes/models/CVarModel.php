<?php

/**
 * @file
 *
 * @brief 
 * 
 * 变量管理
 *
 */

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );


define ('RVAR_COLOR',		1);
define ('RVAR_IMAGE',		2);
define ('RVAR_ATTACH',		3);
define ('RVAR_AUTHOR',		4);
define ('RVAR_FROM',		5);
define ('RVAR_CATALOG',		6);
define ('RVAR_CONTENT',		7);
define ('RVAR_NAV',			8);
define ('RVAR_TARGET',		9);
define ('RVAR_TPL',			10);
define ('RVAR_STYLE',		11);
define ('RVAR_TPL_LIST',	12);
define ('RVAR_TPL_CONTENT', 13);
define ('RVAR_THREME',		14);
define ('RVAR_UTYPE',		15);
define ('RVAR_DIV',			16);
define ('RVAR_MODEL_ATTR',	17);

class CVarModel extends CTableModel
{
	
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);
	}
		
	public function CVarModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}
	
	protected function _initFieldEx(&$f)
	{
		switch ($f['name']) {			
			
			case 'pid':
				$f['input_type'] = 'treemodel';
				$f['show'] = false;		
				break;		
			
			default:
				break;
		}
		return true;
	}


	public function getVarList($pid)
	{
		$filter = array('pid'=>$pid);
		$udb = $this->select($filter);

		$vardb = array();
		foreach ($udb as $key => $v) {
			$k = $v['value'];
			$vardb[$k] = $v;
		}

		return $vardb;
	}
	
	public function getVarListByName($name)
	{
		$res = $this->getOne(array('name'=>$name));
		if (!$res)
			return false;
		return $this->getVarList($res['id']);
	}

	public function get_var_select($key, $default=null)
	{
		$res = "";		
		$vardb = $this->getVarList($key);

		foreach ($vardb as $k=>$v) {
			$selected = $default == $k ? 'selected' : '';
			$res .= "<option value='$k' $selected > $v</option>";
		}
		
		return $res;
	}	

	public function is_var_title($key, $default)
	{
		$vardb = $this->getVarList($key);

		foreach ($vardb as $k=>$v) {
			if ($default == $k) 
				return $v;
		}		
		return false;
	}	

	public function get_var_checkbox($vid, $status)
	{
		$res = "";		
		$vardb = $this->getVarList($vid);

		$res = "<div class='checkbox-list'>";		
		$mask = 0x1;
		
		foreach ($vardb as $key=>$ct)	{
			$checked = "";
			$mask = 0x1 << $key; // mask
			
			$ck = $mask & $status;
			
			if($ck !== 0) $checked = "checked";
			$res .= "<label class='checkbox-inline'><input type='checkbox' name='vars[]' value='$key' $checked>$ct </label>";
		}
		
		$res .= "</div>";

		return $res;
	}

	public function get_var_checkbox_name($vid, $mask)
	{
		$res = "";		
		$vardb = $this->getVarList($vid);
		foreach($vardb as $k=>$v) {
			$flag = ($mask & (1 << $k));
			if ($flag) {
				$res .= "$v, ";
			} else {
				$res .= "__, ";
			}
		}
		$res = substr($res, 0, -2);
	
		return $res;	
	}

	//返回查询用掩码 
	public function get_var_mask($var_array)
	{
		if (!is_array($var_array))
			$var_array = explode(",", $var_array);			
		
		$mask = 0x0;
		foreach($var_array as $key=>$v) {
			$mb = 0x1 << $v;
			$mask |= $mb;					
		}
		
		return $mask;
	}

	public function get_var_name($key, $var)
	{
		$vardb = $this->getVarList($key);		
		foreach ($vardb as $k=>$v) {
			if ($var == $k) 
				return $v;
		}

		return false;
	}

	public function get_var_table($key)
	{
		return $this->getVarList($key);
	}
	
	
	
	protected function formatOperate($row, &$ioparams=array())
	{
		$id = $row[$this->_pkey];
		$defOpt = parent::formatOperate($row, $ioparams);
		
		$defOpt[] = array(
				'name'=>'add',
				'title'=>'添加子变量',
				'icon'=>'fa fa-plus',
				'url'=>$ioparams['_base'].'/add?id='.$id,
				);
		/*$res =  "<a href='$add_sub_url' class='btn green btn-xs btn-circle' data-original-title='添加子变量' title='添加子变量' > <i class='fa fa-plus' ></i> </a>";
		$res .= $defOpt;*/
		return $defOpt;
		
	}
	
	
	public function getMaskByTitle($name, $title)
	{
		$res = $this->getOne(array('name'=>$name));
		if (!$res)
			return false;
		
		$id = $res['id'];
					
		$vardb = $this->getVarList($res['id']);
		foreach ($vardb as $key=>$v) {
			if ($v['title'] == $title) 
				return 0x1 << $v['value']; // mask;			
		}
		
		//创建
		$val = 1;
		while(1) {
			$found = false;
			foreach ($vardb as $key=>$v) {
				if ($v['value'] == $val) {
					$val ++;
					$found = true;
					break;
				}				
			}
			
			if (!$found)
				break;
		}
		
		$_params = array();
		$_params['pid'] = $id;
		$_params['value'] = $val;
		$_params['title'] = $title;
		
		$res = $this->set($_params);
		
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "set var failed!", $_params);
			return false;
		}		
		
		return 0x1 << $val;
	}
}