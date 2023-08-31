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
class MongoDatabase extends CDatabase
{
	protected $_currentdb = null;
	function __construct($name, $options)
	{
		parent::__construct($name, $options);
	}
	
	function MongoDatabase($name, $options)
	{
		$this->__construct($name, $options);
	}
		
	public function connect()
	{
		if ($this->_link)
			return true;
		
		$cnnstring = "mongodb://".$this->_options['dbhost'].":".$this->_options['dbport']."/".$this->_options['dbname'];
		$link = new MongoClient($cnnstring);
		if (!$link) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "new MongoClient failed!");
			return false;
		}
				
		$this->_link = $link;
		$this->_currentdb = $link->selectDB($this->_options['dbname']);		
		//$this->_currentdb = $link->$this->_options['dbname'];
		
		return true;
	}
	
	/* ==========================================
	 * DB HELPER FUNCTIONS
	 * 库操作
	 * ========================================== */
	public function db_select($dbname=null)
	{
		$res = $this->_link->selectDB($dbname);
		$this->_currentdb = $res;
		
		return $res;
	}
	
	/*
	array(3) {
	 ["databases"]=>
	 array(1) {
	   [0]=>
	   array(3) {
	     ["name"]=>
	     string(5) "local"
	     ["sizeOnDisk"]=>
	     float(65536)
	     ["empty"]=>
	     bool(false)
	   }
	 }
	 ["totalSize"]=>
	 float(65536)
	 ["ok"]=>
	 float(1)
	}*/
	
	public function db_exists($dbname)
	{
		$dbs = $this->_link->listDBs();  
		if ($dbs) {
			foreach ($dbs['databases'] as $key=>$v) {
				if ($v['name'] == $dbname)
					return true;
			} 
		}
		
		return false;
	}
	
	
	public function db_drop($dbname)
	{
		if (!$this->db_exists($dbname))
		{
			return true;
		}
				
		//删除一个数据库 
		//if ($dbname == $this->_options['dbname'])
		//	return false;
			 
		$res = $this->_currentdb->drop(); 
		if (!$res) {
			$this->halt("drop db failed!");
		}
		
		var_dump("drop db '$dbname' ok!!");
				
		return $res;
	}
	
	public function db_create($dbname, $exists_drop=false)
	{
		if ($exists_drop === true)
		{
			$this->db_drop($dbname);
		}
		
		$res = $this->_link->selectDB($dbname);
		if (!$res) {
			$this->halt("select db failed!");
		}
		$this->_currentdb = $res;
		return $res;
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
	
	
	public function queryFields($tablename)
	{
		$tableinfo = $this->getTableInfo($tablename);
		if (!$tableinfo) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call getTableInfo failed!");
			return false; 
		}
		$strtypes = array('char' => true ,'varchar'=>true, 'text'=>true, 'tinytext'=>true );
		
		$fdb = array();
		$indexdb = $tableinfo['index'];
		
		foreach ($tableinfo['fdb'] as $key=>$v) {
			$name = $v['name'];
		
			$is_primary_key = ($tableinfo['pkey'] == $name)?true:false;	
			
			$type = $v['type'];
							
						
			$is_string = isset($strtypes[$type])?true:false;
			$is_null = ($v['is_nullable'] == 'YES')?true:false;
			
			$v['is_primary_key'] = $is_primary_key;
			$v['is_field'] = true;
			$v['is_string'] = $is_string;
			$v['is_null'] = $is_null;
			$v['required'] = !$is_null;
			
			$fdb[$name] = $v;
		}
		return $fdb;
	}	
	
	
	
	protected function query_noerror($sql, $method = '') 
	{
		$sql = $this->_prefix_replace($sql);		
		if($method =='U_B' && function_exists('mysql_unbuffered_query'))
		{
			$query = mysql_unbuffered_query($sql, $this->_link);
		}
		else
		{
			$query = mysql_query($sql, $this->_link);
		}
		
		
		$this->query_num++;
		if (!$query) 
		{
			return false;
		}
		
		return $query;
	}
	
	
	public function get_one( $sql )
	{
		
		$query = $this->query($sql, 'U_B');
		if (!$query)
			return false;		
			
		$rs = mysql_fetch_array($query, MYSQL_ASSOC);		
		@mysql_free_result($query);
		
		return $rs;
	}
	
	public function get($tablename, $pkey, $id) 
	{
		$params = array($pkey=>$id);
		$res = $this->findOne($tablename, $params);	
		return $res;		
	}
	
	public function findOne($tablename, $params) 
	{
		$filter = $params['__wheres'];
		
		$db = $this->_currentdb;  
		$collection = $db->selectCollection($tablename);
		
		$res = $collection->findOne($filter); 
		
		return $res;		
	}
	
	
   /** 
   * 插入记录 
   * 
   * 参数： 
   * $table_name:表名 
   * $record:记录 
   * 
   * 返回值： 
   * 成功：true 
   * 失败：false 
   */  
	
	public function insert($tablename, $params) 
	{
		try {  
			$db = $this->_currentdb;  
			$collection = $db->selectCollection($tablename);
			
			$collection->insert($params, array('w'=>true));  
			
			return true;  
		}
		  
		catch (MongoCursorException $e)  
		{  
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, $e->getMessage());  
			return false;  
		}  			
		return true;
		
	}
	
	public function find($table_name, $query_condition, $result_condition=array(), $fields=array(), &$params=array())  
	{  
		$db = $this->_currentdb;  
		$collection = $db->selectCollection($table_name);
		//var_dump($query_condition);		
		$cursor = $collection->find($query_condition, $fields);  
		if ($params) {
			$total = $cursor->count();
			$params['total'] = $total;
			$nr_page = ceil($total/$params['page_size']);
			$params['nr_page'] = $nr_page;
			$page = $params['page'];
			$page_size = $params['page_size'];
			if ($nr_page == 0) {
				$params['page'] = 1;
			} else if ($params['page'] > $nr_page) //页号无效, 置为最后一页
				$params['page'] = $nr_page;		
			
			$params['start'] = ($params['page']-1)*$params['page_size'];
			$result_condition['start'] = $params['start'] ;
		}
		
		if (!empty($result_condition['start']))  
		{  
			$cursor->skip($result_condition['start']);  
		}  
		if (!empty($result_condition['limit']))  
		{  
			$cursor->limit($result_condition['limit']);  
		}  
		if (!empty($result_condition['sort']))  
		{  
			$cursor->sort($result_condition['sort']);  
		}  
		
		//var_dump($cursor);
		
		$result = array();  
		try {  
			while ($cursor->hasNext())  
			{  
				$result[] = $cursor->getNext();  
			}  
		}  
		catch (MongoConnectionException $e)  
		{  
			$this->error = $e->getMessage();  
			return false;  
		}  
		catch (MongoCursorTimeoutException $e)  
		{  
			$this->error = $e->getMessage();  
			return false;  
		}  
		return $result;  
	} 
	
	public function buildFilterSQL(&$params=array())
	{
		$where = array();
		
		$and_wheres = array();		
		foreach ($params['and_wheres'] as $key=>$v) {
			$type = $v['type'];
			$key = $v['key'];
			$value = $v['value'];
			
			switch ($type) {
				case 'lte':
					$and_wheres[] = array($key=>array('$lte'=>$value));
					break;
				case 'gte':
					$and_wheres[] = array($key=>array('$gte'=>$value));
					break;
				case 'eq':
					$and_wheres[] = array($key=>$value);
					break;
				case 'like':
					$and_wheres[] = array($key=>new MongoRegex("/.*".$value.".*/"));//"$key like %$value%";
					break;
			}
		}
		
		$or_wheres = array();		
		foreach ($params['or_wheres'] as $key=>$v) {
			$type = $v['type'];
			$key = $v['key'];
			$value = $v['value'];
			
			switch ($type) {
				case 'lte':
					$or_wheres[] = array($key=>array('$lte'=>$value));
					break;
				case 'gte':
					$or_wheres[] = array($key=>array('$gte'=>$value));
					break;
				case 'eq':
					$or_wheres[] = array($key=>$value);
					break;
				case 'like':
					$or_wheres[] = array($key=>new MongoRegex("/.*".$value.".*/"));
					break;
			}
		}		
		
		
		if ($and_wheres) {
			$where['$and'] = $and_wheres;
		}
		
		if ($or_wheres) {
			$where['$or'] = $or_wheres;
		}
		
		$params['__wheres'] = $where;
		
		return true;			
	}
	
	
	public function buildSortSQL(&$params=array())
	{
		$sort = array();
		if (isset($params['order_field']) && $params['order_field']) {
			$sort[$params['order_field']] = $params['order_dir'] == 'asc'?1:-1;			
		}
		
		$params['__sort'] = $sort;
		return true;
	}
	
	
	public function select($tablename, &$params=array())
	{
		$queryparams = array();
		if (isset($params['__filter']) 
				&& !empty($params['__filter']['__wheres']) ) {						
			$queryparams = $params['__filter']['__wheres'];			
		}
		
		$sortParams = array();
		if (isset($params['__sort']) 
				&& !empty($params['__sort']['__sort']) ) {						
			$sortParams = $params['__sort']['__sort'];			
		}
		
		////total
		//$sql_total = "select count(*) as total from $tablename $where";
		//$res = $this->get_one($sql_total);
		
		$page_size =  $params['page_size'];
		$start =  ($params['page']-1)*$page_size;
				
		$limitparams = array("start"=>$start, "limit"=>$page_size, "sort"=>$sortParams);
		//var_dump($limitparams);
		
		//查询最大值 field
		$data = $this->find($tablename, 
				$queryparams, 
				$limitparams, array(), $params) ;
				
		$params['nr_row'] = count($data);;
		$params['rows'] = $data;
		
		return $data;
		
	}
	
	function get_max_id($table, $field)
	{
		//查询最大值 field
		$res = $this->find($table, 
				array(), 
				array("limit"=>1,"sort"=>array("id"=>-1)), array($field=>true)) ;
			
		if (!$res) {
			return 1;			
		}
		return intval($res[0][$field]) + 1;
	}
	
	
	//更新
	function exec($sql) 
	{
		$sql = $this->_prefix_replace($sql);
		
		$res = $this->_link->exec($sql);
		if(!$res){
			$this->halt('Update Error: ' . $this->_link->lastErrorMsg());
		} 
				
		$this->query_num++;
		
		return $res;
	}		
	
	
	function fetch_array($query, $result_type = PGSQL_ASSOC) 
	{
		return $query->fetchArray(SQLITE3_ASSOC);
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
		return mysql_get_server_info($this->_link);
	}	
	
	//关闭数据库
	protected function close()
	{
		$this->_link->close();
		$this->_link = 0;
	}	
	
	//数据出现，中断处理
	protected function halt($msg='') 
	{
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "MongoDB halt!error=".$this->_link->lastErrorMsg());		
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
	
	protected function getCollection($tname)
	{
		return $this->_currentdb->selectCollection($tname);
	}
	
	protected function createCollection($tname)
	{
		return $this->_currentdb->createCollection($tname);		
	}
	
	
	protected function setTableInfo($tableinfo)
	{	
		$tname = 'tableinfo';		
		if (!($c = $this->getCollection($tname))) {
			$c = $this->createCollection($tname);	
			// create a unique index on 'y'
			$c->createIndex (array('name'=>1), array('unique'=>true));
		} 
		if ($c) {
			
			$name = $tableinfo['name'];
			$data = serialize($tableinfo);			
			//写入
			$cursor = $c->find(array("name"=>$name));
			foreach ( $cursor  as  $doc ) {
				$old = $doc;
			}
			
			$res = $c->insert(array( "name"=>$name, "tableinfo"=>$data));
			if (!$res) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "insert failed!");
				return false;
			}
			
			return true;
		}		
		
		rlog(RC_LOG_ERROR, __FILE__, __LINE__, "create 'tableinfo' createCollection  failed!");
		return false;
	}
	
	protected function getTableInfo($tablename)
	{
		$tname = 'tableinfo';		
		if (!($c = $this->getCollection($tname))) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no tableinfo '$tname'!");
			return false;
		} 
		
		$tableinfo = array();
		$cursor = $c->find(array("name"=>$tablename));
		foreach ( $cursor  as  $v ) {
			$tableinfo = $v;
		}
		
		if (!$tableinfo) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no table '$tablename'! ");
			return false;
		}
		
		$tableinfo = unserialize($tableinfo['tableinfo']);
		
		return $tableinfo;
	}
	
	
	/* ==========================================
	 * SQL SCRIPTS HELPER FUNCTIONS
	 * ==========================================*/
	protected function importCreateTable($tableinfo)
	{
		$res = $this->setTableInfo($tableinfo);
		var_dump($res);
		
		$tablename = $tableinfo['name'];
				
		//创建table (collection)
		$res = $this->_currentdb->createCollection($tablename);
				
		return $res;
		
	}
	
	protected function importSql($sqlinfo)
	{
		$res = false;
		switch ($sqlinfo['type']) {
			case 'createtable':
				$res = $this->importCreateTable($sqlinfo['tableinfo']);
				break;
			case 'droptable':
			case 'createview':
			case 'insertinto':
			case 'data':
				break;
			default:
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "unknow sql type '{$v['type']}'!");
				break;
		}
		
		/*$sql = $sqlinfo['sql'];
		$res =  $this->query($sql);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call query failed!sql=$sql");
			return false;			
		}*/
		
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
	
	function table_exists($tname)
	{
		$cdb = $this->_currentdb->getCollectionNames(array('filter'=>array('name'=>$tname)));
		if ($cdb) {
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
		
		return $data;
	}
	
	public function truncate($tablename) 
	{
		$collection = $this->_currentdb->selectCollection($tablename);
		// will not work:
		$res = $collection->remove();
		
		return $res;	
	}
	
}
