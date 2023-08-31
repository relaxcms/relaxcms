<?php

/**
 * @file
 *
 * @brief 
 * 
 * 权限管理类
 *
 */

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

define ('PERM_READ', 0x1);
define ('PERM_ADD', 0x2);
define ('PERM_UPDATE', 0x4);
define ('PERM_DELETE', 0x8);
define ('PERM_WRITE', 0xE);
define ('PERM_EXECUTE', 0x10);

define ('LEVEL_USER', 1);
define ('LEVEL_ADMIN', 2);
define ('LEVEL_SYSADMIN', 4);
define ('LEVEL_AUDIT', 8);
define ('LEVEL_SUPER', 10);
define ('LEVEL_USER1', 0x20);
define ('LEVEL_USER2', 0x40);


class CPrivilegeModel extends CTableModel
{
	protected $_permistion_ids = array(
		'i'=>0, 
		'r'=>PERM_READ, 
		'a'=>PERM_ADD,
		'u'=>PERM_UPDATE,
		'd'=>PERM_DELETE,
		'w'=>PERM_WRITE, 
		'x'=>PERM_EXECUTE);
	
	//! 三权分立: 管理员，系统管理员，审计管理员
	protected $_level_ids = array(
		'user'=>LEVEL_USER, 
		'admin'=>LEVEL_ADMIN, 
		'sysadmin'=>LEVEL_SYSADMIN, 
		'audit'=>LEVEL_AUDIT, 
		'super'=>LEVEL_SUPER);
	
	
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);
	}
	
	public function PrivilegeModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}

	public function getPrivilegeByComponent($component)
	{
		if (!$this->_db)
			return false;
		$res = $this->getOne(array('component'=>$component));
		
		return $res;
	}
	
	
	public function getPermistionId($permisions) 
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, '$permisions='.$permisions);
		
		if (!$permisions) //全不选即全选
			return 0xf;		
		$id = 0;
		
		foreach ($permisions as $v) {
			//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, '$v='.$v);
			
			$id |= $this->getPermistionIdByName($v); 
		}
		
		return $id;
	}
	
	public function getPermistionIds() 
	{
		return $this->_permistion_ids;
	}
	
	/**
	 * This is method getPermistionIdByName
	 *
	 * @param mixed $name eg: iw
	 * @return mixed This is the return value description
	 *
	 */
	public function getPermistionIdByName($name) 
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, '$name='.$name);
		
		$id = 0;
		for ($i=0; $i<strlen($name);$i++) {
			$ch = $name[$i];
			//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, '$ch='.$ch);
			$id |= $this->_permistion_ids[$ch]; 
		}
		return $id;
	}
	
	public function isPermistionPublic($name) 
	{
		$id = 0;
		for ($i=0; $i<strlen($name);$i++) {
			$ch = $name[$i];
			$id = $this->_permistion_ids[$ch]; 
			if ($id === 0)
				return true;
		}
		return false;
	}
	
	
	
}