<?php

/**
 * @file
 *
 * @brief 
 * 
 * 备份
 *
 */

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );
class CBackupModel extends CDataModel
{
	protected $_bdir;
	protected $_restoredir;
	
	protected $_mdb = array();
	protected $_bdb = array();
	
	protected $_max_mid;
	protected $_max_bid;
	
	public function __construct($name, $options=array())
	{
		parent::__construct($name, $options);
	}
	
	public function CBackupModel($name, $options=array())
	{
		$this->__construct($name, $options);
	}
	
	
	protected function _initModels()
	{
		$no_backup_system_model = array('session');
		
		$tables = $this->_db->show_tables();
		$mdb = array();
		
		$id = 1;
		foreach ($tables as $key => $v) {
			$name = str_replace('cms_', '', $v['Name']);
			if (in_array($name, $no_backup_system_model)) {
				$v['disabled'] = true;
			}
			
			$v['name'] = $name;
			$v['id'] = $id++;
			$v['rows'] = $v['Rows'];
			$v['size'] = $v['Rows'];
			
			$mdb[] = $v;			
		}		
		$this->_mdb = $mdb;
		$this->_max_mid = $id;
	}
	
	protected function _initBackupFiles()
	{
		$zdir = RPATH_CACHE.DS.'backup';
		$fdb = s_readdir($zdir);
		
		$bdb = array();
		$max_bid = 0;
		foreach ($fdb as $key=>$v) {
			$item = array();
			$fname = $v;
			$ext = s_extname($fname);
			$idnamedb = explode('_', $fname);
			$id = intval(end($idnamedb));
			$item['name'] = $v;
			$item['id'] = $id;
			
			$item['atime'] = fileatime($zdir.DS.$v);
			$item['size'] = nformat_human_file_size(filesize($zdir.DS.$v));
			
			$bdb[] = $item;
			
			if ($max_bid < $id)
				$max_bid = $id;
			
		}
		
		if ($bdb) {
			array_sort_by_field($bdb, "atime", true);
		}
		$this->_bdb = $bdb;
		$this->_max_bid  = $max_bid;
	}
	
	protected function _init()
	{
		parent::_init();
		
		$this->_bdir = RPATH_CACHE.DS.'backup';
		if (!is_dir($this->_bdir)) 
			s_mkdir($this->_bdir);
		
		$this->_restoredir = RPATH_CACHE.DS.'restoredbtmp';
		if (!is_dir($this->_restoredir)) 
			s_mkdir($this->_restoredir);
		
		$this->_initModels();		
		$this->_initBackupFiles();		
	}
	
	
	public function getMinfoById($id)
	{
		foreach ($this->_mdb as $key=>$v) {
			if ($v['id'] == $id)
				return $v;
		}
		
		return false;
	}
	
	public function getBinfoById($id)
	{
		foreach ($this->_bdb as $key=>$v) {
			if ($v['id'] == $id)
				return $v;
		}
		
		return false;
	}

	public function getModels()
	{
		return $this->_mdb;
	}
	
