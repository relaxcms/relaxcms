<?php

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class CUpgrade
{
	protected $_options;
	
	public function __construct($options= array())
	{
		$this->_options = $options;
	}	
	
	public function CUpgrade($options= array()) 
	{
		$this->__construct($options);
	}
	
	public function upgrade($tgzfile)
	{
		if (!file_exists($tgzfile)) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no upgrade file '$tgzfile'!");
			return false;
		}
		
		$dir = RPATH_CACHE.DS."upgrade";
		if (!is_dir($dir))
			mkdir($dir);
		
		$z = Factory::GetTar();		
		if (!$z->extract($tgzfile, $dir)) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "extract failed, target=$tgzfile");
			return false;
		}
				
		$files = s_readdir($dir, "all");		
		$readme = $dir.DS."readme.txt";
		$udb = array();
		if (file_exists($readme )) {
						
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
		
		
		$cf = get_config();
		$verdb = explode(".", $cf['version']);
		$verdb2 = explode(".", $udb['version']);
		
		if ($verdb[0] > $verdb2[0]) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "upgrade major version error!");	
			return false;			
		} elseif ($verdb[1] > $verdb2[1]) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "upgrade minor version error!");	
			return false;
		} elseif ($verdb[2] > $verdb2[2]) {
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
						rlog(RC_LOG_ERROR, __FILE__, __LINE__, "import '$sql' failed");
						continue;
					} else {
						rlog(RC_LOG_INFO, __FILE__, __LINE__, "upgrade SQL ok.", $sql);	
					}
				}				
			} elseif ($ext == 'bat' || ($ext == 'sh' && $v != 'post_update.sh')) {
				$cmd = RPATH_CACHE.DS."upgrade".DS.$v;
				$cmd = escapeshellarg($cmd);
				if ($ext == 'sh') //添加可执行权限
					system("chmod a+x $cmd");
				$res = shell_exec($cmd);
				if (!$res) {
					rlog(RC_LOG_ERROR, __FILE__, __LINE__, "upgrade failed", "$cmd");	
					continue;
				}  else {						
					rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "upgrade shell ok", "$cmd");	
				}
			} else {
				$dst_path = RPATH_ROOT;
				$src_path = RPATH_CACHE.DS."upgrade".DS.$v;
				
				if (!is_dir($src_path)) {
					rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no src dir '$src_path'!");
					continue;
				}
				$res = s_copy($src_path, $dst_path);
				if (!$res) {
					rlog(RC_LOG_ERROR, __FILE__, __LINE__, "upgrade copy from '$src_path' to dst '$dst_path' failed!");
				} else {
					rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "upgrade dir '$dst_path' ok.");
				}
			}			
		}
		
		//post
		$cmd = $dir.DS."post_update.sh";
		if (file_exists($cmd)) {
			system("chmod a+x $cmd");	
			if (system($cmd, $res) === false ) {
				rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call system failed! cmd=$cmd, res=$res");
			}
		}
		
		//clean
		s_rmdir($dir);
				
		return true;
		 		
	}
		
	public function webpatch($argv)
	{
		if (!$argv) 
			exit("error!");		
		$nr = count($argv);
		if ($nr < 2)
			exit("error!");

		$tgzfile = $argv[1];
		
		$res = $this->upgrade($tgzfile);
		if (!$res) {
			echo "webpatch failed!\n";
		} else {
			echo "webpatch success.\n";
		}		
		return true;
	}
}
?>