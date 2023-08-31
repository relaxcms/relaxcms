<?php
/**
 * @file
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class CBackupComponent extends CFileDTComponent
{
	
	function __construct($name, $options)
	{
		parent::__construct($name, $options);
	}
	
	function CBackupComponent($name, $options)
	{
		$this->__construct($name, $options);
	}
		
	public function show(&$ioparams=array(), $force=-1)
	{
		$this->initActiveTab(3, $force);
		
		
		$m = Factory::GetModel('backup');
		$udb = $m->getModels();	
		
		
			
		$this->assign("udb", $udb);
		
		$bdb = $m->getBackupFiles();
		foreach ($bdb as $key=>&$v) {
			$v['_atime'] = tformat($v['atime']);
		}		
		$this->assign("bdb", $bdb);
		
		//fdb
		$fdb = $m->getRestoreList();
		foreach ($fdb as $key=>&$v) {
			$v['_atime'] = tformat($v['atime']);
		}		
		$this->assign("fdb", $fdb);
		
	}
	
	protected function backout(&$ioparams=array())
	{
		$id = $this->_id;
		
		$wqlen = $this->requestInt('wqlen');
		
		if (!isset($_REQUEST['left'])) {
			$left = -1;
		} else {
			$left = $this->requestInt('left');
		}
		
		$data = array();
		$data['left'] = $left;
		$data['id'] = $id;
		$data['islast'] = $wqlen == 0;
		if (!$id) {
			showStatus(-1, $data);
		}
		
		$m = Factory::GetModel('backup');
		$res = $m->backout($id, $data);
		showStatus($res, $data);
		
		return true;
	}
	
	protected function backup()
	{
		$db = Factory::GetDBO();
		
		$tables = $db->show_tables();
		$left = count($tables);
		
		$udb = array();		
		$i = 0;
		$cache = Factory::GetCache();
		
		foreach($tables as $key=>$v) {
			$name = $v['Name'];
						
			//返回数据，回带行数						
			$data = $db->backup_out($name, 0, $v['Rows']);
			$file = $name;
			
			$left --;
			$res = $cache->cache_backup($file, $data, $left, $start);
		}
				
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "str_backup_out_ok");			
		show_message('str_backup_out_ok', $this->_base);
	}
		
	protected function delete(&$ioparams=array())
	{
		$id = $this->_id;
		$m = Factory::GetModel('backup');
		$res = $m->del($id);
		
		showStatus($res);
		
		return true;
	}
	
	protected function delrestore(&$ioparams=array())
	{
		$id = $this->_id;
		$m = Factory::GetModel('backup');
		$res = $m->delrestore($id);
		
		showStatus($res?0:-1);
		
		return true;
	}
	
	protected function delall(&$ioparams=array())
	{
		$params = array();
		$this->getParams($params);
		$ids = $params['id'];
				
		$m = Factory::GetModel('backup');
		foreach ($ids as $key=>$v) {
			$res = $m->delrestore($v);
		}
		showStatus(0);
		return true;
	}
	
	protected function backin(&$ioparams=array())
	{
		$id = $this->_id;
		
		$wqlen = get_int('wqlen');
		
		if (!isset($_REQUEST['left'])) {
			$left = -1;
		} else {
			$left = get_int('left');
		}
		
		$data = array();
		$data['left'] = $left;
		$data['id'] = $id;
		$data['islast'] = $wqlen == 0;
		if (!$id) {
			showStatus(-1, $data);
		}
		
		$m = Factory::GetModel('backup');
		$res = $m->backin($id, $data);
		showStatus($res, $data);
		
		return true;
	}
	
	
	protected function restore(&$ioparams=array())
	{
		$id = $this->_id;
		$m = Factory::GetModel('backup');
		$res = $m->uncompressRestore($id);
		if (!$res) {
			show_error('str_backup_restore_failed');
			return false;
		}
		$this->show($ioparams, 1);
	}
	
	protected function download(&$ioparams=array(), $fid=0)
	{
		$id = $this->_id;
		$m = Factory::GetModel('backup');
		$res = $m->download($id);
		exit();	
	}
	
	protected function download2()
	{
		$prefix = '';
		$zip = get_var('zip');
		$file = RPATH_CACHE.DS."backup".DS.$zip;	
		
		//加载
		if (function_exists('rkey_encrypt_file')) {
			$file = RPATH_CACHE.DS."backup".DS.'en-'.$zip;
			if (!copy(RPATH_CACHE.DS."backup".DS.$zip, $file));
				rlog('call copy error'); 
			
			if (!rkey_encrypt_file($file))
				rlog('call 	rkey_encrypt_file error');
				
			$prefix = 'en-';
			$is_encrypt = true;
		}
		
		header("Content-Disposition: attachment;filename={$prefix}{$zip}"); 
		@readfile($file);		
		rlog("str_backup_download_ok", $file);		
		if ($is_encrypt)
			unlink($file);	
		exit();	
	}
	
	
	protected function upload_restore()
	{
		if ($this->_sbt) {
			if (!function_exists('rkey_decrypt_file')) {
				set_error('str_error_not_support');
				return false;
			}
			
			//上专
			$uploadfile = $_FILES['upfile'];
			$att = Factory::GetAttach();
			$filename = $att->upload_file($uploadfile, null, RPATH_CACHE);
			if (!$filename) {
				set_error('str_upload_error');
				return false;
			}
			
			$file = RPATH_CACHE.DS.$filename;
			$res = rkey_decrypt_file($file);
				
			$dir = RPATH_CACHE;
			$cache = Factory::GetCache();
			$udb = $cache->get_zip_info($dir.DS.$filename, true);
			
			$left = count($udb);
			
			$db = Factory::GetDBO();
			foreach ($udb as $key=>$v) {
				$sqlfile = $dir.DS.$v['name'];
				$db->backup_in($sqlfile, 0, 0, $total);
				unlink($sqlfile);
			}
			unlink($dir.DS.$filename);
			rlog('str_backup_restore_from_local_ok');
			show_message('str_backup_restore_from_local_ok', $this->_base);
		}
	}
	
	protected function simpleupload(&$ioparams=array())
	{
		$m = Factory::GetModel('backup');
		$res = $m->upload($ioparams);
		showStatus($res);
	}
}