<?php

class SystemApplication extends CMainApplication
{
	public function __construct($name, $options=null)
	{
		parent::__construct($name, $options);
	}
		
	public function SystemApplication($name, $options=null)
	{
		$this->__construct($name, $options);
	}
	
		
	/**
	 * Ĭ��ҳ
	 *
	 * @return mixed This is the return value description
	 *
	 */
	protected function checkStartComponent(&$ioparams=array())
	{
		$default_component = $this->getDefaultComponent();
		
		if ($ioparams['component'] != $default_component)
			return false;
			
		//��һ��Ȩ��
		$cf = get_config();
		if (!empty($cf['default_component']) && $this->hasPrivilegeOf($cf['default_component'])) {
			$cname = $cf['default_component'];
			$ioparams['component'] = $cname;			 
			if (!$this->isComponent($cname)) {
				$menus = $this->getMenus();
				if (isset($menus[$cname])) {
					$appname = $menus[$cname]['app'];
					if ($appname != $this->_name) {
						$ioparams['aname'] = $appname;
						$ioparams['_aname'] = $appname;
						$ioparams['cname'] = $cname;	
						$ioparams['componentinfo'] = $menus[$cname];	
						$this->setRunApp($appname);
					}
				}
			}	
		}
		
		return true;
	}
	
		
	protected function initDefaultRoleGroup()
	{
		$privdb = $this->getMenusPids();
		if (!is_array($privdb))
			$privdb = array();
			
		//rlog(RC_LOG_ERROR, __FILE__, __LINE__, $privdb); exit;
		
		//��ʼ����
		$m = Factory::GetModel('group');
		//$m->clean();
		
		//ϵͳ����Ա��
		$name = $this->i18n('str_sysadmin_group');
		$params = array('id' =>1, 'name'=>$name , 'type'=>1);
		$m->set($params);
		
		//����Ա��
		$name = $this->i18n('str_admin_group');
		$params = array('id' =>2, 'name'=>$name , 'type'=>1);
		$m->set($params);
		
		//�û���
		$name = $this->i18n('str_user_group');
		$params = array('id' =>3, 'name'=>$name , 'type'=>1);
		$m->set($params);
		
		$m = Factory::GetModel('privilege2group');
		$m->clean();
		
		foreach ($privdb as $key=>$v) {
			if (isset($v['pid']))
				$pid = $v['pid'];
			else 
				$pid = 0;
			if (isset($v['permision']))
				$permision = $v['permision'];
			else 
				$permision = 0;
			
			//ϵͳ����Ա��Ȩ��(���)	
			$params = array('id'=>0, 'pid'=>$pid, 'gid'=>1, 'permision'=>$permision);
			$m->set($params);
			
			//����Ա��
			if (!$v['level'] || ($v['level'] & LEVEL_ADMIN)) {
				$params = array('id'=>0, 'pid'=>$pid, 'gid'=>2, 'permision'=>$permision);
				$m->set($params);
			}	
			
			//�û���
			if (!$v['level'] ) {
				$params = array('id'=>0, 'pid'=>$pid, 'gid'=>3, 'permision'=>$permision);
				$m->set($params);
			}			
		}
		
		//��ʼ����ɫ
		$m = Factory::GetModel('role');
		$role = $this->i18n('str_role_sysadmin');
		$params = array('id' =>'1', 'name'=>$role , 'type'=>1);
		$m->set($params);
		
		$role = $this->i18n('str_role_admin');
		$params = array('id' =>'2', 'name'=>$role , 'type'=>2);
		$m->set($params);
		
		$role = $this->i18n('str_role_user');
		$params = array('id' =>'3', 'name'=>$role , 'type'=>2);
		$m->set($params);
		$m->cache();
		
		//ϵͳ����Ա��ɫ
		$m = Factory::GetModel('group2role');
		$params = array('gid' =>1, 'rid'=>1);
		$m->set($params);
		
		//����Ա
		$params = array('gid' =>2, 'rid'=>2);
		$m->set($params);
		
		//�û�������û���
		$params = array('gid' =>3, 'rid'=>3);
		$m->set($params);
		
		return true;
	}
	
	public function install($ioparams=array())
	{
		$res1 = false;
		//table
		$db = Factory::GetDBO();		
		$sql = RPATH_DATABASE.DS.'sql'.DS."create_table.sql";
		if (file_exists($sql)) {
			if (!($res1 = $db->import($sql))) {
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "WARNING: call import '$sql' error.");				
			}
		}
		
		$res2 = parent::install($ioparams);
		if (!$res2)  {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "WARNING: call install failed!");
		}
		
		$this->init($ioparams);
		
		//��ʼ��Ĭ�������ɫ
		$res3 = $this->initDefaultRoleGroup();		
		return $res1 || $res2 || $res3;
	}
	
	public function localwebservice($ioparams=array())
	{		
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "IN");
		
		//�ļ���ʱ����
		$m = Factory::GetModel('file');
		$m->timerProcess();
		
		$timeout = 300;
		if ($this->check_localwebservice_timeout($timeout)) {
			$m = Factory::GetModel('user_seccode');
			$m->timer();
		}
		
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "OUT");
		return false;
	}
}