<?php
/**
 * @file
 *
 * @brief 
 * PostgreSQL 数据库管理
 *
 * eg: https://www.php.net/manual/zh/function.pg-connect.php
 * 
 * 
 * $dbconn = pg_connect("dbname=mary");
//connect to a database named "mary"
$dbconn2 = pg_connect("host=localhost port=5432 dbname=mary");
// connect to a database named "mary" on "localhost" at port "5432"
$dbconn3 = pg_connect("host=sheep port=5432 dbname=mary user=lamb password=foo");
//connect to a database named "mary" on the host "sheep" with a username and password

$conn_string = "host=sheep port=5432 dbname=test user=lamb password=bar";
$dbconn4 = pg_connect($conn_string);
//connect to a database named "test" on the host "sheep" with a username and password
 * 
 * 
 */
class PgsqlDatabase extends CDatabase
{
	
	function __construct($name,  $options )
	{
		parent::__construct($name, $options);
	}
	
	function PgsqlDatabase($name,  $options)
	{
		$this->__construct($name, $options);
	}
		
	public function connect()
	{
		if ($this->_link)
			return true;
		
		$cnnstring = "host=".$this->_options['dbhost'];
		$cnnstring .= " port=".$this->_options['dbport'];
		$cnnstring .= " dbname=".$this->_options['dbname'];
		$cnnstring .= " user=".$this->_options['dbuser'];
		$cnnstring .= " password=".$this->_options['dbpassword'];
		
		$link = pg_connect($cnnstring);	
		if (!isset($this->_options['pclose'])) 	{
			$link = pg_connect($cnnstring);		
		} else {
			// pg_pconnect(string $connection_string, int $connect_type = ?): resource
			$link = pg_pconnect($cnnstring);		
		}
			
		if (!$link) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "Connect to PostgreSQL failed!cnnstring=$cnnstring", pg_last_error());
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
		$this->_options['dbname'] = $dbname;
		$this->close();
		$res = $this->connect();
		