	public function getBackupFiles()
	{
		return $this->_bdb;
	}
	
	
	private function _get_dir_files($dir)
	{
		$files = array();
		$file = array();
		
		$d = dir($dir);
		$ext = ".txt";
		
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
	
	protected function getBackupFileName($extname='zip')
	{
		$cf = get_config();
		$id = $this->_max_bid+1;
		$bname = $cf['dbname'].'data_'.tformat_current('YmdHis').'_'.$id;
		
		return $this->_bdir.DS.$bname.".".$extname;
	}
	
	
	//缓存备份
	public function compressBackupAllFiles($dir)
	{
		$zipfile = $this->getBackupFileName();		
		$files = $this->_get_dir_files($dir);
		
			
		$zip = Factory::GetZip();
		$zip->compress($zipfile, $files, $dir);
		
		//加密
		fencrypt($zipfile, $zipfile.".bz");
		
		unlink($zipfile);
				
		//删除缓存sql
		s_rmdir($dir);		
		return true;
	}
	
	
	public function backout($id, &$data)	
	{
		$minfo = $this->getMinfoById($id);
		if (!$minfo)
			return false;
		
		$modelname = $minfo['name'];
		$tablename = 'cms_'.$modelname;
		$fields = $this->_db->queryFields($tablename);
		if (!$fields) 
			return false;
		
		$names = array_keys($fields);		
		$fnames = implode(',', $names);		
		
		
		//打开文件
		$dir = RPATH_CACHE.DS."tmpbackup";
		if (!is_dir($dir)) 
			s_mkdir($dir);
		$file = $dir.DS.$modelname."-$id.txt";
		
		
		$rows = $minfo['rows'];
		$left = $data['left'];	
		if ($left < 0) { //清空
			$fd = fopen($file, "w+");
			fprintf($fd, $fnames."\n");
			$left = $rows;
			$start = 1;
		} else {	
			$start = $rows - $left+1;
			$fd = fopen($file, "a+");
		}
		
		$page_size = 100;//一次最多读100条
		$page = ceil($start/$page_size);
		
		if ($left > 0 ) {//开始导出 : $start, 10
			$m = Factory::GetModel($modelname);
			$params = array('page'=>$page, 'page_size'=>$page_size);
			$udb = $m->select($params);
			$nr = count($udb);
			
			//记录：""
			$content = '';
			foreach ($udb as $key=>$v) {
				foreach($v as $k2=>$v2) {
					$v[$k2] = addcslashes($v2, "\n\r\"\\");					
				}
				$content .= '"'.implode('","', $v)."\"\n";
			}			
			fwrite($fd, $content);
			$left -= $nr;
		}
		
		$data['left'] = $left;
		$data['total'] = $rows;
		fclose($fd);	
		
		
		if ($data['islast'] && $left == 0) { //备份完毕
			$this->compressBackupAllFiles($dir);
		}
	}
	
	public function del($id)
	{
		
		$binfo = $this->getBinfoById($id);
		if (!$binfo)
			return false;
			
		$zfile = $this->_bdir.DS.$binfo['name'];	
		$res = @unlink($zfile);	
		
		
		$this->writeLog(RC_LOG_NOTICE, __FUNCTION__, $res, $binfo, null, $id);
		
		return $res;
		
	}
	
	
	protected function getRows($fname)
	{
		$nr_line = 0;
		$fp = fopen($fname, "r");		
		while ($line=fgets($fp)) {
			$nr_line ++;
		}
		fclose($fp);
		$nr_line --;
		return $nr_line;
	}
	
	public function getRestoreFiles($zdir)
	{
		$files = s_readdir($zdir);
		
		$fdb = array();
		$id = 1;
		foreach ($files as $key=>$v) {
			$item = array();
			$fname = $v;
			$ext = s_extname($fname);
			list($modname, $id) = explode('-', $fname);
			$item['modelname'] = $modname;
			$item['name'] = $v;
			$item['id'] = $id;
			
			$item['atime'] = fileatime($zdir.DS.$v);
			$item['size'] = nformat_human_file_size(filesize($zdir.DS.$v));
			
			//行数
			$item['rows'] = $this->getRows($zdir.DS.$v);
			
			$fdb[] = $item;
			
		}		
		return $fdb;		
	}
	
	public function uncompressBackupAllFiles($zipfile)
	{	
		$zfile = $zipfile;
		$extname = s_extname($zfile);
		if ($extname == "bz") {
			if (!fdecrypt($zipfile, $zfile)) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "fdecrypt '$zfile' failed!");
				return false;
			}
			$zipfile = $zfile;
		}
		$tdir = $this->_restoredir;
		
