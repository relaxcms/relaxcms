<?php

/**
 * @file
 *
 * @brief 
 * 缓存类定义
 *
 * @author Jonny <xjlicn@163.com>
 * @date	2018-8-3
 *
 * Copyright (c), 2018, relaxcms.com
 */
class CCache
{
	protected $_cache_dir;
	
	function __construct()
	{
		$this->_cache_dir = RPATH_CACHE;
	}
	
	function CCache()
	{
		$this->__construct();
	}
	
	//创建
	static function GetInstance()
	{
		static $instance;		
		if(!is_object($instance))	{
			$instance = new CCache();			
		}
		return $instance;
	}
		
			
	
	
	private function _get_sql_files($dir)
	{
		$files = array();
		$file = array();
		
		$d = dir($dir);
		$ext = ".sql";
		
		while (false !== ($entry = $d->read())) {
			$file_ext = substr($entry, -4, 4);
			if ($file_ext == $ext)
			{
				$file['data'] = file_get_contents($dir.DS.$entry);
				$file['name'] = $entry;
				$file['time'] = fileatime($dir.DS.$entry);
				
				$files[] = $file;
			}
		}
		$d->close();
		
		return $files;
	}
	
	//缓存备份
	public function cache_backup($file, $data, $left, $start, $dir=null)
	{
		$cf = get_config();
		
		$mode = 0777;
		if ($dir === null)
			$dir = RPATH_CACHE;
		
		$dir = $dir.DS."backup";
		is_dir($dir) || @mkdir($dir, $mode);
		
		$path = $dir.DS.$file.".sql";
		
		if($start == 0)
			s_write($path,  $data); //清除文件内容
		else
			s_write($path,  $data, "a+"); //添加模式
		
		$ts = time();
		$bname = $cf['dbname'].'_'.date('Y_m_d_H_i_s', $ts);
		//压缩目录
		if ($left === 0 ) {
			$zlib = $dir.DS.$bname.".zip";
			$files = $this->_get_sql_files($dir);
			
			$zip = Factory::GetZip();
			$zip->compress($zlib, $files);
			
			//删除缓存sql
			s_unlink($dir.DS."*.sql");
		}
		
		return 1;
	}
	
	
	
	//提取Zip文件列表
	public function get_backup_zip_files()
	{
		$files = array();
		$file = array();
		
		$dir = RPATH_CACHE.DS."backup";
		if (!is_dir($dir))
		{
			return false;
		}
		
		$d = dir($dir);
		$ext = ".zip";
		
		while (false !== ($entry = $d->read())) {
			$file_ext = substr($entry, -4, 4);
			if ($file_ext == $ext) {
				$file['name'] = $entry;
				$file['time'] = filemtime($dir.DS.$entry);
				$file['size'] = filesize($dir.DS.$entry);
				
				$files[$file['time']] = $file;
			}
		}
		$d->close();
		
		
		return $files;
	}
	
	private function _get_sql_files2($dir)
	{
		$files = array();
		$file = array();
		
		$d = dir($dir);
		$ext = ".sql";
		
		while (false !== ($entry = $d->read())) {
			$file_ext = substr($entry, -4, 4);
			if ($file_ext == $ext)
			{
				$file['table'] = substr($entry, 0, strlen($entry)-4);
				$file['name'] = $entry;
				$file['time'] = fileatime($dir.DS.$entry);
				$file['size'] = filesize($dir.DS.$entry);
				
				$files[] = $file;
			}
		}
		$d->close();
		
		return $files;
	}
	
	
	//提取zip中文件信息
	public function get_zip_info($zip, $have_path=false)
	{
		if (!$have_path) {
			$dir = RPATH_CACHE.DS."backup";
			$path = RPATH_CACHE.DS."backup".DS.$zip;
		} else {
			$dir = dirname($zip);
			$path = $zip;
		}
		
		//清理目录
		s_unlink($dir.DS."*.sql");
		
		//解压		
		$z = Factory::GetZip();		
		$z->extract($path, $dir);
		
		//提联sql信息
		return $this->_get_sql_files2($dir);	
	}
	
	
	//备分表恢复
	function backin($table, $left, $start, $count, &$total)
	{
		$dir = RPATH_CACHE.DS."backup";
		$path = $dir.DS.$table.".sql";
		
		$db = Factory::GetDBO();
		$db->backup_in($path, $start, $count, $total);
		
		//
		/*$next = $start + $count;
		if ($next != $total)
		{
			if($left == 0) $left = 1;
		}*/
		
		//清理目录
		if ($left === 0 )
			s_unlink($dir.DS."*.sql");
		
		return "1";		
	}
	
	//cache style
	
	function CacheStyle()
	{
		$db = Factory::GetDBO();
		$sql = "select * from cms_style";
		
		$udb = $db->Select($sql);
		
		$path = RPATH_CACHE_SITE.DS."style.php";
		
		$cache="<?php\n";
		$cache_array = "\$style = array(\n";
		
		foreach($udb as $key=>$v)
		{
			$cache_array .= "\t'$v[style]'=>array(\n";
			foreach($v as $k2=>$v2)
			{
				$cache_array .= "\t'$k2'=>'$v2',\n";				
			}
			$cache_array .= "\t),\n";		
		}
		
		$cache_array .= ");\n";
		$cache .= $cache_array."?>";
		
		my_write($path,  $cache);
		
		//模板
		foreach($udb as $key=>$v)
		{
			$this->CacheTemplate($v['sid']);
		}		
	}
	
	
	
	
	
