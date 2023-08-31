<?php
/**
 * @file
 *
 * @brief 
 * 
 * Msg Model
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class CMsgModel extends CTableModel
{
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);
	}
		
	public function CMsgModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}
	
	protected function _initFieldEx(&$f)
	{
		switch ($f['name']) {
			case 'opened':
			case 'sends':
				$f['edit'] = false;
				break;
			case 'type':
			case 'level':
			case 'status':
				$f['input_type'] = 'selector';
				break;
			case 'flags':
				$f['input_type'] = 'varmulticheckbox';
				break;
			case 'cuid':
				$f['input_type'] = 'UID';
				$f['searchable'] = 'true';
				break;
			case 'ctime':
				$f['input_type'] = 'TIMESTAMP';
				break;
			default:
				break;
		}
		
		return true;
	}
	
	
	
	public function formatForView(&$row, &$ioparams = array())
	{
		$res = parent::formatForView($row, $ioparams);
		
		$row['_level'] = $this->getLabelColorName($row['level'], $icon);
		$row['_icon'] = $icon;
		$row['_ctimelong'] = tformat_timelong($row['ctime']);
		
		
		return $res;
	}
	
	
	public function getMyList($params=array(), &$ioparams=array())
	{
		$m = Factory::GetModel('msg2user');
		$uid = get_uid();
		$params['uid'] = $uid;
		
		$udb = $m->select($params, $ioparams);
		
		$rows = array();
		foreach ($udb as $key=>$v) {
			$mid = $v['mid'];
			$row = $this->getForView($mid);
			
			$rows[] = $row;
		}
		
		return $rows;
	}
	
	
	protected function buildInputForMultiSelect(&$field, $params, &$ioparams=array())
	{
		$ac = Factory::GetApp()->getActiveComponent();
		if($ac)			
			$ac->enableJSCSS('multiselect');
		
		$name = $field['name'];
		
		$m = Factory::GetModel('user');
		$users = $m->gets();
		
		$res = '';
		$res .= "<select multiple='multiple' class='multi-select' id='param_$name' name='params[$name][]'>";
		
		foreach ($users as $key=>$v) {
			$username = $v['nickname']?$v['nickname'] : $v['name'];
			$selected = '';
			$id = $v['id'];
			if (isset($params['_uid'])) {
				foreach ($params['_uid'] as $k2=>$v2) {
					if ($v2['uid'] == $id) {
						$selected = 'selected';
						break;
					}
				}
			}
			$res .= "<option  value='$id' $selected >$username</option>";
		}
		
		
		$res .= "</select><script>$('#param_$name').multiSelect(); </script>";
		return $res;				
	}	
	
	
	
	public function getFieldsforInput($params=array(), &$ioparams=array())
	{
		$fdb = parent::getFieldsforInput($params, $params);
		
		$name = 'uid';
		$newfield = $this->newField($name, array('input_type'=>'uid','sort'=>99));
		$newfield['input'] = $this->buildInputForMultiSelect($newfield, $params,  $ioparams);
		
		
		$fdb[$name] = $newfield;
		
		
		array_sort_by_field($fdb, "sort", false);
		
		return $fdb;
	}
	
	protected function deleteTo($id)
	{
		$m = Factory::GetModel('msg2user');
		$res = $m->delete(array('mid'=>$id));
		return $res;
	}
	
	protected function sendTo($params)
	{
		$res = false;
		$m = Factory::GetModel('msg2user');
		
		$id = $params['id'];
		
		$this->deleteTo($id);
		if (isset($params['uid'])) {
			$udb = is_array($params['uid'])?$params['uid']:explode(',', $params['uid']);	
			foreach ($udb as $key=>$v) {
				$item = array();
				$item['mid'] = $id;
				$item['uid'] = $v;
				
				$res = $m->set($item);			
				
			}
		}
		
		return $res;
	}
	
	
	public function set(&$params, &$ioparams=array())
	{
		$res = parent::set($params, $ioparams);
		if ($res) {
			//发送
			$this->sendTo($params);
		}
		return $res;
	}
	
	public function del($id)
	{
		$res = parent::del($id);
		if ($res) {
			$this->deleteTo($id);
		}
		
		return $res;
	}
}
