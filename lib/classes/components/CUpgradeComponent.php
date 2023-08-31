<?php

/**
 * @file
 *
 * @brief 
 *   升级
 *
 */
defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class CUpgradeComponent extends CFileDTComponent
{
	public function __construct($name, $options)
	{
		parent::__construct($name, $options);		
	}
	
	public function CUpgradeComponent($name, $options)
	{
		$this->__construct($name, $options);
	}
	
	protected function show(&$ioparams = array())
	{
		$upload_max_filesize = ini_get('upload_max_filesize'); //2M
		$post_max_size = ini_get('post_max_size'); //2M

		//m转为字节
		$_upload_max_filesize = nformat_get_human_file_size($upload_max_filesize);
		$_post_max_size = nformat_get_human_file_size($post_max_size);

		if ($_upload_max_filesize > $_post_max_size) {
			$max_upload_max_filesize = $post_max_size;
			$_max_upload_max_filesize = $_post_max_size;
		} else {
			$max_upload_max_filesize = $upload_max_filesize;
			$_max_upload_max_filesize = $_upload_max_filesize;
		}

		//查询升级包是不是已经上传过了
		$uploaded = false;
		$uploadinfo = cache_array('upgrade');
		if ($uploadinfo) {
			$uploadinfo['size'] = nformat_human_file_size($uploadinfo['size']).'( '.number_format($uploadinfo['size']).' bytes)';
			$this->assign('uploadinfo', $uploadinfo);
			$uploaded = true;
		}
		$this->assign('uploaded', $uploaded);
			

		$upgradeclearonce = false;
		if (!file_exists(RPATH_CACHE.DS.'upgradeclearonce')) {
			$upgradeclearonce = true;
		}	
		
		$this->assign('upgradeclearonce', $upgradeclearonce);
		$this->assign('max_upload_max_filesize', $max_upload_max_filesize);
		$this->assign('_max_upload_max_filesize', $_max_upload_max_filesize);
		
		$updatetag = false;
		if (!file_exists(RPATH_CACHE.DS.'updatetag')) {
			$updatetag = true;
		}	
		$this->assign('updatetag', $updatetag);
		
		return false;
	}
	

	
	/**
	 * 上传升级包，并解压到缓存目录
	 *
	 * @param mixed $upfile This is a description
	 * @return mixed This is the return value description
	 *
	 * array
	 0 => string 'alter.sql' (length=9)
	 1 => string 'readme.txt' (length=10)
	 2 => string 'web' (length=3)
	 'readme' => 
	   array
	     'title' => string 'cm-2.4.0-sp1-upgrade 2.4.1 to 2.4.2' (length=35)
	     'dirname' => string 'web' (length=3)
	     'version' => string '2.4.0' (length=5)
	     'changelog' => string 'fixed bugs and upgrade 2.4.1 to 2.4.2' (length=37)
	
	
	 */
	protected function doUpload()
	{
		$uptypes = array('tar', 'gz', 'zip', 'lz', 'lic');

		$upfile = array_pop($_FILES);		
		//$upfile = get_var("upfile", "", "FILES");
		if (!$upfile) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "not found upfile");
			return false;
		}

		$filename = $upfile['name'];
		$tmpfile = $upfile['tmp_name'];
		$filesize = $upfile['size'];
		
		//缓存目录		
		$dir = RPATH_CACHE.DS."upgrade";
		if (!is_dir($dir))
			mkdir($dir);
		$file_ext = s_fileext($filename);				
		if (!in_array($file_ext, $uptypes)) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "unkown file format '$file_ext' ");
			return false;
		}

		//上传复制
		$target = $dir.DS.'patch_'.md5($filename).".$file_ext";
		if (!@copy($tmpfile, $target)) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "upgrade_upload_error, call copy() error,src=$tmpfile, target=$target, size=$filesize");
			return false;
		}

		/*
		if (function_exists('rkey_decrypt_file')) {
			if (!rkey_decrypt_file($target)) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call rkey_decrypt_file error, target:$target");
				return false;
			}
		}*/
		
		$upfile['target'] = $target;
		$upfile['ext'] = $file_ext;
		$upfile['from']= 'local upload';

		$res = cache_array('upgrade', $upfile);

		return $res;
	}

	//上传升级包
	protected function upload(&$ioparams=array())
	{
		$res = $this->doUpload();
		showStatus($res);
		return $res;
	}

	protected function cleanUpload($uploadinfo, &$ioparams=array())
	{
		unlink($uploadinfo['target']);
		unlink(RPATH_CACHE.DS.'upgrade.php');
		@s_rmdir(RPATH_CACHE.DS."upgrade");

		//清理一下缓存

		$cci18bdir = RPATH_CACHE.DS.$ioparams['_appname'].DS.'i18n';
		if (is_dir($cci18bdir))
			s_rmdir($cci18bdir);

		return true;
	}
	
	//del upgrade file
	protected function del(&$ioparams=array())
	{
		$uploadinfo = cache_array('upgrade');
		if (!$uploadinfo) {
			showStatus(-1);
			return  false;
		}

		$this->cleanUpload($uploadinfo, $ioparams);		
		showStatus(0);		
	}
	
	
	protected function doUpgradeByTgz($uploadinfo, &$ioparams=array())
	{
		$upgrade_res = false;

		$ext = $uploadinfo['ext'];
		$dir = RPATH_CACHE.DS."upgrade";
		$target = $uploadinfo['target'];

		//解压
		if ($ext == "zip") 
			$z = Factory::GetZip();
		else 
			$z = Factory::GetTar();

		if (!$z->extract($target, $dir)) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "extract failed, target=$target");
			return false;
		}

		//读readme.
		$files = s_readdir($dir, "all");

		$readme = $dir.DS."readme.txt";
		$udb = array();
		if (file_exists($readme )) {
			
			
			//解readme
			$lines = file($readme);
			foreach($lines as $line)
			{
				$line = trim($line);
				if (!$line)
					continue;
				
				if (substr($line, 0, 2) == "--") 
					continue;

				
				list($var, $value) = explode("=", $line);
				$var = trim($var);
				
				if ($var == "") 
					continue;
				
				$value = trim($value);			
				$udb[$var] = $value;
			}
			$files['readme'] = $udb;	
		}
		
				
		//检查版本
		$cf = get_config();
		$verdb = explode(".", $cf['version']);
		$verdb2 = explode(".", $udb['version']);

		if ($verdb[0] > $verdb2[0]) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "upgrade major version error!");	
			return false;			
		} elseif ($verdb[0] == $verdb2[0] && $verdb[1] > $verdb2[1]) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "upgrade minor version error!");	
			return false;
		} elseif ($verdb[0] == $verdb2[0] 
			&& $verdb[1] == $verdb2[1] 
			&& $verdb[2] > $verdb2[2]) {
			//查一下是否有-
			$vd1 = explode('-', $verdb[2]);
			$vd2 = explode('-', $verdb2[2]);
			$p1 = array_shift($vd1);
			$p2 = array_shift($vd2);
			if ($p1 < $p2) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "upgrade patch version error!");
				return false;
			}
		}
		
		$db = Factory::GetDBO();
		foreach ($files as $key=>$v) {
			if (is_array($v))
				continue;
			if ($v == "readme.txt" ) {
				$upgrade_log = file_get_contents(RPATH_CACHE.DS."upgrade".DS.$v);
				if ($upgrade_log) {
					rlog(RC_LOG_INFO, __FILE__, __LINE__, $upgrade_log);
				}
				continue;
			}

			$ext = s_fileext($v);	
			if ($ext == 'sql') {
				$sql = RPATH_CACHE.DS."upgrade".DS.$v;
				if (!file_exists($sql)) {
					rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "WARNING : no upgrade sql file '$sql'");
					continue;
				}
				$data = file_get_contents($sql);
				if (strstr($data, "\$\$")) {
					if (!$db->exec_procedure_file($sql,"\$\$")) {
						rlog(RC_LOG_ERROR, __FILE__, __LINE__, "WARING exec_procedure_file '$sql' failed");
						continue;
					} else {
						rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "str_upgrade_sql_ok", $sql);
					}
				} else {
					if (!$db->import($sql)) {
						rlog(RC_LOG_ERROR, __FILE__, __LINE__, "WARING exec_script '$sql' failed");
						continue;
					} else {
						rlog(RC_LOG_INFO, __FILE__, __LINE__, "str_upgrade_sql_ok", $sql);	
					}
				}				
			} elseif ($ext == 'bat' || ($ext == 'sh' && $v != 'post_update.sh')) {
				$cmd = RPATH_CACHE.DS."upgrade".DS.$v;
				$cmd = escapeshellarg($cmd);
				if ($ext == 'sh') //添加可执行权限
					system("chmod a+x $cmd");
				$res = shell_exec($cmd);
				if (!$res) {
					rlog(RC_LOG_ERROR, __FILE__, __LINE__, "str_upgrade_failed", "$cmd");	
					continue;
				}  else {						
					rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "str_upgrade_shell_ok", "$cmd");	
				}
			} else {
				$dst_path = RPATH_ROOT;
				$src_path = RPATH_CACHE.DS."upgrade".DS.$v;
				//升级脚本
				if (!is_dir($src_path)) {
					rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "str_upgrade_no_dir", $src_path );
					continue;
				}
				$res = s_copy($src_path, $dst_path);
				if (!$res) {
					rlog(RC_LOG_ERROR, __FILE__, __LINE__, "upgrade copy from '$src_path' to dst failed!");
				} else {
					rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "str_upgrade_ok", $src_path);
				}

				
			}			
			$upgrade_res = true;
		}
		
		//post
		$cmd = RPATH_CACHE.DS."upgrade".DS."post_update.sh";
		if (file_exists($cmd)) {//升级后处理
			system("chmod a+x $cmd");	
			if (!system($cmd, $res) ) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call system failed! cmd=$cmd, res=$res");
			}
		} else {
		}
						
		return $upgrade_res;
	}

	protected function doUpgradeLZ($uploadinfo, &$ioparams=array())
	{
		return false;
	}

	protected function doUpgrade($uploadinfo, &$ioparams=array())
	{

		$res = false;

		switch ($uploadinfo['ext']) {
			case 'tgz':
			case 'gz':
			case 'zip':
				$res = $this->doUpgradeByTgz($uploadinfo);
				break;
			
			case 'lz':
			case 'lic':
				# code...
				$res = $this->doUpgradeLZ($uploadinfo);
				break;
			
			default:
				# code...
				break;
		}

		return $res;
	}


	//升级
	protected function upgrade(&$ioparams=array())
	{
		$uploadinfo = cache_array('upgrade');
		if (!$uploadinfo) {
			showStatus(-1);
			return  false;
		}

		$res = $this->doUpgrade($uploadinfo, $ioparams);
		if (!$res) {
			showStatus(-1);
			return  false;			
		}

		//清理
		$this->cleanUpload($uploadinfo, $ioparams);

		showStatus(0);
	}
}

?>