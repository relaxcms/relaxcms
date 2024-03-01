<?php
/**
 * @file
 *
 * @brief 
 * DATABASE 类
 *
 */

class CDatabase 
{
	protected $_name;
	protected $_options;
	
	protected $_link = 0;
	protected $query_num = 0;
	
	protected $_datatypes = array('char', 'varchar', 'tinytext', 'text', 'mediumtext','longtext',
				'tinyint', 'int', 'smallint','bigint','float', 'double', 'decimal');
				
	protected $_strdatatypes = array('char' => true ,'varchar'=>true, 'text'=>true, 'tinytext'=>true, 'timestamp'=>true );
	
	protected $_last_error = '';	
	
	function __construct($name, $options=array() )
	{
		$this->_name = $name;	
		$this->_options = $options;	
		
		$this->connect();
	}
	
	function CDatabase( $name, $options )
	{
		$this->__construct($name, $options);
	}
	
	static function GetInstance($name, $options=array())
	{
		
		static $instances;
		
		if (!isset( $instances )) 
		{
			$instances = array();
		}
		
		$dbcfg = get_dbconfig($name);
		if (!$dbcfg) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "no db config '$name'!");
			return false;
		}
		
		$dbtype = $dbcfg['dbtype'];
						
		if (!$options) 
			$options = $dbcfg;	
		else 
			$options = array_merge($dbcfg, $options);		
		
		$sig = serialize(array($dbtype, $options));	
		$instance = null;
		
		$classname	= ucfirst($dbtype).'Database';
		if (empty($instances[$sig])) {	
			require_once RPATH_DATABASE.DS.$dbtype.".php";			
			$instance	= new $classname($name, $options);
			$instances[$sig] = $instance;
		} else {
			$instance = $instances[$sig];
			if (!$instance || !$instance->is_connected()) {
				require_once RPATH_DATABASE.DS.$dbtype.".php";
				$instance	= new $classname($name, $options);
				$instances[$sig] = $instance;
			}
		}
		
		return $instance;
	}
	
	public function connect()
	{
		return false;
	}
	
	public function is_connected()
	{
		return $this->_link;
	}
	
	public function reconnect($newdb='')
	{
		return false;
	}
	
	public function getLink()
	{
		return $this->_link;
	}
	
	/* ==========================================
	 * DB HELPER FUNCTIONS
	 * ========================================== */
	
	/**
	 * 选择DB
	 * 相当于 use db
	 *
	 * @param mixed $dbname This is a description
	 * @return mixed This is the return value description
	 *
	 */
	public function db_select($dbname)
	{
		return false;
	}
	
	/**
	 * 创建DB
	*/
	public function db_create($dbname, $exists_drop=false)
	{
		return false;
	}
	
	
	/**
	 * 删除DB
	 *
	 * @param mixed $dbname This is a description
	 * @return mixed This is the return value description
	 *
	 */
	public function db_drop($dbname)
	{
		return false;
	}
	
	
	/**
	 * 查询DB占用空间
	 *
	 * @return mixed This is the return value description
	 *
	 */
	public function db_space($dbname='')
	{
		return false;
	}
	
	public function db_name()
	{
		return $this->_options['dbname'];
	}

	public function db_options()
	{
		return $this->_options;
	}
	
	/**
	 * 查询DB是否存在
	 *
	 * @param mixed $dbname This is a description
	 * @return mixed This is the return value description
	 *
	 */
	public function db_exists($dbname)
	{
		return false;
	}
	
	public function selectdb($dbname)
	{
		return $this->db_select($dbname);
	}
	
	public function usedb($dbname)
	{
		return $this->db_select($dbname);
	}

	public function createdb($dbname)
	{
		return $this->db_create($dbname);
	}
		
	public function dropdb($dbname)
	{
		return $this->db_drop($dbname);
	}
	
	
	public function createUser($params)
	{
		$dbuser = $params['dbuser'];
		$dbpassword = $params['dbpassword'];
		$dbname = $params['dbname'];
		
		//查一下用户是否存在
		/*$sql = "select 1 from user where user='$dbuser'";
		if ($this->exists($sql)) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, 'str_install_dbuser_exists');
			return false;				
		}*/
		
		$sql = "GRANT ALL PRIVILEGES ON $dbname.* TO $dbuser@localhost IDENTIFIED BY '$dbpassword'";
		$res = $this->exec($sql);
		
		$sql = "FLUSH PRIVILEGES";
		$res = $this->exec($sql);
		
		return $res;
	}
	
	public function dropUser($dbuser)
	{
		// drop user sxerm@localhost;
		$sql = "drop user $dbuser@localhost";
		$res = $this->exec($sql);
		
		return $res;
	}
	
	
	public function changePassword($dbuser, $dbpassword)
	{
		$sql = "update user set password=PASSWORD('$dbpassword') where user='$dbuser'";
		$res = $this->exec($sql);
		
		$sql = "flush privileges";
		$res = $this->exec($sql);
		
		return $res;
	}
	
	/* ==========================================
	 * SQL SCRIPTS HELPER FUNCTIONS
	 * ==========================================*/
	
	protected function parseTableName($udb, &$tableinfo)
	{
		//tablename
		$tablename = trim(array_pop($udb));
		if ($tablename == '(') {
			$tablename = trim(array_pop($udb));
		} else {
			$tablename = rtrim($tablename, '('); 
		}
		
		$ifnotexists = false;
		if (isset($udb[2]) && $udb[2]== 'if' && isset($udb[3]) && $udb[3] == 'not') {
			$ifnotexists = true;
		}
		
		$tableinfo['name'] = $tablename;		
		$tableinfo['ifnotexists'] = $ifnotexists;
		$tableinfo['fdb'] = array();		
		$tableinfo['index'] = array();		
	}
	
	protected function parseTableIndex($indextype, $indexfields, &$tableinfo)
	{
		//string(17) "UNIQUE KEY(name),"
		$tableinfo['index'][] = array('indextype'=>$indextype,'indexfields'=>$indexfields);
		if ($indextype == 'primary') {
			$tableinfo['pkey'] = $indexfields;
		}
	}
	
	protected function parseTableEngine($type, $line, $udb, &$tableinfo)
	{
		//)ENGINE=MyISAM DEFAULT CHARSET=utf8;"
		if (strstr($type, 'myisam')) {
			$tableinfo['engine'] = 'myisam';
		} 	
	}
		
	protected function parseTableField($name, $type, $null, $line, $udb, &$tableinfo)
	{
		//password varchar(32) NOT NULL
		$field = array();		
		$field['name'] = $name;
		$field['null'] = $null == 'not'?false:true;
		
		//length
		$pos = strpos($type, '(');
		if ($pos !== false) {
			$length = intval(substr($type, $pos+1));
			$type = substr($type, 0, $pos);
		} else {
			$length = 0;
		}
		$field['type'] = $type;
		$field['length'] = $length;
		
		for ($i=3; $i<count($udb); $i++) {
			$key = strtolower($udb[$i]);
			switch($key) {
				case 'primary':
					$field['is_primary_key'] = true;
					break;
				case 'default':
					$field['default'] = $udb[++$i];
					break;
				case 'comment':
					$field['comment'] = $udb[++$i];
					break;
			}
		}		
		/*if ($field['null'] && isset($udb[3]) && $udb[3] == 'default') {
			$field['default'] = $udb[4];
		} else if (!$field['null']  && isset($udb[4]) && $udb[4] == 'default') {
			$field['default'] = $udb[5];
		}	*/	
		$tableinfo['fdb'][] = $field;
	}
		
	protected function parseCreateTableSql($tdb)
	{
		$tableinfo = array();
		
		//解释
		//$firstline = array_shift($tdb);
		//create table if not exists cms_admin(
		foreach ($tdb as $key=>$v) {
			$v = trim($v);
			$v = str_replace("\t", ' ',$v);
			
			$_udb = explode(' ', $v);
			$udb = array();
			foreach ($_udb as $k2=>$v2) {
				$v2 = trim($v2);
				$v2 = rtrim($v2,',');
				if (!$v2)
					continue;
					
				$udb[] = $v2;	
			}
			
			$f0 = strtolower(trim($udb[0])); //name
			$f1 = strtolower(trim($udb[1])); //type
			$f2 = strtolower(trim($udb[2])); //lenght
			$f3 = strtolower(trim($udb[3])); //null or not null
			
			if ($f0 == 'create' && $f1 == 'table')	{
				$this->parseTableName($udb, $tableinfo);				
			} 			
			//index
			elseif (preg_match_all('#index[\s+]?[\w+]*[\s+]?\((.*)\)#isU', $v, $matchs)) {
				$this->parseTableIndex('index', $matchs[1][0], $tableinfo);
			}			
			//unique
			elseif  (preg_match_all('#unique[\s+]?[\w+]*[\s+]?\((.*)\)#isU', $v, $matchs)) {
				$this->parseTableIndex('unique', $matchs[1][0], $tableinfo);
			}
			//primary
			elseif  (preg_match_all('#primary[\s+]?[\w+]*[\s+]?\((.*)\)#isU', $v, $matchs)) {
				$this->parseTableIndex('primary', $matchs[1][0], $tableinfo);
			} else if ($f0[0] == ')') {//)ENGINE=MyISAM DEFAULT CHARSET=utf8;"
				$field = $this->parseTableEngine($f0, $v, $udb, $tableinfo);	
			}  else {
				$field = $this->parseTableField($f0, $f1, $f2, $v, $udb, $tableinfo);				
			}
		}
		
		return $tableinfo;		
	}
	
	public function parseSQLFile($sqlfile)
	{
		if (!file_exists($sqlfile)) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no sqlfile '$sqlfile'!");
			return array();
		}
		
		$lines = file($sqlfile);
		$first = true;
		$res = true;		
		
		$sqldb = array();
				
		foreach($lines as $key => $value)
		{
			$value = trim($value);
			if (!$value || $value[0] == '#') 
				continue;
			if ($value == "") 
				continue;
				
			if ($first) {
				$value = strim_bom($value);
				$sql = '';
				$tdb = array();
				
				$first = false;
			}
			
			if (substr($value, 0, 3) == "-- ") 
				continue;			
			
			if ( substr($value, -1) == ';' ) //以;结尾
			{
				$sql .= $value;
				$tdb[] = $value;
				$item = array();
								
				if (preg_match("/create\s+table/i", $sql)) {
					$extra = substr(strrchr($sql, ')'), 1);
					$tabtype = substr(strchr($extra, '=') ,1);
					$tabtype = substr($tabtype, 0,  strpos($tabtype,strpos($tabtype,' ') ? ' ' : ';'));
					$sql = str_replace($extra, '', $sql);
					
					$type = 'createtable';
					$item['tableinfo'] = $this->parseCreateTableSql($tdb);
					
				} elseif (preg_match("/create\s+view/i", $sql)) {
					$ttt = 1;
					$type = 'createview';
				} elseif(preg_match("/insert\s+into/i", $sql)) {
					$sql = 'REPLACE '.substr($sql,6);
					$type = 'insertdata';
				} elseif(preg_match("/drop\s+table/i", $sql)) {
					$type = 'droptable';
				} else {
					$type = 'data';
				}				
				
				$item['type'] = $type;
				
				$item['sql'] = rtrim($sql,";");
				
				$sqldb[] = $item;			
				
				$sql = '';
			} 
			else
			{
				$sql .= $value."\n";
				$tdb[] = $value;
			}			
		}
		
		return $sqldb;
	}
	
	
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
		return $sqlinfo['sql'];
	}
	
	protected function importSql($sqlinfo)
	{
		
		return false;
	}
		
	
	/**
	 * 导入脚本
	 *
	 * @param mixed $sqlfile This is a description
	 * @return mixed This is the return value description
	 *
	 */
	public function import($sqlfile)
	{
		$sqldb = $this->parseSQLFile($sqlfile);
		$res = true;
		foreach ($sqldb as $key=>$v) {
			//检查是否存在
			if ($v['type'] == 'createtable') {
				$tname = $v['tableinfo']['name'];
				if ($this->table_exists($tname)) {
					rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "table '$tname' exists!");
					$res = true;
					continue;
				}
			}			
			$res = $this->importSql($v);			
		}
			
		return $res;
	}
	
	/**
	 * 导出脚本
	 *
	 * @param mixed $sqlfile This is a description
	 * @return mixed This is the return value description
	 *
	 */
	public function export()
	{
		return false;		
	}
	
	
	/* ==========================================
	 * TABLE HELPER FUNCTIONS
	 * ==========================================*/
	
	//替换前缀
	protected function _prefix_replace( $sql )
	{
		$dbname = $this->_options['dbname'];
		$pre = $this->_options['prefix'];
				
		$sql = trim( $sql );		
		$sql = str_replace('cms_', $pre, $sql);
		
		return $sql;		
	}
	
	
	
			
	//查询
	function query($sql, $method = '') 
	{
		return false;
	}
	
	function get_one( $sql )
	{
		return false;
	}
	
	function get_max_id($table, $field)
	{
		return false;
	}
	
	public function exec($sql) 
	{
		return false;
	}
	
	public function get($tablename, $pkey, $id) 
	{
		$sql = "select * from $tablename where $pkey='$id'";
		$res = $this->get_one($sql);	
		return $res;		
	}
	
	public function findOne($tablename, $params, $sort=array()) 
	{
		$where = $this->buildFilterSQL($params);	
		$orderby = $this->buildSortSQL($params);	
		
		$field = isset($params['__fields'])?$params['__fields']:'*';
		
		$sql = "select $field from $tablename $where $orderby";
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__,  'sql='.$sql);
		
		
		$res = $this->get_one($sql);	
		
		return $res;		
	}
	
	public function getCount($tablename, $params) 
	{
		$where = $this->buildFilterSQL($params);	
			
		$sql = "select count(*) as total from $tablename $where";	
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__,  'sql='.$sql);
		$res = $this->get_one($sql);
		$total = intval($res['total']);
		return $total;
	}
	
	public function group($tablename, $params=array())
	{
		$where = $this->buildFilterSQL($params);	
		$groupby = $this->buildGroupSQL($params);	
		
		$fn = $params['fn'];
		$field = isset($params['__fields'])?$params['__fields']:'*';
		
		
		$sql = "select $groupby, $fn($field) as val from $tablename $where $groupby";	
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "sql=$sql");
		
		$rows = array();	
		$res = $this->query($sql);
		if ($res) {
			while ($v = $this->fetch_array($res)) {
				$rows[] = $v;
			}
			$this->free_result($res);	
		}
		return $rows;
	}
	
	public function compute($tablename, $fn, $field, $params=array())
	{
		$is_array = false;
		$where = $this->buildFilterSQL($params);
		
		//fields
		$fns='';
		if (is_array($field)) {
			$udb = array();
			foreach($field as $key=>$v) {
				$udb[] = "$fn($key) as $key ";
			}
			$fns = implode(',', $udb);
			$is_array = true;
		} else {
			$fns = "$fn($field) as $field";
		}
		$sql = "select $fns from $tablename $where";		
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, 'sql='.$sql);
		
		$res = $this->get_one($sql);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "compute failed! sql=$sql");
			return false;
		}
		
		//rlog($res);
		if ($is_array)
			return $res;
		else 
			return $res[$field];
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
	
	
	public function update($tablename, $pkey, $id, $params) 
	{
		$k2v = array();
		foreach ($params as $k=>$v) {
			$k2v[] = "`$k`='$v'";
		}
		
		$keyvals = implode(',', $k2v);
		$sql = "update $tablename set $keyvals";
		if ($id !== false)
			$sql .= " where $pkey=$id";
					
		$res = $this->exec($sql);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "update failed!sql=$sql", $params);
		}
		
		return $res;
	}
	
	public function delete($tablename, $params) 
	{
		$where = $this->buildFilterSQL($params);
		$sql = "delete from $tablename $where";
		$res = $this->exec($sql);		
		return $res;
	}
	
	public function truncate($tablename) 
	{
		$sql = "truncate table $tablename";
		
		$res = $this->exec($sql);
			
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "res=$res");
				
		return $res !== false;	
	}
		
	/**
	 * $result_type: MYSQL_ASSOC，MYSQL_NUM 和 MYSQL_BOTH
	*/
	function fetch_array($query, $result_type = MYSQL_ASSOC) 
	{
		return false;
	}
	
	public  function affected_rows() 
	{
		return false;
	}
	
	public function num_rows($query) 
	{
		return false;
	}
	
	protected function free_result($query) 
	{
		return false;
	}
	
	
	//关闭数据库
	public function close()
	{
		return false;
	}	
	
	protected function __error()
	{
		return false;
	}
	
	protected function __errno()
	{
	}
	
	//数据出现，中断处理
	protected function halt($msg='') 
	{
		return false;
	}
	
	public function get_last_error()
	{
		return $this->_last_error;
	}
	
	function table_exists($table)
	{
		return false;
	}
	
	public function exists($sql)
	{
		$query = $this->query($sql);
		if (!$query)
			return false;
		
		$row = $this->num_rows($query);
		return !!$row;
	}
	
	protected function is_string($type)
	{
		return isset($this->_strdatatypes[$type])?true:false;
	}
	
	/**
	 * This is method buildFilterSQL
	 *
		
	 * @param mixed $params This is a description
	 * @return mixed This is the return value description
	
	 	$filter['and_where'] = $and_wheres;
		$filter['or_where'] = $or_wheres;
	 *
	 */
	protected function buildFilterSQL(&$params=array())
	{
		$where = "";
		
		$filter = $params['____filter'];
		if ($filter) {
			$and_wheres = array();		
			foreach ($filter['and_wheres'] as $key=>$v) {
				$type = $v['type'];
				$op = $v['op'];
				$key = '`'.$v['key'].'`';
				$value = $v['value'];
				
				$is_string = $this->is_string($type);
				
				switch ($op) {
					case '<':					
					case 'lt':					
						$and_wheres[] = $is_string?"$key<'$value'":"$key<$value";
						break;
					case '>':
					case 'gt':
						$and_wheres[] = $is_string?"$key>'$value'":"$key>$value";
						break;
					case '<=':
					case 'lte':
						$and_wheres[] = $is_string?"$key<='$value'":"$key<=$value";
						break;
					case '>=':
					case 'gte':
						$and_wheres[] = $is_string?"$key>='$value'":"$key>=$value";
						break;
					case '=':
					case '==':
					case 'eq':
						$and_wheres[] = $is_string?"$key='$value'":"$key=$value";
						break;
					case '!==':
					case '!=':
					case 'ne':
						$and_wheres[] = $is_string?"$key!='$value'":"$key!=$value";
						break;
					case 'like':
						$and_wheres[] = "$key like '%$value%'";
						break;
					case 'llike':
						$and_wheres[] = "$key like '$value%'";
						break;
					case 'rlike':
						$and_wheres[] = "$key like '%$value'";
						break;
					case 'in':
						$value = is_array($value)?implode(',', $value):$value;
						$and_wheres[] = "$key in ($value)";
						break;
				}
			}
			
			$or_wheres = array();		
			foreach ($filter['or_wheres'] as $key=>$v) {
				$type = $v['type'];
				$op = $v['op'];
				$key = '`'.$v['key'].'`';
				$value = $v['value'];
				$is_string = $this->is_string($type);
				
				switch ($op) {
					case '<':
					case 'lt':
						$or_wheres[] = $is_string?"$key<'$value'":"$key<$value";
						break;
					case '>':
					case 'gt':
						$or_wheres[] = $is_string?"$key>'$value'":"$key>$value";
						break;
					case '<=':
					case 'lte':
						$or_wheres[] = $is_string?"$key<='$value'":"$key<=$value";
						break;
					case '>=':
					case 'gte':
						$or_wheres[] = $is_string?"$key>='$value'":"$key>=$value";
						break;
					case '=':
					case '==':
					case 'eq':
						$or_wheres[] = $is_string?"$key='$value'":"$key=$value";
						break;
					case '!==':
					case '!=':
					case 'ne':
						$and_wheres[] = $is_string?"$key!='$value'":"$key!=$value";
						break;					
					case 'like':
						$or_wheres[] = "$key like '%$value%'";
						break;
					case 'in':
						$value = is_array($value)?implode(',', $value):$value;
						$or_wheres[] = "$key in ($value)";
						break;
				}
			}		
			
			
			if ($and_wheres) {
				$where .= implode(" and ", $and_wheres);
			}
			
			if ($or_wheres) {
				$where && $where .= " and ";
				$where .= '('.implode(" or ", $or_wheres).')';
			}
			
			$where && $where = " where ".$where;
		}
			
		return $where;		
	}
	
	protected function buildSortSQL(&$params=array())
	{
		$orderby = "";
		if (isset($params['____orderby'])) {
			$orderby = 'order by ';
			foreach ($params['____orderby'] as $key=>$v) {
				$orderby .= " $key $v,";
			}
			$orderby = trim($orderby, ',');			
		}
		return $orderby;
	}
	
	protected function buildGroupSQL(&$params=array())
	{
		$groupby = isset($params['__groupby'])?(is_array($params['__groupby'])?'group by '.implode(',', $params['__groupby']):$params['__groupby']):'';
		
		return $groupby;		
	}
	
	
	
	protected function buildLimitSQL($tablename, $where, $orderby, $groupby, &$params)
	{
		$limit = "";
		
		$page_size =  $params['page_size'];
		$start =  $params['start'];
		
		$limit = " LIMIT $start,$page_size";
		
		$fields = isset($params['__fields'])?(is_array($params['__fields'])?implode(',', $params['__fields']):$params['__fields']):'*';
				
		$sql = "select $fields from $tablename $where $groupby $orderby $limit";
		
		//var_dump($sql);
		return $sql;
	}
	
	
	
	//查询
	
	/**
	 * This is method select
	 *
	 * @param mixed $tablename This is a description
	 * @param mixed $params 
		$params['__filter'] = $filter;
		$params['__orderby'] = $orderby;
		$params['__pagination'] = $pagination;
		  
		  
		$pagination['page'] = $page;
		$pagination['page_size'] = $page_size;
		$pagination['start'] = $start;
		$pagination['limit'] = $page_size;
		  
	 * @return mixed This is the return value description
	 *
	 */
	public function select($tablename, &$params=array())
	{
		//filter		
		$where = $this->buildFilterSQL($params);
		
		//order 
		$orderby = $this->buildSortSQL($params);
		
		//groupby, eg: group by userid,type
		$groupby = $this->buildGroupSQL($params);
				
		//total
		$sql_total = "select count(*) as total from $tablename $where $groupby";
		$res = $this->get_one($sql_total);
		$total = intval($res['total']);
		$page_size = intval($params['page_size']);
		!$page_size && $page_size = PHP_INT_MAX;
		$params['total'] = $total;
		$nr_page = ceil($total/$page_size);
		$params['nr_page'] = $nr_page;
		$page = intval($params['page']);
		if ($page <= 0) 
			$page = 1;
		if ($nr_page == 0) 
			$nr_page = 1;
			
		if ($page > $nr_page) //页号无效, 置为最后一页
			$page = $nr_page;		
			
		$params['page_size'] = $page_size;
		$params['page'] = $page;
		$params['start'] = ($page-1)*$page_size;
		
		$sql = $this->buildLimitSQL($tablename, $where, $orderby, $groupby, $params);
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "sql=$sql");
		
		$data = array();	
		$res = $this->query($sql);
		if ($res) {
			while ($v = $this->fetch_array($res)) {
				$data[] = $v;
			}
			$this->free_result($res);	
		}
				
		$params['nr_row'] = count($data);;
		$params['rows'] = $data;
		
		return $data;
	}	
		
	
	public function get_version()
	{
		return false;
	}
	
	public function get_dbinfo()
	{
		return $this->_options['dbtype'].'/'.$this->_options['dbhost'];
	}
	
	
	public function backup_out($table, $start, &$count)
	{
		return false;
	}
	
	public function backup_in($file, $start, $count, &$total)
	{
		return false;
	}
	
	
	
	function get_guid()
	{
		return false;
	}
	
	
	public function escape_string($data)
	{
		return $data;
	}
	
	public function get_primary_key($table) 
	{
		return false;
	}
	
	
	public function queryFields($table)
	{
		return false;
	}
	
	//查看表中字段
	public function show_fields($table)
	{
		return false;
	}
	
	//查看到所有表
	public function show_tables()
	{
		return false;
	}
	
	//通过表名，提取字段，组成以','分隔的字串返回
	public function get_field_names($table_name)
	{
		return false;
	}
	
	public function exec_procedure_file($file, $delimiter='$$')
	{
		return false;
	}
	
	public function set_names()
	{
		return false;
	}
}