		$zip = Factory::GetZip();
		$res = $zip->uncompress($zipfile, $tdir);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "uncompress zipfile '$zipfile' failed!");
			
		}	
		if ($extname == "bz") {
			unlink($zfile);
		}
		return $res;
	}
	
	public function uncompressRestore($id)
	{
		$binfo = $this->getBinfoById($id);
		if (!$binfo)
			return false;
			
		//解压
		$zipfile = $this->_bdir.DS.$binfo['name'];
		$res = $this->uncompressBackupAllFiles($zipfile);	
		
		return $res;
	}
	
	
	public function getRestoreList()
	{
		$tdir = $this->_restoredir;		
		$fdb = $this->getRestoreFiles($tdir);
		return $fdb;
	}
	
	protected function cleanRestoreFiles()
	{
		@s_rmdir($this->_restoredir);
	}
	
	protected function getFinfoById($id)
	{
		$fdb = $this->getRestoreList();
		
		$finfo = array();
		foreach ($fdb as $key=>$v) {
			if ($v['id'] == $id) {
				$finfo = $v;
				break;
			}
		}
		
		return $finfo;		
	}
	
		
	public function backin($id, &$data)
	{
		$finfo = $this->getFinfoById($id);
		if (!$finfo) {
			return false;
		}
		//modelname
		$modname = $finfo['modelname'];
		$m = Factory::GetModel($modname);
		if (!$m) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no model '$modname'!");
			return false;
		}
		$fields = $m->getFields();
		
		
		//开始恢复
		$rows = $finfo['rows'];
		$left = $data['left'];	
		if ($left < 0) { 
			$left = $rows;
			//clean
			$m->truncate();
			$start = 0;
		} else {	
			$start = $rows - $left;
		}
		
		$nr = $left;
		if ($nr > 100) //一次最多读100条
			$nr = 100;
		
		if ($left > 0 ) {//开始导入 : $start, $nr
			
			$fname = $this->_restoredir.DS.$finfo['name'];
			$fp = fopen($fname, "r");	
			if (!$fp){
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "invalid fname '$fname'!");
				return false;
			}
			//读首行
			$fnames = fgets($fp);
			if (!$fnames){
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no date!");
				return false;
			}
			
			//字段
			$fnames = trim($fnames);
			$ndb = explode(',', $fnames);
			$nr_name = count($ndb);
			
			$nr_line = 0;
			$nr_read = 0;
			while (($line = fgets($fp))) {
				if ($start > $nr_line++)
					continue;
				//还原
				$line = trim($line);
				$line = trim($line, "\"");
				$vdb = explode("\",\"", $line);
				
				$params = array();
				for($i=0; $i<$nr_name; $i++) {
					$key = $ndb[$i];
					//$v = stripcslashes($vdb[$i]);
					$params[$key] = $this->parseValue($fields[$key], $vdb[$i]);	
				}
				
				$res = $m->insert($params);
				if (!$res) {
					rlog(RC_LOG_ERROR, __FILE__, __LINE__, "WARNING: insert model '$modname' failed!start=$start, nr_line=$nr_line", $params);
					return false;
				}
				//rlog(__FILE__, __LINE__, "sql=".$sql);
				
				$nr_read ++;
				if ($nr_read >= $nr)
					break;										
			}
			$left -= $nr;
			fclose($fp);			
		}
		
		$data['left'] = $left;
		$data['total'] = $rows;			
		
		if ($data['islast'] && $left == 0) { //备份完毕
			//$this->cleanRestoreFiles();
			setMsg("str_backup_restore_ok", $modname);
		}
		return true;
	}
	
	public function download($id)
	{
		$binfo = $this->getBinfoById($id);
		if (!$binfo)
			return false;
		
		//解压
		$fname = $binfo['name'];
		$zipfile = $this->_bdir.DS.$fname;
		
		header("Content-Disposition: attachment;filename=$fname"); 
		@readfile($zipfile);		
		setMsg("str_backup_download_ok", $zipfile);	
		exit;
	}
	
	public function upload(&$ioparams=array())
	{
		$ioparams['model'] = 'backup';
		$ioparams['oid'] = '0';
		
		$m = Factory::GetModel('file');
		$ioparams['nodbuploadcallback'] = true;
		$res = $m->upload($ioparams);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "upload failed!");
			return false;
		}
		
		if (count($res) != 1) {
			return false;
		}
		
		$finfo = array_pop($res);	
		//检查备份文件类型
		if ($finfo['extname'] != 'zip' 
				&& $finfo['extname'] != 'tar'
				&& $finfo['extname'] != 'tgz'
				&& $finfo['extname'] != 'gz'
				&& $finfo['extname'] != 'bz') {
			rlog(__FILE__, __LINE__, "invalid upload type! extname=".$finfo['extname']);
			return false;
		}
		
		//生成一个文件名
		$zipfile = $this->getBackupFileName($finfo['fullextname']);
		
		//if (function_exists("move_uploaded_file")) {
		//	$res = move_uploaded_file($finfo['opath'], $zipfile);
		//} else {
			$res = copy($finfo['opath'], $zipfile);
		//}
		
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "copy file from '{$finfo['opath']}' to '$zipfile' failed!");
			return false;
		}
		
		//clean
		@unlink($finfo['opath']);
		/*$res = $m->del($finfo['id']);
		if (!$res) {
			rlog(__FILE__, __LINE__, "delete file {$finfo['id']} failed!");
			return false;
		}*/
		return true;
	}
	
	
	
	public function delrestore($id)
	{
		$finfo = $this->getFinfoById($id);
		if (!$finfo)
			return false;
		
		$file = $this->_restoredir.DS.$finfo['name'];	
		$res = @unlink($file);	
		if (!$res) {
			rlog(__FILE__, __LINE__, "unlink file '$file' failed!");
			return false;
		}
		return $res;
	}
	
}