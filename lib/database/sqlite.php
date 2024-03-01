<?php
/**
 * @file
 *
 * @brief 
 * SQLite3 数据库管理
 *
 * eg: https://www.php.net/manual/zh/class.sqlite3.php

 * 
 * 
 * 
 */
class SqliteDatabase extends CDatabase
{
	protected $_dbrootdir = '';
	function __construct($name, $options)
	{
		$this->_dbrootdir = RPATH_CONFIG_DATA;
		if (!is_dir($this->_dbrootdir))
			mkdir($this->_dbrootdir);
			
		parent::__construct($name, $options);		
	}
	
	function SqliteDatabase($name, $options)
	{
		$this->__construct($name, $options);
	}
		
	public function connect()
	{
		if ($this->_link)
			return true;
		
		$link = new CSqlite();
		if (!$link) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "new CSqlite failed!");
			return false;
		} 
		
		$dbfile = str_replace(DS,'/', $this->_dbrootdir.DS.$this->_options['dbname'].'.db');
		
		$res = $link->open($dbfile);	
		
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
		
		$this->_options['dbname'] = $dbname;
		$this->close();
		$res = $this->connect();
				
		return $res;
	}
	
	
	public function db_drop($dbname)
	{
		if (!$this->db_exists($dbname))
		{
			return true;
		}
		
		unlink($this->_dbrootdir.DS.$dbname);
		
		return true;
	}
	
	public function db_create($dbname, $exists_drop=false)
	{
		if ($exists_drop === true)
		{
			$this->db_drop($dbname);
		}
		
		$this->close();
		$this->_options['dbname'] = $dbname;
		$res = $this->connect();
		
		return true;
	}
	
	protected function dbfile($dbname)
	{
		!$dbname && $dbname = $this->_options['dbname'];
		$dbfile = str_replace(DS,'/', $this->_dbrootdir.DS.$dbname.'.db');
		
		return $dbfile;
	}
	
	public function db_space($dbname='')
	{
		$file = $this->dbfile($dbname);
		$o_size = filesize($file);
		
		$o_size	 = nformat_size($o_size);
		return $o_size;		
	}
	
	public function db_exists($dbname)
	{
		return file_exists($this->_dbrootdir.DS.$dbname);		
	}
	
	/* ==========================================
	 * TABLE HELPER FUNCTIONS
	 * 表操作
	 * ========================================== */
		
	//查询
	public function query($sql, $method = '') 
	{
		$sql = $this->_prefix_replace($sql);
			
		$query = $this->_link->query($sql);
				
		$this->query_num++;
		if (!$query) 
		{
			$this->halt('Query Error: ' . $sql);
			return false;
		}
		
		return $query;
	}
	
	public function queryFields($table_name)
	{
		$sql = "select * from sqlite_master where type='table' and tbl_name='$table_name'";
		
		$res = $this->query($sql);
		
		
		$fdb = array();
		if ($res) {
			$strtypes = array('char' => true ,'varchar'=>true, 'text'=>true, 'tinytext'=>true );
			$sql = "";
			while ($v = $this->fetch_array($res)) {
				$sql = $v['sql'];
				break;
			}
			$tdb = explode("\n", $sql);
			
			$tableinfo = $this->parseCreateTableSql($tdb);
			$_fdb = $tableinfo['fdb'];
			$pkey = $tableinfo['pkey'];			
			$fdb = array();
			
			foreach ($_fdb as $key=>$v) {
										
				$name = $v['name'];
				$is_primary_key = isset($v['is_primary_key']) ? $v['is_primary_key']: (($pkey == $name)?true:false);
				
				$type = $v['type'];
								
				$is_string = isset($strtypes[$type])?true:false;
				$is_null = $v['null']?true:false;
				
				$v['is_primary_key'] = $is_primary_key;
				$v['is_field'] = true;
				$v['is_string'] = $is_string;
				$v['is_null'] = $is_null;
				$v['required'] = !$is_null;
				
				$fdb[$name] = $v;
				//var_dump($v);
				
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
		
		while($res = $query->fetchArray(SQLITE3_ASSOC) ) {
			break;		
		}
		
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
	
	function exec($sql) 
	{
		$sql = $this->_prefix_replace($sql);
		
		$res = $this->_link->exec($sql);
		if (!$res){
			$this->halt('Update Error: ' . $this->_link->lastErrorMsg());
		} 
				
		$this->query_num++;
		
		return $res;
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
	
	
	function fetch_array($query, $result_type = SQLITE3_ASSOC) 
	{
		return $query->fetchArray(SQLITE3_ASSOC);
	}
	
	function affected_rows() 
	{
		return mysql_affected_rows($this->_link);
	}
	
	function num_rows($query) 
	{
		return $query->rowCount();
	}
	
	function free_result($query) 
	{
		return false;
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
		return $this->_link->version();
	}	
	
	//关闭数据库
	public function close()
	{
		$this->_link->close();
		$this->_link = 0;
	}	
	
	//数据出现，中断处理
	protected function halt($msg='') 
	{
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "PSQL halt!error=".$this->_link->lastErrorMsg());		
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
	
	/* ==========================================
	 * SQL SCRIPTS HELPER FUNCTIONS
	 * ==========================================*/
	
	/**
	 * buildCreateTableSql
		
	eg:
	Array
	(
	   [tableinfo] => Array
	       (
	           [name] => cms_server
	           [ifnotexists] =>
	           [fdb] => Array
	               (
	                   [0] => Array
	                       (
	                           [name] => id
	                           [null] =>
	                           [type] => int
	                           [length] => 0
	                       )
		
	                   [1] => Array
	                       (
	                           [name] => name
	                           [null] =>
	                           [type] => varchar
	                           [length] => 64
	                       )
		
	                   [2] => Array
	                       (
	                           [name] => description
	                           [null] => 1
	                           [type] => text
	                           [length] => 0
	                       )
		
	                   [3] => Array
	                       (
	                           [name] => ip
	                           [null] => 1
	                           [type] => varchar
	                           [length] => 64
	                       )
		
	                   [4] => Array
	                       (
	                           [name] => webrooturl
	                           [null] => 1
	                           [type] => varchar
	                           [length] => 128
	                       )
		
	                   [5] => Array
	                       (
	                           [name] => rtmprooturl
	                           [null] => 1
	                           [type] => varchar
	                           [length] => 128
	                       )
		
	                   [6] => Array
	                       (
	                           [name] => hlsrooturl
	                           [null] => 1
	                           [type] => varchar
	                           [length] => 128
	                       )
		
	                   [7] => Array
	                       (
	                           [name] => vodrooturl
	                           [null] => 1
	                           [type] => varchar
	                           [length] => 128
	                       )
		
	                   [8] => Array
	                       (
	                           [name] => ts
	                           [null] =>
	                           [type] => bigint
	                           [length] => 0
	                       )
		
	                   [9] => Array
	                       (
	                           [name] => flags
	                           [null] =>
	                           [type] => int
	                           [length] => 0
	                           [default] => 0,
	                       )
		
	                   [10] => Array
	                       (
	                           [name] => status
	                           [null] =>
	                           [type] => int
	                           [length] => 0
	                           [default] => 0,
	                       )
		
	               )
		
	           [index] => Array
	               (
	                   [0] => Array
	                       (
	                           [indextype] => primary
	                           [indexfields] => id
	                       )
		
	               )
		
	           [pkey] => id
	       )
		
	   [type] => createtable
	   [sql] => create table cms_server(
	id int not null,
	name varchar(64) not null,
	description text null,
	ip varchar(64) null,
	webrooturl varchar(128) null,
	rtmprooturl varchar(128) null,
	hlsrooturl varchar(128) null,
	vodrooturl varchar(128) null,
	ts bigint not null,
	flags int not null default 0,
	status int not null default 0,
	primary key(id)
	)
	)
		
		
	*/
	
	protected function buildCreateTableSql($sqlinfo)
	{
		// default
		$tableinfo = $sqlinfo['tableinfo'];
		$name = $tableinfo['name'];
		$fdb = $tableinfo['fdb'];
		$sql = "create table $name ( \n";
		foreach ($fdb as $key=>$v) {
			$sql .= " $v[name] ".($v['length'] > 0?$v['type'].'('.$v['length'].')':$v['type']).' '.($v['null']?' null ': 'not null');
			$sql .= isset($v['default'])?' default '.$v['default']:' ';
			
			$sql .= $tableinfo['pkey'] == $v['name']?' primary key ':' ';
			
			$sql .= ",\n";			
		}
		
		$sql = rtrim($sql, ",\n");
		$sql.=")\n";
		
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "sql=$sql");
		
		return $sql;
		
	}
	
	
	protected function importSql($sqlinfo)
	{
		switch ($sqlinfo['type']) {
			case 'createtable':
				$sql = $this->buildCreateTableSql($sqlinfo);
				break;
			case 'droptable':
			case 'createview':
			case 'insertinto':
			case 'data':
			default:
				$sql = $sqlinfo['sql'];
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "unknow sql type '{$v['type']}'!");
				break;
		}

		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $sqlinfo);
		$res =  $this->query($sql);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call query failed!sql=$sql");
			return false;			
		}		
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
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, '$pre='.$pre);
			if ($pre == "drop" || $pre == "crea")
			{
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $v);
				$res = $this->query($v);
			} else {
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, 'unknown sql='.$v);
			}
		}
		return $res;				
	}
	
	function get_last_error()
	{
		$errorCode =  $this->_link->lastErrorCode();
		$errorInfo =  $this->_link->lastErrorMsg();
		return $errorInfo.'('.$errorCode.')';
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
		
		return SQLite3::escapeString($data); 
	}
	
	public function truncate($tablename) 
	{
		$sql = "delete from $tablename";
		
		$res = $this->exec($sql);	
		
		return $res;	
	}
	
}
