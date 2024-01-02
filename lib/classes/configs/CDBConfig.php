<?php
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );
define('DEFAULT_DATABASE_NAME', 'rcdb9');

class CDBConfig extends CConfig
{
	protected $_dbtype = 'mysql';

	public function __construct($name, $options= array())
	{
		parent::__construct($name, $options);
		$this->_dbtype = isset($options['dbtype'])?$options['dbtype']:'mysql';
	}	

	function CDBConfig($name, $options= array()) 
	{
		$this->__construct($name, $options);
	}
	
	static function GetInstance($name="", $options=array())
	{
		static $instances;		
		if (!isset( $instances )) 
		{
			$instances = array();
		}
		
		if (in_array($name,array('mysql', 'sqlite','mssql', 'pqsql','mongo','mysqlpdo'))) {
			$options['dbtype'] = $name;
		}
		
		$sig = serialize(array($name, $options));		
		$instance = null;		
		if (empty($instances[$sig])) {
			$instance	= new CDBConfig($name, $options);
			$instances[$sig] = $instance;
		} else {
			$instance = $instances[$sig];			
		}		
		return $instance;
	}
	
	
	
	public function load($reload=false)
	{
		$cfg = parent::load($reload);
		
		$dbtype = isset($cfg['dbtype'])?$cfg['dbtype']:$this->_dbtype;
		
		$dbhost = '127.0.0.1';
		$dbport = '3306';		
		$dbuser = 'root';
		$dbpassword = '';		
		$dbname = DEFAULT_DATABASE_NAME;		
		switch($dbtype) {
			case 'pgsql':
				$dbport = '5432';
				$dbname = 'postgres';				
				break;
			case 'mongo':
				$dbport = '27017';
				break;
			case 'mssql':
				$dbuser = 'sa';
				$dbpassword = 'sa';
				$dbport = '1433';
				//$dbname = 'master';	
				break;
			case 'mysqlpdo':	
				$dbhost = '127.0.0.1'; //dns慢
				break;
			default:
				break;
		}	
		
		$cfg['dbtype'] = $dbtype;
		!isset($cfg['dbhost']) &&  $cfg['dbhost'] = $dbhost;
		!isset($cfg['dbport']) &&  $cfg['dbport'] = $dbport;
		!isset($cfg['dbuser']) &&  $cfg['dbuser'] = $dbuser;
		!isset($cfg['dbpassword']) &&  $cfg['dbpassword'] = $dbpassword;
		!isset($cfg['dbname']) &&  $cfg['dbname'] = $dbname;
		!isset($cfg['dbcharset']) &&  $cfg['dbcharset'] = 'utf8';//'latin1';
		!isset($cfg['prefix']) &&  $cfg['prefix'] = 'cms_';
		!isset($cfg['pclose']) &&  $cfg['pclose'] = true;
		
		return $cfg;
	}
}
