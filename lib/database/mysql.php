<?php
/**
 * @file
 *
 * @brief 
 * MYSQL 数据库管理
 *
 */
class MysqlDatabase extends CDatabase
{
	
	function __construct($name,  $options )
	{
		parent::__construct($name, $options);
	}
	
	function MysqlDatabase($name,  $options )
	{
		$this->__construct($name, $options);
	}
		
	public function connect()
	{
		if ($this->_link)
			return true;
			
		$dbhost = $this->_options['dbhost'];
		if (array_key_exists('dbport', $this->_options) && $this->_options['dbport']) {
			$dbhost .= ":".$this->_options['dbport'];
		}		
		
		
		if (!isset($this->_options['pclose']))
		{
			$link = @mysql_connect(
					$dbhost, 
					$this->_options['dbuser'], 
					$this->_options['dbpassword'], 
					true
					);
		}
		else
		{
			$link = @mysql_pconnect(
					$dbhost, 
					$this->_options['dbuser'], 
					$this->_options['dbpassword']);
			
		}
		
		if (!$link) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "Connect to MySQL failed!", mysql_error());
			return false;
		}
		
		$this->_link = $link;
		
		if($this->get_version() > '4.1' && $this->_options['dbcharset'])
		{
			mysql_query("SET character_set_connection=".$this->_options['dbcharset'].", character_set_results=".$this->_options['dbcharset'].", character_set_client=binary", $this->_link);
		}
		
		if ($this->get_version() > '5.0')
		{
			mysql_query("SET sql_mode=''", $this->_link);
		}
		
		if ($this->_options['dbname']) 
		{
			if (!$this->selectdb($this->_options['dbname']))
			{
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "use db '{$this->_options['dbname']}' failed!", mysql_error());
				return false;
			}
		}		
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
	
	/* ==========================================
	 * TABLE HELPER FUNCTIONS
	 * 表操作
	 * ========================================== */
	
	//查询
	public function query($sql, $method = '') 
	{
		if (!$this->_link) {
			$res = $this->connect();
			if (!$res) {
				return false;
			}
		}
					
		$sql = $this->_prefix_replace($sql);	
		if ($method =='U_B' && function_exists('mysql_unbuffered_query'))
		{
			$query = mysql_unbuffered_query($sql, $this->_link);
		}
		else
		{
			$query = mysql_query($sql, $this->_link);
		}
		
		//rlog(RC_LOG_DEBUG, __FUNCTION__, '$sql='.$sql);
		
		
		$this->query_num++;
		if (!$query) 
		{
			$this->halt('Query Error: ' . $sql);
			return false;
		}
		
		return $query;
	}
		
	
	public function get_one( $sql )
	{
		$query = $this->query($sql, 'U_B');
		if (!$query) {
			return false;		
		}			
		$res = mysql_fetch_array($query, MYSQL_ASSOC);		
		@mysql_free_result($query);
		
		return $res;
	}
	
	function get_max_id($table, $field)
	{
		$sql = "select max($field) as maxid from $table";
		
		$rs = $this->get_one($sql);
		if(!$rs)
		{
			return 1;
		}
		else
		{
			return intval($rs['maxid']) +1;
		}
	}
	
	
	public function exec($sql) 
	{
		$query = $this->query($sql, 'U_B');
		if (!$query) {
			return false;		
		}	
		
		@mysql_free_result($query);	
		
		return true;
	}
	
	
	public function insert($tablename, $params) 
	{
		$keys = array_keys($params);
		$keys = implode("`,`", $keys);		
		$values = implode("','", $params);
		
		$sql = "insert into $tablename (`$keys`) values ('$values')";
		
		$res = $this->exec($sql);
		
		return $res;		
	}
	
	function fetch_array($query, $result_type = MYSQL_ASSOC) 
	{
		return mysql_fetch_array($query, $result_type);
	}
	
	function affected_rows() 
	{
		return mysql_affected_rows($this->_link);
	}
	
	function num_rows($query) 
	{
		$rows = mysql_num_rows($query);
		return $rows;
	}
	
	function free_result($query) 
	{
		return mysql_free_result($query);
	}
	
	function exists($sql)
	{
		$query = $this->Query($sql);
		if (!$query)
			return false;
			
		$row = $this->num_rows($query);
		return !!$row;
	}
	
	
	protected function __error()
	{
		return mysql_error($this->_link);
	}
	
	protected function __errno()
	{
		return mysql_errno($this->_link);
	}	
	
	public function get_version()
	{
		return mysql_get_server_info($this->_link);
	}
	
	public function get_dbinfo()
	{
		return $this->_options['dbtype'].'/'.$this->get_version();
	}	
	
	//关闭数据库
	public function close()
	{
		mysql_close($this->_link);
		$this->_link = 0;
	}	
	
	//数据出现，中断处理
	protected function halt($msg='') 
	{
		$errno = $this->__errno();
		$error = $this->__error();
		
		
		$last_error = "$error($errno)";
		
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "MYSQL Halt: $last_error!");
		
		$this->_last_error = $last_error;
		
	}	
	
	
	/**
	 * 备份
	 *
	 * @param mixed $table This is a description
	 * @param mixed $start This is a description
	 * @param mixed $count This is a description
	 * @return mixed This is the return value description
	 *
	fields : 
	array
	 0 => 
	   array
	     'Field' => string 'aid' (length=3)
	     'Type' => string 'int(11)' (length=7)
	     'Collation' => null
	     'Null' => string 'NO' (length=2)
	     'Key' => string 'PRI' (length=3)
	     'Default' => null
	     'Extra' => string '' (length=0)
	     'Privileges' => string 'select,insert,update,references' (length=31)
	     'Comment' => string '' (length=0)
	 1 => 
	   array
	     'Field' => string 'title' (length=5)
	     'Type' => string 'varchar(255)' (length=12)
	     'Collation' => string 'utf8_general_ci' (length=15)
	     'Null' => string 'NO' (length=2)
	     'Key' => string '' (length=0)
	     'Default' => string '' (length=0)
	     'Extra' => string '' (length=0)
	     'Privileges' => string 'select,insert,update,references' (length=31)
	     'Comment' => string '' (length=0)
	*/
	public function backup_out($table, $start, &$count)
	{
		
		$fields = $this->show_fields($table);
		$fdb = array();
		$fnames = array();
		foreach ($fields as $key=>$v) {
			$fdb[$v['Field']] = $v;
			$fnames[] = $v['Field'];
		}
		
		$data = "";
		$fnames = implode(',', $fnames);
		
		//表结构备份
		if($start == 0)
			$data .= $this->backup_table($table);
		
		
		//导出内容
		$sql = "select $fnames from $table ";
		
		$udb = $this->select($sql, $start, $count);				
		foreach($udb as $key=>$v) {
			$keys = array();
			$values = array();
			
			$tmp = "INSERT INTO $table ($fnames) VALUES(";
			foreach($v as $k2=>$v2){
				$f = $fdb[$k2];
				
				if ($v2 == "") {
					if ($f['Null'] == 'YES')
						$tmp .= "NULL,";
					else 
						$tmp .= "'',";					
				}
				else
					$tmp .= "'".mysql_real_escape_string($v2)."',";
			}
			
			$tmp = substr($tmp, 0, -1); //去除','
			$tmp .= ");\n";
			
			$data .= $tmp;
		}
		
		$count = count($udb);				
		return $data;
	}
	
	protected function backup_table($table)
	{
		$data = "";
		$data .= "DROP TABLE IF EXISTS $table;\n";
		
		$create = $this->get_one("SHOW CREATE TABLE $table");
		
		$create['Create Table'] = str_replace($create['Table'], $table, $create['Create Table']);
		$data .= $create['Create Table'].";\n";
		
		return $data;
	}
	
	
	public function backup_in($file, $start, $count, &$total)
	{
		$lines = file($file);
		$sql = '';
		$num = 0;
		$charset = $this->_options['dbcharset'];
		
		foreach($lines as $key => $value)
		{
			$value = trim($value);
			if(!$value || $value[0]=='#') continue;
			
			if (substr($value, 2) == "--") continue;
			
			if(substr($value, -1) == ';') {
				$sql .= $value;				
				if(strncasecmp($sql, "CREATE", 6) === 0) {
					
					$extra = substr(strrchr($sql, ')'), 1);
					$tabtype = substr(strchr($extra, '=') ,1);
					$tabtype = substr($tabtype, 0,  strpos($tabtype,strpos($tabtype,' ') ? ' ' : ';'));
					$sql = str_replace($extra, '', $sql);
					
					if($this->get_version() > '4.1')
					{
						$extra = $charset ? "ENGINE=$tabtype DEFAULT CHARSET=$charset;" : "ENGINE=$tabtype;";
					}
					else
					{
						$extra = "ENGINE=$tabtype;";
					}
					
					$sql .= $extra;
					
				} elseif (strncasecmp($sql, "INSERT", 6) === 0) {
					$sql = 'REPLACE '.substr($sql, 6);
				}
				
				if ($sql != "") {
					$this->query($sql);
					$sql = '';
				}
				
				rlog("call query:$sql");
			} else {
				$sql .= $value;
			}			
		}
	}
		
	/* ==========================================
	 * SQL SCRIPTS HELPER FUNCTIONS
	 * ==========================================*/
	protected function importSql($sqlinfo)
	{
		$sql = $sqlinfo['sql'];
		
		switch ($sqlinfo['type']) {
			case 'createtable':
				if ($this->get_version() > '4.1')
				{
					$sql .= $this->_options['dbcharset'] ? "ENGINE=MyISAM DEFAULT CHARSET={$this->_options['dbcharset']};" : "ENGINE=MyISAM";
				}
				else
				{
					$sql .= "ENGINE=MyISAM";
				}
			case 'droptable':
			case 'createview':
			case 'insertinto':
			case 'data':
				break;
			default:
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "unknow sql type '{$v['type']}'!");
				break;
		}
		
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, 'sql='.$sql);
		
		$res =  $this->query($sql);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call query failed!sql=$sql");			
		}
		return $res;
	}
		
	//执行脚本
	public function exec_script($sql_file)
	{
		if (!file_exists($sql_file))
		{
			return false;
		}
		
		$lines = file($sql_file);
		$first = true;
		$res = true;		
		
		$sql = '';
		
		foreach($lines as $key => $value)
		{
			$value = trim($value);
			if (!$value || $value[0] == '#') continue;
			if ($value == "") continue;
			if ($first) {
				$value = strim_bom($value);
				$first = false;
			}
			
			if (substr($value, 0, 3) == "-- ") continue;			
			
			if ( substr($value, -1) == ';' ) //以;结尾
			{
				$sql .= $value;
				
				if (preg_match("/create\s+table/i", $sql))
				{
					$extra = substr(strrchr($sql, ')'), 1);
					$tabtype = substr(strchr($extra, '=') ,1);
					$tabtype = substr($tabtype, 0,  strpos($tabtype,strpos($tabtype,' ') ? ' ' : ';'));
					$sql = str_replace($extra, '', $sql);
					
					if($this->get_version() > '4.1')
					{
						$extra = $this->_options['dbcharset'] ? "ENGINE=$tabtype DEFAULT CHARSET={$this->_options['dbcharset']};" : "ENGINE=$tabtype;";
					}
					else
					{
						$extra = "ENGINE=$tabtype;";
					}
					
					$sql .= $extra;
					
				} elseif (preg_match("/create\s+view/i", $sql)) {
					$ttt = 1;
				} elseif(preg_match("/insert\s+into/i", $sql)) {
					$sql = 'REPLACE '.substr($sql,6);
				}
								
				$res = $this->query($sql);
				$sql = '';
			} 
			else
			{
				$sql .= $value;
			}			
		}
		
		return $res;		
	}
	
	public function exec_procedure_file($file, $delimiter='$$')
	{
		$res = true;
		$data = s_read($file);		
		$udb = explode($delimiter, $data);
		
		foreach($udb as $key=>$v)
		{
			$v = trim($v);
			while (substr($v, 0, 3) == "-- ") { //跳过注释
				$v = strstr($v, "\n");
				$v = trim($v);
			} 
			
			$pre = strtolower(substr($v, 0, 4));
			if ($pre == "drop" || $pre == "crea")
			{
				$res = $this->query($v);
			} 
		}
		return $res;				
	}
	
	function get_last_error()
	{
		$res = mysql_error($this->_link).$this->_last_error;
					
		return $res;
	}
	
	
	
	
	function table_exists($table)
	{
		$sql = "SHOW TABLES LIKE '".$table."'";
		$h = $this->query($sql);
		
		if( mysql_num_rows($h) ==1 )
		{
			return true;
		}
		return false;		
	}
	
	
	
	
	function get_guid()
	{
		$sql = "select uuid() as guid";
		
		if(function_exists('mysql_unbuffered_query'))
		{
			$query = mysql_unbuffered_query($sql, $this->_link);
		}
		else
		{
			$query = mysql_query($sql, $this->_link);
		}		
		$rs =& mysql_fetch_array($query, MYSQL_ASSOC);		
		mysql_free_result($query);		
		return $rs['guid'];
	}
	
	
	/**
	 * 查询表的主键
	 *
	 * @param mixed $table_name 表名
	 * @return mixed This is the return value description
	 *
	 */
	public function get_primary_key($table_name) 
	{
		$sql = "describe $table_name";
		$udb = $this->select($sql, 0, 0);
		
		foreach ($udb as $key=>$v){
			if ($v['Key'] == 'PRI') {
				return $v['Field'];
			}
		}
		
		return false;
	}

	

	public function queryFields($table_name)
	{
		$sql = "SHOW FULL FIELDS FROM $table_name";
		
		$res = $this->query($sql);
		

		$fdb = array();
		if ($res) {
			$strtypes = array('char' => true ,'varchar'=>true, 'text'=>true, 'tinytext'=>true );

			while ($v = $this->fetch_array($res)) {
				
				$name = $v['Field'];
				$is_primary_key = ($v['Key'] == 'PRI')?true:false;
				
				$type = $v['Type'];
				$pos = strpos($type, '(');
				if ($pos !== false) {
					$length = intval(substr($type, $pos+1));
					$type = substr($type, 0, $pos);
		  		} else {
		  			$length = 0;
		  		}
				
				$is_string = isset($strtypes[$type])?true:false;
				$is_null = ($v['Null'] == 'YES')?true:false;
				
				$v['type'] = $type;
				$v['length'] = $length;
				$v['is_primary_key'] = $is_primary_key;
				
				$v['is_field'] = true;
				$v['is_string'] = $is_string;
				$v['is_null'] = $is_null;
				$v['required'] = !$is_null;
												
				$fdb[$name] = $v;
			}
			$this->free_result($res);	
		}
		return $fdb;
	}	
	
	public function get_field_names($table_name, $limit=0)
	{
		$index = 0;
		
		if (!$this->table_exists($table_name))
			return false;
		$fdb = array();
		
		$udb = $this->select("SHOW FULL FIELDS FROM $table_name", 0, 0);
		foreach($udb as $k=>$v) {
			if ($limit && $index++ >= $limit)
				break;
				
			$fdb[$v['Field']] = $v['Field'];			
		}
		return $fdb;
	}
	
		
	//显示表格中字段详细信息
	
	/**
	 * This is method show_fields
	 *
	 * @param mixed $table_name This is a description
	 * @return mixed This is the return value description
	 *
	 * 
	mysql> SHOW FULL FIELDS FROM cms_custom_info
	   -> ;
	+-------------+-------------+-------------------+------+-----+---------+-------+---------------------------------+---------+
	| Field       | Type        | Collation         | Null | Key | Default | Extra | Privileges                      | Comment |
	+-------------+-------------+-------------------+------+-----+---------+-------+---------------------------------+---------+
	| custom_id   | int(11)     | NULL              | NO   | PRI | NULL    |       | select,insert,update,references |         |
	| name        | varchar(32) | latin1_swedish_ci | NO   |     | NULL    |       | select,insert,update,references |         |
	| description | text        | latin1_swedish_ci | YES  |     | NULL    |       | select,insert,update,references |         |
	| idtype      | tinyint(4)  | NULL              | NO   |     | NULL    |       | select,insert,update,references |         |
	| idno        | varchar(32) | latin1_swedish_ci | NO   |     | NULL    |       | select,insert,update,references |         |
	| mobile      | varchar(64) | latin1_swedish_ci | NO   |     | NULL    |       | select,insert,update,references |         |
	| telephone   | varchar(64) | latin1_swedish_ci | NO   |     | NULL    |       | select,insert,update,references |         |
	| email       | varchar(64) | latin1_swedish_ci | NO   |     | NULL    |       | select,insert,update,references |         |
	| pid         | int(11)     | NULL              | NO   |     | NULL    |       | select,insert,update,references |         |
	| level       | tinyint(4)  | NULL              | NO   |     | NULL    |       | select,insert,update,references |         |
	| type        | tinyint(4)  | NULL              | NO   |     | NULL    |       | select,insert,update,references |         |
	| status      | tinyint(4)  | NULL              | NO   |     | NULL    |       | select,insert,update,references |         |
	+-------------+-------------+-------------------+------+-----+---------+-------+---------------------------------+---------+
	12 rows in set (0.02 sec)
	
	mysql>
	
	 */

	public function show_fields($table_name)
	{
		if (!$this->table_exists($table_name))
			return false;
			
		return $this->select("SHOW FULL FIELDS FROM $table_name", 0, 0);
	}
	
	//显示数据库中所有表
	/**
	mysql> show table status where Engine !='VIEW';
+-------------------------------+--------+---------+------------+-------+----------------+-------------+-------------------+--------------+-----------+----------------+------------
---------+---------------------+------------+-------------------+----------+----------------+---------+
| Name                          | Engine | Version | Row_format | Rows  | Avg_row_length | Data_length | Max_data_length   | Index_length | Data_free | Auto_increment | Create_time
         | Update_time         | Check_time | Collation         | Checksum | Create_options | Comment |
+-------------------------------+--------+---------+------------+-------+----------------+-------------+-------------------+--------------+-----------+----------------+------------
---------+---------------------+------------+-------------------+----------+----------------+---------+
| cms_accesslog                 | MyISAM |      10 | Dynamic    |     0 |              0 |           0 |   281474976710655 |         1024 |         0 |              1 | 2020-05-27
20:40:13 | 2020-05-27 20:40:13 | NULL       | latin1_swedish_ci |     NULL |                |         |
| cms_acl                       | MyISAM |      10 | Dynamic    |     0 |              0 |           0 |   281474976710655 |         1024 |         0 |           NULL | 2020-05-27
20:40:11 | 2020-05-27 20:40:11 | NULL       | latin1_swedish_ci |     NULL |                |         |
| cms_analysis                  | MyISAM |      10 | Dynamic    |     0 |              0 |           0 |   281474976710655 |         2048 |         0 |              1 | 2020-05-27
20:40:11 | 2020-05-27 20:40:11 | NULL       | latin1_swedish_ci |     NULL |                |         |
*/
	public function show_tables()
	{
		$udb = array();
		$res = mysql_query("show table status where Engine !='VIEW'");
		while ($v = $this->fetch_array($res)) 
		{
			$udb[] = $v;
		}
		
		return $udb;
	}
	
	
	public function escape_string($data)
	{
		/*
		00->5c 30  0x00->\0
		0a->5c 6e  换行->\n
		0d->5c 72  回车->\r
		1a->5c 5a  代替->\Z
		22->5c 22  "   ->\"
		27->5c 27  '   ->\'
		5c->5c 5c  \   ->\\
		*/
		
		return mysql_real_escape_string($data); //mysql_escape_string($data);
	}
	
	public function set_names()
	{
		mysql_query("SET character_set_connection=".$this->_options['dbcharset'].", character_set_results=".$this->_options['dbcharset'].", character_set_client=binary", $this->_link);		
	}
}
