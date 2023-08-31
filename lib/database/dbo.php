<?php
/**
 * @file
 *
 * @brief 
 * MongoDB 数据库管理
 *
 * 
 * 
 */
class DboDatabase extends CDatabase
{
	function __construct($name, $options)
	{
		parent::__construct($name, $options);
	}
	
	function DboDatabase($name, $options)
	{
		$this->__construct($name, $options);
	}
	
	protected function parseDsnForMysql($options)
	{
		if (!empty($options['socket'])) {
			$dsn = 'mysql:unix_socket='. $options['socket'];
		} elseif (!empty($options['dbport'])) {
			$dsn = 'mysql:host=' . $options['dbhost'] . ';port=' . $options['dbport'];
		} else {
			$dsn = 'mysql:host=' . $options['dbhost'];
		}
		$dsn .= ';dbname=' . $options['dbname'];
		
		if (!empty($options['dbcharset'])) {
			$dsn .= ';charset=' . $options['dbcharset'];
		}
		return $dsn;
				
	}
	
	protected function parseDsn($options)
	{
		$type = $options['dbotype'];
		switch ($type) {
			default:
			case 'mysqli':
			case 'mysql':
				$res = $this->parseDsnForMysql($options);
			break;
		}
		return $res;
	}
		
	public function connect()
	{
		if ($this->_link)
			return true;
		
		//"mysql:host=localhost;port=3306;dbname=rcdb7;charset=latin1"
		$dsn = $this->parseDSN($this->_options);
		
		$link = new PDO($dsn, $this->_options['dbuser'], $this->_options['dbpassword'], $params);
		
		if (!$link) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "new PDO failed!dsn=$dsn");
			return false;
		}
				
		$this->_link = $link;
		return true;
	}
	
	/* ==========================================
	 * DB HELPER FUNCTIONS
	 * 库操作
	 * ========================================== */
	public function db_select($dbname=null)
	{
		if (!$dbname)
			$dbname = $this->_options['dbname'];
		
		$res = @mysql_select_db($dbname);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "use db '{$this->_options['dbname']}' failed!", mysql_error());
		}
		
		return $res;
	}
	
	public function db_exists($dbname)
	{
		$sql = "show databases like '$dbname%';";
		$h = $this->query($sql);
		if (mysql_num_rows($h) ==1 )
		{
			return true;
		}
		return false;	
	}
	
	
	public function db_drop($dbname)
	{
		if (!$this->db_exists($dbname))
		{
			return true;
		}
		
		$sql = "drop database $dbname";		
		if (function_exists('mysql_unbuffered_query'))
		{
			$query = mysql_unbuffered_query($sql, $this->_link);
		}
		else
		{
			$query = mysql_query($sql, $this->_link);
		}
		
		if (!$query) 
		{
			return false;
		}
		
		@mysql_free_result($query);
		return true;
	}
	
	public function db_create($dbname, $exists_drop=false)
	{
		if ($exists_drop === true)
		{
			$this->db_drop($dbname);
		}
		
		$sql = "create database $dbname";		
		if(function_exists('mysql_unbuffered_query'))
		{
			$query = mysql_unbuffered_query($sql, $this->_link);
		}
		else
		{
			$query = mysql_query($sql, $this->_link);
		}
		
		if (!$query) 
		{
			return false;
		}
		@mysql_free_result($query);
		return true;
	}
	
	
	
	public function db_space($dbname='')
	{
		$o_size = 0;
		$query = mysql_query("SHOW TABLE STATUS");
		while ($rs = $this->fetch_array($query)) {
			$o_size = $o_size + $rs['Data_length'] + $rs['Index_length'];
		}
		
		$o_size	 = nformat_size($o_size);
		return $o_size;		
	}
	
	
	
	
	
}
