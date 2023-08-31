<?php
/**
 * @file
 *
 * @brief 
 * MSSQL 数据库管理
 *
 */
class MssqlDatabase extends CDatabase
{
	
	function __construct($name,  $options )
	{
		parent::__construct($name, $options);
	}
	
	function MssqlDatabase($name,  $options )
	{
		$this->__construct($name, $options);
	}
		
	public function connect()
	{
		if ($this->_link)
			return true;
			
/*
$serverName = "serverName\\sqlexpress"; //serverName\instanceName
$connectionInfo = array( "Database"=>"dbName", "UID"=>"userName", "PWD"=>"password");
$conn = sqlsrv_connect( $serverName, $connectionInfo);*/

		$serverName =$this->_options['dbhost'];		
		$connectionInfo = array( "Database"=>$this->_options['dbname'], 
				"UID"=>$this->_options['dbuser'], 
				"PWD"=>$this->_options['dbpassword']);		
		$link = sqlsrv_connect($serverName, $connectionInfo);
		if (!$link) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "Connect to MSSQL failed!", $connectionInfo, sqlsrv_errors());
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
		
		$sql = "use $dbname;";
		
		$res = sqlsrv_query($this->_link, $sql);
		if (!$res) {
			$this->halt("select db '$dbname' failed!");
			return false;
		}
		@sqlsrv_free_stmt($res);		
		return true;
	}
	
	
	public function db_drop($dbname)
	{
		if (!$this->db_exists($dbname))
		{
			return true;
		}
		
		$sql = "drop database $dbname";		
		
		$query = sqlsrv_query($this->_link, $sql);
		
		if (!$query) 
		{
			$this->halt("drop database failed!sql=".$sql);
			return false;
		}
		
		@sqlsrv_free_stmt($query);
		return true;
	}
	
	/**
	 * 创建DB
	*/
	public function db_create($dbname, $exists_drop=false)
	{
		if ($exists_drop === true)
		{
			$this->db_drop($dbname);
		}
		
		$sql = "create database $dbname";		
		$query = sqlsrv_query($this->_link, $sql);
		if (!$query) {
			$this->halt("create database failed!sql=".$sql);
			return false;
		}
		@sqlsrv_free_stmt($query);
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
		$sql = "select db_id('$dbname')";
		$result = $this->query($sql);
			
		if (!$result)
			return false;
			
		$nr = 0;
		while($row = sqlsrv_fetch_array($result)) {
			$nr ++ ;
		}		
		@sqlsrv_free_stmt($result);
		return $nr > 0;	
	}
	
	
	function select_db($dbname)
	{
		return @mysql_select_db($dbname);
	}
	
	/* ==========================================
	 * TABLE HELPER FUNCTIONS
	 * 表操作
	 * ========================================== */
	
	
	protected function queryPKey($tablename)
	{
		//$sql = "sp_columns $tablename";
		$sql = "sp_pkeys $tablename";
		
		$res = $this->query($sql);
		
		$pkeydb = array();
		if ($res) {
			while ($v = $this->fetch_array($res)) {
				$name = $v['COLUMN_NAME'];
				$pkeydb[$name] = $v;
			}			
			$this->free_result($res);	
		}
		
		//查主键
		return $pkeydb;
	}
	
	/**
	 * 查询表结构
	 * query table structure
	 *
	 * @param mixed $tablename This is a description
	 * @return mixed This is the return value description
	 *
	 * array(19) {
	 ["TABLE_QUALIFIER"]=>
	 string(5) "rcdb7"
	 ["TABLE_OWNER"]=>
	 string(3) "dbo"
	 ["TABLE_NAME"]=>
	 string(9) "cms_admin"
	 ["COLUMN_NAME"]=>
	 string(2) "id"
	 ["DATA_TYPE"]=>
	 int(4)
	 ["TYPE_NAME"]=>
	 string(3) "int"
	 ["PRECISION"]=>
	 int(10)
	 ["LENGTH"]=>
	 int(4)
	 ["SCALE"]=>
	 int(0)
	 ["RADIX"]=>
	 int(10)
	 ["NULLABLE"]=>
	 int(0)
	 ["REMARKS"]=>
	 NULL
	 ["COLUMN_DEF"]=>
	 NULL
	 ["SQL_DATA_TYPE"]=>
	 int(4)
	 ["SQL_DATETIME_SUB"]=>
	 NULL
	 ["CHAR_OCTET_LENGTH"]=>
	 NULL
	 ["ORDINAL_POSITION"]=>
	 int(1)
	 ["IS_NULLABLE"]=>
	 string(3) "NO "
	 ["SS_DATA_TYPE"]=>
	 int(56)
	 
	 */

	public function queryFields($tablename)
	{
		//查主键
		$pkeydb = $this->queryPKey($tablename);
		
		$sql = "sp_columns $tablename";
		
		$res = $this->query($sql);
				
		$fdb = array();
		if ($res) {
			$strtypes = array('char' => true ,'varchar'=>true, 'text'=>true, 'tinytext'=>true );
			//$next_result = sqlsrv_next_result($res);
						
			while ($v = $this->fetch_array($res)) {
				
				$name = $v['COLUMN_NAME'];
				$is_primary_key = isset($pkeydb[$name])?true:false;	
								
				$type = $v['TYPE_NAME'];
				$length = $v['LENGTH'];
				$is_string = isset($strtypes[$type])?true:false;
				$is_null = ($v['IS_NULLABLE'] == 'YES')?true:false;
				
				$v['name'] = $name;
				$v['type'] = $type;
				$v['length'] = $length;
				
				$v['is_primary_key'] = $is_primary_key;
				$v['is_string'] = $is_string;
				$v['is_null'] = $is_null;
				$v['required'] = !$is_null;
				$v['is_field'] = true;
				
				$fdb[$name] = $v;
			}			
			$this->free_result($res);	
		}
		
		return $fdb;
	}	
	
	
	
	
	//查询
	public function query($sql, $method = '') 
	{
		if (!$this->_link)
			$this->connect();
					
		$sql = $this->_prefix_replace($sql);
			
		$query = sqlsrv_query($this->_link, $sql);
		
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
		$query = $this->query($sql);
		if (!$query)
			return false;		
			
		$res = sqlsrv_fetch_array($query, SQLSRV_FETCH_ASSOC);		
		@sqlsrv_free_stmt($query);
		
		return $res;
	}
		
	
	function get_max_id($table, $field)
	{
		$sql = "select max($field) as maxid from $table";
		
		$res = $this->get_one($sql);
		if (!$res)
		{
			return 1;
		}
		else
		{
			return intval($res['maxid']) +1;
		}
	}
	
	protected function buildLimitSQL($tablename, $where, $orderby, &$params)
	{
		$limit = "";
		
		$page =  $params['page'];
		$page_size =  $params['page_size'];
		$start =  $params['start'];
		$pkey =  $params['pkey'];
		
		/*
		select top 10 *
		from cms_admin 
		where (id > (select max(id) from (select top 20 id from cms_admin order by id) as t)) 
		order by id*/
		
		if ($page == 1) {
			$sql = "select top $page_size * from $tablename $where $orderby ";
		} else {
			$n = ($page - 1)*$page_size;
			//检查排序是否为主键倒序
			if ($orderby) {
				$order_dir = $params['__orderby']['order_dir'];				
				$order_field = $params['__orderby']['order_field'];						
			} else {
				$order_dir = 'asc';				
				$order_field = $pkey;	
								
				$orderby = "order by $order_field $order_dir";	
			}
						
									
			if ($where) {
				if ($order_dir == 'desc') {
					$where .= " and ($order_field < (select min($order_field) from (select top $n $order_field from $tablename $orderby) as t)) ";
				} else {
					$where .= " and ($order_field > (select max($order_field) from (select top $n $order_field from $tablename $orderby) as t)) ";
				}
			} else {
				if ($order_dir == 'desc') {
					$where .= " where $order_field < (select min($order_field) from (select top $n $order_field from $tablename $orderby) as t)";
				} else {
					$where .= " where $order_field > (select max($order_field) from (select top $n $order_field from $tablename $orderby) as t)";
				}
			}
										
			$sql = "select top $page_size * from $tablename $where $orderby ";
		}
		//var_dump($sql);
		return $sql;
	}
	
	
	//更新
	function exec($sql) 
	{
		$sql = $this->_prefix_replace($sql);
		
		$query = sqlsrv_query($this->_link, $sql);
		
		$this->query_num++;
		
		if (!$query) 
		{
			$this->halt('Update Error: ' . $sql);
			return false;
		}
		@sqlsrv_free_stmt($query);	
		
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
	
	function fetch_array($query, $result_type = SQLSRV_FETCH_ASSOC) 
	{
		return sqlsrv_fetch_array($query, $result_type);
	}
	
	function affected_rows() 
	{
		return sqlsrv_affected_rows($this->_link);
	}
	
	function num_rows($query) 
	{
		$rows = mysql_num_rows($query);
		return $rows;
	}
	
	function free_result($query) 
	{
		return sqlsrv_free_stmt($query);
	}
	
	function exists($sql)
	{
		$query = $this->Query($sql);
		if (!$query)
			return false;
			
		$row = $this->num_rows($query);
		return !!$row;
	}
		
	function get_version()
	{
/*
array(3) {
  ["CurrentDatabase"]=>
  string(5) "rcdb7"
  ["SQLServerVersion"]=>
  string(10) "09.00.1399"
  ["SQLServerName"]=>
  string(4) "WIN7"
}
*/		$res = sqlsrv_server_info($this->_link);
		
		return $res;
	}	
	
	//关闭数据库
	function close()
	{
		sqlsrv_close($this->_link);
		$this->_link = 0;
	}	
	
	//数据出现，中断处理
	function halt($msg='') 
	{
		$error = sqlsrv_errors();
				
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, $msg, $error);
		
		return false;
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
	
	
	/**
	 * 创建MSSQL创建表SQL
	 *
	 * @param mixed $sqlinfo This is a description
	 * @return mixed This is the return value description
	 *
	 * -- Disk-Based CREATE TABLE Syntax
	 * https://docs.microsoft.com/en-us/sql/t-sql/statements/create-table-transact-sql?view=sql-server-ver15
	 * 
	CREATE TABLE
	   { database_name.schema_name.table_name | schema_name.table_name | table_name }
	   [ AS FileTable ]
	   ( {   <column_definition>
	       | <computed_column_definition>
	       | <column_set_definition>
	       | [ <table_constraint> ] [ ,... n ]
	       | [ <table_index> ] }
	         [ ,...n ]
	         [ PERIOD FOR SYSTEM_TIME ( system_start_time_column_name
	            , system_end_time_column_name ) ]
	     )
	   [ ON { partition_scheme_name ( partition_column_name )
	          | filegroup
	          | "default" } ]
	   [ TEXTIMAGE_ON { filegroup | "default" } ]
	   [ FILESTREAM_ON { partition_scheme_name
	          | filegroup
	          | "default" } ]
	   [ WITH ( <table_option> [ ,...n ] ) ]
	[ ; ]
	 
	<column_definition> ::=
	column_name <data_type>
	   [ FILESTREAM ]
	   [ COLLATE collation_name ]
	   [ SPARSE ]
	   [ MASKED WITH ( FUNCTION = 'mask_function') ]
	   [ [ CONSTRAINT constraint_name ] DEFAULT constant_expression ]
	   [ IDENTITY [ ( seed , increment ) ]
	   [ NOT FOR REPLICATION ]
	   [ GENERATED ALWAYS AS { ROW | TRANSACTION_ID | SEQUENCE_NUMBER } { START | END } [ HIDDEN ] ]
	   [ NULL | NOT NULL ]
	   [ ROWGUIDCOL ]
	   [ ENCRYPTED WITH
	       ( COLUMN_ENCRYPTION_KEY = key_name ,
	         ENCRYPTION_TYPE = { DETERMINISTIC | RANDOMIZED } ,
	         ALGORITHM = 'AEAD_AES_256_CBC_HMAC_SHA_256'
	       ) ]
	   [ <column_constraint> [, ...n ] ]
	   [ <column_index> ]
	 
	<data_type> ::=
	[ type_schema_name. ] type_name
	   [ ( precision [ , scale ] | max |
	       [ { CONTENT | DOCUMENT } ] xml_schema_collection ) ]
	 
	<column_constraint> ::=
	[ CONSTRAINT constraint_name ]
	{ 
	  { PRIMARY KEY | UNIQUE }
	       [ CLUSTERED | NONCLUSTERED ]
	       [
	           WITH FILLFACTOR = fillfactor
	         | WITH ( <index_option> [ , ...n ] )
	       ]
	       [ ON { partition_scheme_name ( partition_column_name )
	           | filegroup | "default" } ]
	 
	 | [ FOREIGN KEY ]
	       REFERENCES [ schema_name. ] referenced_table_name [ ( ref_column ) ]
	       [ ON DELETE { NO ACTION | CASCADE | SET NULL | SET DEFAULT } ]
	       [ ON UPDATE { NO ACTION | CASCADE | SET NULL | SET DEFAULT } ]
	       [ NOT FOR REPLICATION ]
	 
	 | CHECK [ NOT FOR REPLICATION ] ( logical_expression )
	}
	 
	<column_index> ::=
	INDEX index_name [ CLUSTERED | NONCLUSTERED ]
	   [ WITH ( <index_option> [ ,... n ] ) ]
	   [ ON { partition_scheme_name (column_name )
	        | filegroup_name
	        | default
	        }
	   ]
	   [ FILESTREAM_ON { filestream_filegroup_name | partition_scheme_name | "NULL" } ]
	 
	<computed_column_definition> ::=
	column_name AS computed_column_expression
	[ PERSISTED [ NOT NULL ] ]
	[
	   [ CONSTRAINT constraint_name ]
	   { PRIMARY KEY | UNIQUE }
	       [ CLUSTERED | NONCLUSTERED ]
	       [
	           WITH FILLFACTOR = fillfactor
	         | WITH ( <index_option> [ , ...n ] )
	       ]
	       [ ON { partition_scheme_name ( partition_column_name )
	       | filegroup | "default" } ]
	 
	   | [ FOREIGN KEY ]
	       REFERENCES referenced_table_name [ ( ref_column ) ]
	       [ ON DELETE { NO ACTION | CASCADE } ]
	       [ ON UPDATE { NO ACTION } ]
	       [ NOT FOR REPLICATION ]
	 
	   | CHECK [ NOT FOR REPLICATION ] ( logical_expression )
	]
	 
	<column_set_definition> ::=
	column_set_name XML COLUMN_SET FOR ALL_SPARSE_COLUMNS
	 
	<table_constraint> ::=
	[ CONSTRAINT constraint_name ]
	{
	   { PRIMARY KEY | UNIQUE }
	       [ CLUSTERED | NONCLUSTERED ]
	       (column [ ASC | DESC ] [ ,...n ] )
	       [
	           WITH FILLFACTOR = fillfactor
	          | WITH ( <index_option> [ , ...n ] )
	       ]
	       [ ON { partition_scheme_name (partition_column_name)
	           | filegroup | "default" } ]
	   | FOREIGN KEY
	       ( column [ ,...n ] )
	       REFERENCES referenced_table_name [ ( ref_column [ ,...n ] ) ]
	       [ ON DELETE { NO ACTION | CASCADE | SET NULL | SET DEFAULT } ]
	       [ ON UPDATE { NO ACTION | CASCADE | SET NULL | SET DEFAULT } ]
	       [ NOT FOR REPLICATION ]
	   | CHECK [ NOT FOR REPLICATION ] ( logical_expression )
		
	<table_index> ::=
	{  
	   {  
	     INDEX index_name  [ UNIQUE ] [ CLUSTERED | NONCLUSTERED ]
	        (column_name [ ASC | DESC ] [ ,... n ] )
	   | INDEX index_name CLUSTERED COLUMNSTORE
	   | INDEX index_name [ NONCLUSTERED ] COLUMNSTORE ( column_name [ ,... n ] )
	   }
	   [ WITH ( <index_option> [ ,... n ] ) ]
	   [ ON { partition_scheme_name ( column_name )
	        | filegroup_name
	        | default
	        }
	   ]
	   [ FILESTREAM_ON { filestream_filegroup_name | partition_scheme_name | "NULL" } ]
	 
	}
		
	<table_option> ::=
	{  
	   [ DATA_COMPRESSION = { NONE | ROW | PAGE }
	     [ ON PARTITIONS ( { <partition_number_expression> | <range> }
	     [ , ...n ] ) ] ]
	   [ FILETABLE_DIRECTORY = <directory_name> ]
	   [ FILETABLE_COLLATE_FILENAME = { <collation_name> | database_default } ]
	   [ FILETABLE_PRIMARY_KEY_CONSTRAINT_NAME = <constraint_name> ]
	   [ FILETABLE_STREAMID_UNIQUE_CONSTRAINT_NAME = <constraint_name> ]
	   [ FILETABLE_FULLPATH_UNIQUE_CONSTRAINT_NAME = <constraint_name> ]
	   [ SYSTEM_VERSIONING = ON 
	       [ ( HISTORY_TABLE = schema_name.history_table_name
	         [ , DATA_CONSISTENCY_CHECK = { ON | OFF } ] 
	   ) ] 
	   ]
	   [ REMOTE_DATA_ARCHIVE =
	     {
	       ON [ ( <table_stretch_options> [,...n] ) ]
	       | OFF ( MIGRATION_STATE = PAUSED )
	     }
	   ]   
	   [ DATA_DELETION = ON  
	         { ( 
	            FILTER_COLUMN = column_name,   
	            RETENTION_PERIOD = { INFINITE | number { DAY | DAYS | WEEK | WEEKS 
	                             | MONTH | MONTHS | YEAR | YEARS }
	       ) }  
	   ]
	   [ LEDGER = ON [ ( <ledger_option> [,...n ] ) ] 
	   | OFF 
	   ]
	}
		
	<ledger_option>::= 
	{
	   [ LEDGER_VIEW = schema_name.ledger_view_name  [ ( <ledger_view_option> [,...n ] ) ]
	   [ APPEND_ONLY = ON | OFF ]
	}
		
	<ledger_view_option>::= 
	{
	   [ TRANSACTION_ID_COLUMN_NAME = transaction_id_column_name ]
	   [ SEQUENCE_NUMBER_COLUMN_NAME = sequence_number_column_name ]
	   [ OPERATION_TYPE_COLUMN_NAME = operation_type_id column_name ]
	   [ OPERATION_TYPE_DESC_COLUMN_NAME = operation_type_desc_column_name ]
	}
	 
	<table_stretch_options> ::=
	{  
	   [ FILTER_PREDICATE = { NULL | table_predicate_function } , ]
	     MIGRATION_STATE = { OUTBOUND | INBOUND | PAUSED }
	}   
	 
	<index_option> ::=
	{
	   PAD_INDEX = { ON | OFF }
	 | FILLFACTOR = fillfactor
	 | IGNORE_DUP_KEY = { ON | OFF }
	 | STATISTICS_NORECOMPUTE = { ON | OFF }
	 | STATISTICS_INCREMENTAL = { ON | OFF }
	 | ALLOW_ROW_LOCKS = { ON | OFF }
	 | ALLOW_PAGE_LOCKS = { ON | OFF }
	 | OPTIMIZE_FOR_SEQUENTIAL_KEY = { ON | OFF }
	 | COMPRESSION_DELAY= { 0 | delay [ Minutes ] }
	 | DATA_COMPRESSION = { NONE | ROW | PAGE | COLUMNSTORE | COLUMNSTORE_ARCHIVE }
	      [ ON PARTITIONS ( { partition_number_expression | <range> }
	      [ , ...n ] ) ]
	}
		
	<range> ::=
	<partition_number_expression> TO <partition_number_expression>
	  
	  EG:
	  CREATE TABLE sales.visits (
	   visit_id INT PRIMARY KEY IDENTITY (1, 1),
	   first_name VARCHAR (50) NOT NULL,
	   last_name VARCHAR (50) NOT NULL,
	   visited_at DATETIME,
	   phone VARCHAR(20),
	   store_id INT NOT NULL,
	   FOREIGN KEY (store_id) REFERENCES sales.stores (store_id)
	);
		  
	 */
	protected function buildCreateTableSql($sqlinfo)
	{
		$sql = "create table";		
	}
	
	protected function importSql($sqlinfo)
	{
		switch ($sqlinfo['type']) {
			case 'createtable':
				//$sql = $this->buildCreateTableSql($sqlinfo);
				//break;
			case 'droptable':
			case 'createview':
			case 'insertinto':
			case 'data':
			default:
				$sql = $sqlinfo['sql'];
				break;
		}
		
		$res =  $this->query($sql);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call query failed!sql=$sql");			
			return false;
		}
		
		@sqlsrv_free_stmt($res);			
		return true;
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
		sqlsrv_free_stmt($query);		
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
		
		return $data; //mysql_escape_string($data);
	}
	
	public function set_names()
	{
		mysql_query("SET character_set_connection=".$this->_options['dbcharset'].", character_set_results=".$this->_options['dbcharset'].", character_set_client=binary", $this->_link);		
	}
}