	/**
	 * This is method cache_select
	 * 
	 * 缓存选择器
	 * 
	 * @return mixed This is the return value description
	 *
	 * wesley
	 * 2011-12-12 21:11:38
	 */
	public function cache_select()
	{
		$path = RPATH_CACHE.DS."select.php";
		
		$db = Factory::GetDBO();
		
		$sql = "select * from cms_select ";
		$udb = $db->select($sql);
		
		$cache="<?php\n";
		$cache_array = "\$select = array(\n";
		
		foreach($udb as $key=>$v)
		{
			$cache_array .= "\t'$v[eid]'=>array(\n";
			foreach($v as $k2=>$v2)
			{
				$cache_array .= "\t'$k2'=>'$v2',\n";
				
			}
			$cache_array .= "\t),\n";
		}
		
		$cache_array .= ");\n";
		$cache .= $cache_array."?>";
		
		s_write($path,  $cache);
		
		return $udb;
	}
	
	//缓存表	
	public function cache_table($table, $suffix='order by title', $filename='')
	{
		$db = Factory::GetDBO();
		
		$pkey = $db->get_primary_key("cms_$table");
		$sql = "select *,$pkey as TREEID from cms_$table $suffix ";
		
		$udb = $db->select($sql);
		
		!$filename && $filename = $table; 
		if (!is_dir(RPATH_CACHE_TABLE))
			s_mkdir(RPATH_CACHE_TABLE);
		$path = RPATH_CACHE_TABLE.DS."$filename.php";
		
		$cache="<?php\n";
		$cache_array = "\${$filename}db = array(\n";
		
		foreach($udb as $key=>$v)
		{
			$cache_array .= "\t'{$v[$pkey]}'=>array(\n";
			foreach($v as $k2=>$v2)
			{
				$cache_array .= "\t'$k2'=>'$v2',\n";
			}
			$cache_array .= "\t),\n";
		}
		
		$cache_array .= ");\n";
		$cache .= $cache_array."?>";
		
		s_write($path,  $cache);
		
		return $udb;
	}
	
	public function get_cache_table($table, $cacheifnotexist=true)
	{
		$file = RPATH_CACHE_TABLE.DS."$table.php";
		if (file_exists($file)){
			require $file;
			return ${$table.'db'};
		} else if ($cacheifnotexist) {
			return $this->cache_table($table);			
		}		
		return false;
	}
	
	
	function cache_var()
	{
		$m = Factory::GetModel('var');
		$m->cache();		
	}
	
	
	function cache_var_value($vid)
	{
		$db = Factory::GetDBO();
		
		$where =" where vid=$vid ";		
		$sql = "select * from cms_var_value $where order by taxis asc ";
		
		$udb = $db->select($sql);
		
		$dir = RPATH_CACHE.DS.'var';
		if (!is_dir($dir)) 
			mkdir($dir);
		
		$file = $dir.DS."var$vid.php";
		
		$cache="<?php\n";
		$cache_array = "\$vardb = array(\n";
		
		foreach($udb as $key=>$v)
		{
			$cache_array .= "\t'$v[value]'=>'$v[title]',\n";			
		}
		
		$cache_array .= ");\n";
		$cache .= $cache_array."?>";
		
		s_write($file,  $cache);
	}
	
	//提取组中所有成员名以',' 分隔，组成字串反回
	private function _get_project_group_member($gid)
	{
		$db = Factory::GetDBO();
		
		$sql = "select username from cms_user a, cms_project_member b, cms_project_member2group c";		
		$sql .= " where a.uid=b.uid and b.mid=c.mid and c.gid=$gid";
		
		$udb = $db->select($sql);
		
		$users = array();
		foreach ($udb as $key=>$v)
		{
			$users[] = $v['username'];			
		}
		
		return implode(",", $users);
	}
	
	
	
	private function _cache_array($arr, $space='')
	{
		$space .= "\t";
		$res = "array(\n"; 
		foreach($arr as $key=>$v) {
			
			if (is_array($v))
				$res .= "$space'$key'=>".$this->_cache_array($v, $space);
			else 
				$res .= "$space'$key'=>'$v',\n";
		}
		$res .= "$space),\n";
		
		return $res;		
	}
	
	
	/**
	 * 缓存数组
	 *
	 * @param mixed $name 缓存文件名
	 * @param mixed $arr 数组
	 * @return mixed 成功true, 失败false
	 *
	 */
	public function cache_array($name, $arr = null, $cachefile=null)
	{
		if ($cachefile)
			$file = $cachefile;
		else
			$file = RPATH_CACHE.DS.$name.'.php';
		
		if ($arr === null) {
			$res = array();
			if (file_exists($file)) {
				require $file;
				$res = ${$name};
			}
			return $res;
		}
		
		$cache="<?php\n";
		$cache_array = "\${$name} = array(\n";
		$space = "\t";
		foreach($arr as $key=>$v)
		{
			if (!$key)
				continue;
				
			if (is_array($v)) {
				$cache_array .= "\t'$key'=>".$this->_cache_array($v, $space);
			} else {
				$cache_array .= "\t'$key'=>'$v',\n";	
			}		
		}
		
		$cache_array .= ");\n";
		$cache .= $cache_array."?>";
		
		s_write($file,  $cache);

		return true;
	}
	
	
	public function cache_component2pids($pdb)
	{
		$component2pids = array ();
		foreach ($pdb as $key=>$v) {
			$component2pids[$key] = $v['pid'];
			if ($v['child']) {
				foreach ($v['child'] as $k2 =>$v2) {
					$component2pids[$k2] = $v2['pid'];
				}
			}
		}
		
		$this->cache_array('component2pids', $component2pids);
		return true;
	}
}