		return $res;
	}
	
	public function db_create($dbname, $exists_drop=false)
	{
		if ($exists_drop === true)
		{
			$this->db_drop($dbname);
		}
		
		$sql = "create database $dbname";		
		$query = pg_query($this->_link, $sql);
		
		if (!$query) 
		{
			return false;
		}
		
		@pg_free_result($query);
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
		$sql = "select datname from pg_database where datname='$dbname'";
		$h = $this->query($sql);
		if (pg_num_rows($h) ==1 )
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
		$query =$this->query($sql);
		if (!$query) 
		{
			return false;
		}
		
		@pg_free_result($query);
		return true;
	}
	
	/* ==========================================
	 * TABLE HELPER FUNCTIONS
	 * 表操作
	 * ========================================== */
	
	
	function select_db($dbname)
	{
		return pg_select_db($dbname);
	}
	
	
	//查询
	public function query($sql, $method = '') 
	{
		$sql = $this->_prefix_replace($sql);
			
		$query = pg_query($this->_link, $sql);
				
		$this->query_num++;
		if (!$query) 
		{
			$this->halt('Query Error: ' . $sql);
			return false;
		}
		
		return $query;
	}
	
	//select * from pg_stat_user_indexes where relname='cms_admin';
	
	protected function queryPKey($tablename)
	{
		//$sql = "sp_columns $tablename";
		$sql = "select a.table_schema,
				a.table_name,
				b.constraint_name,
				a.ordinal_position as position,
				a.column_name as key_column
				from information_schema.table_constraints b
				join information_schema.key_column_usage a 
				on a.constraint_name = b.constraint_name
				and a.constraint_schema = b.constraint_schema
				and a.constraint_name = b.constraint_name
				where b.constraint_type = 'PRIMARY KEY'
				and a.table_schema='public' and 
				a.table_name='$tablename'";
		
		$res = $this->query($sql);		
		$pkeydb = array();
		if ($res) {
			while ($v = $this->fetch_array($res)) {
				$name = $v['key_column'];
				$pkeydb[$name] = $v;
			}			
			$this->free_result($res);	
		}
		
		//查主键
		return $pkeydb;
	}
	
	
	
	protected function parseDataType($dtname)
	{
		switch ($dtname) {
			case 'int4':
				$type = 'int';
				break;
			case 'bigint':
				$type = 'bigint';
				break;
			default:
				$type = $dtname;
				break;
		}
		return $type;			
	}
	
	/*
	 * 
	array(44) {
	 ["table_catalog"]=>
	 string(8) "postgres"
	 ["table_schema"]=>
	 string(6) "public"
	 ["table_name"]=>
	 string(9) "cms_admin"
	 ["column_name"]=>
	 string(2) "id"
	 ["ordinal_position"]=>
	 string(1) "1"
	 ["column_default"]=>
	 NULL
	 ["is_nullable"]=>
	 string(2) "NO"
	 ["data_type"]=>
	 string(7) "integer"
	 ["character_maximum_length"]=>
	 NULL
	 ["character_octet_length"]=>
	 NULL
	 ["numeric_precision"]=>
	 string(2) "32"
	 ["numeric_precision_radix"]=>
	 string(1) "2"
	 ["numeric_scale"]=>
	 string(1) "0"
	 ["datetime_precision"]=>
	 NULL
	 ["interval_type"]=>
	 NULL
	 ["interval_precision"]=>
	 NULL
	 ["character_set_catalog"]=>
	 NULL
	 ["character_set_schema"]=>
	 NULL
	 ["character_set_name"]=>
	 NULL
	 ["collation_catalog"]=>
	 NULL
	 ["collation_schema"]=>
	 NULL
	 ["collation_name"]=>
	 NULL
	 ["domain_catalog"]=>
	 NULL
	 ["domain_schema"]=>
	 NULL
	 ["domain_name"]=>
	 NULL
	 ["udt_catalog"]=>
	 string(8) "postgres"
	 ["udt_schema"]=>
	 string(10) "pg_catalog"
	 ["udt_name"]=>
	 string(4) "int4"
	 ["scope_catalog"]=>
	 NULL
	 ["scope_schema"]=>
	 NULL
	 ["scope_name"]=>
	 NULL
	 ["maximum_cardinality"]=>
	 NULL
	 ["dtd_identifier"]=>
	 string(1) "1"
	 ["is_self_referencing"]=>
	 string(2) "NO"
	 ["is_identity"]=>
	 string(2) "NO"
	 ["identity_generation"]=>
	 NULL
	 ["identity_start"]=>
	 NULL
	 ["identity_increment"]=>
	 NULL
	 ["identity_maximum"]=>
	 NULL
	 ["identity_minimum"]=>
	 NULL
	 ["identity_cycle"]=>
	 string(2) "NO"
	 ["is_generated"]=>
	 string(5) "NEVER"
	 ["generation_expression"]=>
	 NULL
	 ["is_updatable"]=>
	 string(3) "YES"
	}
	
	*/
	public function queryFields($tablename)
	{
		
		//查主键
		$pkeydb = $this->queryPKey($tablename);
		
		$sql = "SELECT* FROM information_schema.columns WHERE table_name ='$tablename'";
		$res = $this->query($sql);
		
		
		$fdb = array();
		if ($res) {
			$strtypes = array('char' => true ,'varchar'=>true, 'text'=>true, 'tinytext'=>true );
			
			while ($v = $this->fetch_array($res)) {
				//var_dump($v);
				
				$name = $v['column_name'];
				$is_primary_key = isset($pkeydb[$name])?true:false;	
				
				$type = $this->parseDataType($v['udt_name']);
				$length = intval($v['character_maximum_length']);
				
				$is_string = isset($strtypes[$type])?true:false;
				$is_null = ($v['is_nullable'] == 'YES')?true:false;
				
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
	
	
	public function get_one( $sql )
	{
		$query = $this->query($sql, 'U_B');
		if (!$query)
			return false;		
			
		$rs = pg_fetch_assoc($query);		
		@pg_free_result($query);
		
		return $rs;
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
	
		
	protected function buildLimitSQL($tablename, $where, $orderby, &$params)
	{
		$limit = "";
		
		$page_size =  $params['page_size'];
		$start =  $params['start'];

		$limit = " LIMIT " . $page_size. " OFFSET " .$start;
		
		$sql = "select * from $tablename $where $orderby $limit";
		//var_dump($sql);
		return $sql;
	}
	
	//更新
	public function exec($sql) 
	{
		$res = $this->query($sql);		
		if ($res) 
			@pg_free_result($res);
				
		return true;
	}		
	
	public function insert($tablename, $params) 
	{
		$keys = array_keys($params);
		$keys = implode(",", $keys);		
		$values = implode("','", $params);
		
		$sql = "insert into $tablename ($keys) values ('$values')";
		
		$res = $this->exec($sql);
		
		return $res;		
	}
	
	
	public function update($tablename, $pkey, $id, $params) 
	{
		$k2v = array();
		foreach ($params as $k=>$v) {
			$k2v[] = "$k='$v'";
		}
		
		$keyvals = implode(',', $k2v);
		$sql = "update $tablename set $keyvals where $pkey=$id";
		
		$res = $this->exec($sql);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "update failed!sql=$sql", $params);
		}
		
		return $res;
	}
	
	
	
	function fetch_array($query, $result_type = PGSQL_ASSOC) 
	{
		return pg_fetch_array($query, null, $result_type);
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
		return pg_free_result($query);
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
	
	function get_version()
	{
		return mysql_get_server_info($this->_link);
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
		//var_dump($fields); exit;
		
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
	
	
	
	
	//服务器信息
	function database_space()
	{
		$o_size = 0;
		$query = mysql_query("SHOW TABLE STATUS");
		while ($rs = $this->fetch_array($query)) {
			$o_size = $o_size + $rs['Data_length'] + $rs['Index_length'];
		}
		
		$o_size	 = nformat_size($o_size);
		return $o_size;		
	}
	
	/* ==========================================
	 * SQL SCRIPTS HELPER FUNCTIONS
	 * ==========================================*/
	protected function importSql($sqlinfo)
	{
		switch ($sqlinfo['type']) {
			case 'createtable':
			
			case 'droptable':
			case 'createview':
			case 'insertinto':
			case 'data':
				break;
			default:
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "unknow sql type '{$v['type']}'!");
				break;
		}
		
		$sql = $sqlinfo['sql'];
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
			rlog('$pre='.$pre);
			if ($pre == "drop" || $pre == "crea")
			{
				rlog($v);
				$res = $this->query($v);
			} else {
				rlog('unknown sql='.$v);
			}
		}
		return $res;				
	}
	
	function get_last_error()
	{
		return mysql_error($this->_link);
	}
	
	
	function database_exists($dbname)
	{
		$sql = "show databases like '$dbname%';";
		$h = $this->query($sql);
		if (mysql_num_rows($h) ==1 )
		{
			return true;
		}
		return false;	
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
	
	
	//库操作
	function select_database($dbname = null)
	{
		if ($dbname == null)
		{
			$dbname = $this->_options['dbname'];
		}
		
		if (!@mysql_select_db($dbname, $this->_link))
		{
			return false;
		}
		
		return true;
	}
	
	function drop_database($dbname)
	{
		if ($this->database_exists($dbname) === false )
		{
			return true;
		}
		
		$sql = "drop database $dbname";		
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
	
	function create_database($dbname, $exists_drop=false)
	{
		if ($exists_drop === true)
		{
			$this->drop_database($dbname);
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

	

	public function get_fields($table_name)
	{
		$sql = "SHOW FULL FIELDS FROM $table_name";
		$res = $this->query_noerror($sql);

		$fdb = array();
		if ($res) {
			$strtypes = array('char' => true ,'varchar'=>true, 'text'=>true, 'tinytext'=>true );

			while ($v = $this->fetch_array($res)) {
				if ($v['Key'] == 'PRI') 
					$v['is_primary_key'] = true;

				$fieldtype = $v['Type'];
				$pos = strpos($fieldtype, '(');
				if ($pos !== false) {
					$length = intval(substr($fieldtype, $pos+1));
		  			$fieldtype = substr($fieldtype, 0, $pos);
		  		} else {
		  			$length = 0;
		  		}
				if (isset($strtypes[$fieldtype])) {
					$v['is_string'] = true;
				} else {
					$v['is_string'] = false;
				}
				$v['fieldtype'] = $fieldtype;
				$v['length'] = $length;

				$fdb[$v['Field']] = $v;
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
		
		return pg_escape_string($data);
	}
	
	public function set_names()
	{
		mysql_query("SET character_set_connection=".$this->_options['dbcharset'].", character_set_results=".$this->_options['dbcharset'].", character_set_client=binary", $this->_link);		
	}
	
	
	
	//关闭数据库
	protected function close()
	{
		pg_close($this->_link);
		$this->_link = 0;
	}	
	
	//数据出现，中断处理
	protected function halt($msg='') 
	{
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "PSQL halt!", $msg, pg_last_error(), $this->_options);		
		return false;
	}
	
}
