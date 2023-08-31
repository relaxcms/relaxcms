<?php
/**
 * @file
 *
 * @brief 
 * Mysql PDO 数据库管理
 *
 * 
 * 
 */
class MysqlpdoDatabase extends CDatabase
{
	function __construct($name, $options)
	{
		parent::__construct($name, $options);
	}
	
	function MysqlpdoDatabase($name, $options)
	{
		$this->__construct($name, $options);
	}
	
	
	protected function parseDsn($options)
	{
		if (!empty($options['socket'])) {
			$dsn = 'mysql:unix_socket='. $options['socket'];
		} elseif (!empty($options['dbport'])) {
			$dsn = 'mysql:host='.$options['dbhost'].';port=' . $options['dbport'];
		} else {
			$dsn = 'mysql:host=' . $options['dbhost'];
		}
		if (isset($options['dbname']) ) {
			$dsn .= ';dbname=' . $options['dbname'];
		}
		
		if (!empty($options['dbcharset'])) {
			$dsn .= ';charset=' . $options['dbcharset'];
		}
		return $dsn;
	}


	/**
	 * This is method connect
	 *
	 * eg: https://www.php.net/manual/zh/pdo.connections.php
	 * eg: https://www.cnblogs.com/52fhy/p/5352304.html
	 * eg: https://www.php.net/manual/zh/book.pdo.php
	 * 
	 * eg: https://my.oschina.net/u/3828348/blog/4999085 域名连接可能会很慢
	 * 
	
	示例 #4 持久化连接
	<?php
	$dbh = new PDO('mysql:host=localhost;dbname=test', $user, $pass, array(
	   PDO::ATTR_PERSISTENT => true
	));
	?>
	
	
		
	
	pdo::query()方法
	当执行返回结果集的select查询时,或者所影响的行数无关紧要时, 应当使用pdo对象中的query()方法.
	如果该方法成功执行指定的查询,则返回一个PDOStatement对象.
	如果使用了query()方法,并想了解获取数据行总数,可以使用PDOStatement对象中的rowCount()方法获取.

	pdo::exec()方法
	当执行insert,update,delete没有结果集的查询时, 使用pdo对象中的exec()方法去执行.
	该方法成功执行时,将返回受影响的行数.注意,该方法不能用于select查询.
	
	
	01)连接mysql
	
	$m=new PDO("mysql:host=localhost;dbname=test","root","123");
	
	02)连接pgsql
	
	$m=new PDO("pgsql:host=localhost;port=5432;dbname=test","postgres","123");
	
	03)连接Oracle
	
	$m=new PDO("OCI:dbname=accounts;charset=UTF-8", "scott", "tiger"); 
	
	
	PDO 驱动
	
	   CUBRID (PDO) — CUBRID Functions (PDO_CUBRID)
	   MS SQL Server (PDO) — Microsoft SQL Server and Sybase Functions (PDO_DBLIB)
	   Firebird (PDO) — Firebird Functions (PDO_FIREBIRD)
	   IBM (PDO) — IBM Functions (PDO_IBM)
	   Informix (PDO) — Informix Functions (PDO_INFORMIX)
	   MySQL (PDO) — MySQL Functions (PDO_MYSQL)
	   MS SQL Server (PDO) — Microsoft SQL Server Functions (PDO_SQLSRV)
	   Oracle (PDO) — Oracle Functions (PDO_OCI)
	   ODBC and DB2 (PDO) — ODBC and DB2 Functions (PDO_ODBC)
	   PostgreSQL (PDO) — PostgreSQL Functions (PDO_PGSQL)
	   SQLite (PDO) — SQLite Functions (PDO_SQLITE)
	
	 * @return mixed This is the return value description
	 *
	 */
	public function connect()
	{
		if ($this->_link)
			return true;
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, " connect IN");
		//"mysql:host=localhost;port=3306;dbname=rcdb7;charset=latin1"
		$dsn = $this->parseDSN($this->_options);
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, " connect dsn=".$dsn);
		
		try {
			//连接
			$params = array(
					PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, //默认是PDO::ERRMODE_SILENT, 0, (忽略错误模式)
					PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // 默认是PDO::FETCH_BOTH, 4
					);
					
			$link = new PDO($dsn, $this->_options['dbuser'], $this->_options['dbpassword'], $params);
		} catch (PDOException $e) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "new PDO failed!".$e->getMessage());
			$link = false;
		}
		if (!$link) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "new PDO failed!dsn=$dsn");
			return false;
		}
				
		$this->_link = $link;
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "connect OUT");
		return true;
	}
	
	public function reconnect($newdb='')
	{
		if ($this->_link) {
			$this->close();
		}
		
		if ($newdb && $newdb != $this->_options['dbname'])
			$this->_options['dbname'] = $newdb;
			
		return $this->connect();
	}
	
	//关闭数据库
	public function close()
	{
		//$this->_link->close();
		$this->_link = null;
	}
	
	
	protected function __error()
	{
		return $this->_link->errorInfo();
	}
	
	protected function __errno()
	{
		return $this->_link->errorCode();
	}
	
	
	public function query($sql, $method = '') 
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "IN");
		if (!$this->_link)
			$this->connect();
		
		$sql = $this->_prefix_replace($sql);	
		
		try{
			//返回一个PDOStatement对象
			//$query  = $this->_link->prepare($sql);		
			//执行一条预处理语句 .成功时返回 TRUE, 失败时返回 FALSE 
			//$res = $query->execute();
			
			$query = $this->_link->query($sql);		
			if (!$query) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call query failed!sql=$sql");					
			}
		} catch(PDOException $e){
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call query failed!sql=$sql", $e->getMessage());	
			$query = false;
		}
		
		//$row = $query->fetch(); //从结果集中获取下一行，用于while循环
		//$rows = $query->fetchAll(); //获取所有
		
		$this->query_num++;
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "OUT");
				
		return $query;
	}
	
	/*
	pdo::exec()方法
	当执行insert,update,delete没有结果集的查询时,使用pdo对象中的exec()方法去执行.
	该方法成功执行时,将返回受影响的行数.注意,该方法不能用于select查询.
		
		
	//返回受影响的行数
	
	警告 此函数可能返回布尔值 false，但也可能返回等同于 false 的非布尔值。
	请阅读 布尔类型章节以获取更多信息。应使用 === 运算符来测试此函数的返回值。
	
	*/
	
	public function exec($sql) 
	{
		if (!$this->_link)
			$this->connect();
		
		$sql = $this->_prefix_replace($sql);	
		
		try {
			$res = $this->_link->exec($sql);//返回受影响的行数
						
		} catch (PDOException $e) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call exec failed!sql=$sql", $e->getMessage());
			$res = false;			
		}
		
		$this->query_num++;
				
		return $res !== false;		
	}
	
	/*
	
	PDO::FETCH_ASSOC 关联数组形式
	
	PDO::FETCH_NUM 	数字索引数组形式
	
	PDO::FETCH_BOTH 两者数组形式都有，这是默认的
	
	PDO::FETCH_OBJ 	按照对象的形式，类似于以前的mysql_fetch_object()
	
	PDO::FETCH_BOUND 以布尔值的形式返回结果，同时将获取的列值赋给bindParam()方法中指定的变量
	
	PDO::FETCH_LAZY 以关联数组、数字索引数组和对象3种形式返回结果。
	
	//$row = $stmt->fetch(); //从结果集中获取下一行，用于while循环
	
	*/
	
	function fetch_array($query, $result_type = PDO::FETCH_ASSOC) 
	{
		$res = $query->fetch($result_type);
		return $res;
	}
	
	// return first column of first row
	public function fetchFirst($query)
	{
		$row = $query->fetch( PDO::FETCH_ASSOC );
		return $row;
	}
	
	
	function free_result($query) 
	{
		$query = null;
		return false; //mysql_free_result($query);
	}
	
	/*public function affected_rows($query) 
	{
		return $query->rowCount();
	}*/
	
	public function num_rows($query) 
	{
		return $query->rowCount();
	}
	
	
	public function get_one( $sql )
	{
		$query = $this->query($sql);
		if (!$query) {
			return false;		
		}			
		$res = $this->fetchFirst($query);		
		
		return $res;
	}
	
	
	public function get_max_id($table, $field)
	{
		$sql = "select max($field) as maxid from $table";
		
		$res = $this->get_one($sql);
		if (!$res)
		{
			return 1;
		}
		else
		{
			return intval($res['maxid']) + 1;
		}
	}
	
	
	
	/* ==========================================
	 * DB HELPER FUNCTIONS
	 * 库操作
	 * ========================================== */
	public function db_select($dbname=null)
	{
		if ($dbname)
			$this->_options['dbname'] = $dbname;
		
		$this->_link = null;
		
		$res = $this->connect();	
		
		return $res;
	}
	
	public function db_exists($dbname)
	{
		$sql = "show databases";// like '$dbname'
		$query = $this->query($sql);
		$rows = $query->fetchAll();
		foreach ($rows as $key=>$v){
			if ($v['Database'] == $dbname)
				return true;
		}
		return false;	
	}
	
	
	public function db_drop($dbname)
	{
		$sql = "drop database $dbname";		
		$this->exec($sql);
		
		return true;	
	}
	
	public function db_create($dbname, $exists_drop=false)
	{
		$sql = "create database $dbname";		
		$res = $this->exec($sql);
		return $res == 1; //创建成功
	}
	
	
	
	public function db_space($dbname='')
	{
		$o_size = 0;
		$query = $this->query("SHOW TABLE STATUS");
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
		$sql = $sqlinfo['sql'];
		
		switch ($sqlinfo['type']) {
			case 'createtable':
				$sql .= $this->_options['dbcharset'] ? "ENGINE=MyISAM DEFAULT CHARSET={$this->_options['dbcharset']};" : "ENGINE=MyISAM";
				
			case 'droptable':
			case 'createview':
			case 'insertinto':
			case 'data':
				break;
			default:
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "unknow sql type '{$v['type']}'!");
				break;
		}
		
		//var_dump($sql);
		$res = $this->exec($sql);
		if ($res === false) { //Query OK, 0 rows affected (0.02 sec)
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call query failed!sql=$sql");		
			return false;	
		}
		return true;
	}
	
	
	public function queryFields($table_name)
	{
		$sql = "SHOW FULL FIELDS FROM $table_name";
		
		$query = $this->query($sql);
		
		
		$fdb = array();
		if ($query) {
			$strtypes = array('char' => true ,'varchar'=>true, 'text'=>true, 'tinytext'=>true );
			
			while ($v = $this->fetch_array($query)) {
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
			$this->free_result($query);	
		}
		return $fdb;
	}	
	
	
	
	function table_exists($table)
	{
		$sql = "SHOW TABLES LIKE '".$table."'";
		$res = $this->exists($sql);
		return $res;		
	}
	
	function get_guid()
	{
		$sql = "select uuid() as guid";
		
		$res = $this->get_one($sql);
			
		return $res['guid'];
	}
		
		
	public function show_tables()
	{
		$udb = array();
		$res = $this->query("show table status where Engine !='VIEW'");
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
		
		$res =  $this->_link->quote($data); //mysql_escape_string($data);
		
		//前后的'去掉, 与 mysql_escape_string 一致
		$res = trim($res,'\''); 
		
		return $res;
	}
}
