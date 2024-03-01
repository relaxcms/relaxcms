<?php


defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

/**
 * @file
 *
 * @brief 
 * 函数库
 * 解决PHP函数库不足及本系统实现特性
 *
 */

define('IPVER_INET4', 4);
define('IPVER_INET6', 6);

/**
 * @api {get} /user/:id Request User information
 * @apiName get_client_ip
 * @apiVersion 0.8.8
 * @apiGroup Net
 *
 *
 * @apiSuccess {String} ip Client IP
 */

function get_client_ip()
{
	if (!empty($_SERVER['HTTP_CLIENT_IP']))
	{
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	}
	elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
	{
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	}
	else
	{
		$ip = $_SERVER['REMOTE_ADDR'];
	}
	return $ip;
}


///////////////////////////////////////////////字符///////////////////////////

//十六进制字串到ASCII字符
function HexToStr($hex){
	$ret="";
	for($i=0;$i<strlen($hex);$i+=2)
		$ret.=chr(hexdec(substr($hex,$i,2)));
	return $ret;
}
//ASCII字符到十六进制字串
function StrToHex($str){
	$ret="";
	for($i=0;$i<strlen($str);$i++){
		$ch = dechex(ord($str[$i]));
		if(strlen($ch) == 1) $ch="0".$ch;
		$ret.=$ch;
	}
	return strtoupper($ret);
}


///////////////////////////////////////////////日志///////////////////////////

define('RC_LOG_EMERG', 0);
define('RC_LOG_ALERT', 1);
define('RC_LOG_CRIT',  2);
define('RC_LOG_ERROR', 3);
define('RC_LOG_WARNING', 4);
define('RC_LOG_NOTICE', 5);
define('RC_LOG_INFO',  6);
define('RC_LOG_DEBUG', 7);
//RC log
define("RC_LOG_DEFLEVEL", RC_LOG_DEBUG);
define("RC_LOG_LOGFILE", RPATH_CACHE.DS.'rlog.log');
function rlog()
{
	$file = RC_LOG_LOGFILE;
	$args = func_get_args();
	
	$l = Factory::GetLog();
	$logcfg = $l->get_logcfg();
	
	$loglevel = isset($logcfg['loglevel'])?intval($logcfg['loglevel']):RC_LOG_DEFLEVEL;
	
	//探测是否日志等级
	$level = 0;
	$errorcode = 0;
	if (is_numeric($args[0])) {
		$val = intval($args[0], 0);
		$level = $val&0xf;		
		$errorcode = $val&0xfffffff0 ;		
		if ($level > $loglevel)
			return ;
		else if ($level <= RC_LOG_WARNING && $errorcode == 0)
			$errorcode = -1;
	}		
	
	$fd = fopen($file, "a+");	
	
	$timestr = tformat_current();
	$client_ip = isset($_SERVER['REMOTE_ADDR'])?$_SERVER['REMOTE_ADDR']:'127.0.0.1';
	
	fprintf($fd, "%s : client %s : %d/0x%08X", $timestr, $client_ip, $level, $errorcode);
	foreach ($args as $a)
		fprintf($fd, ": %s", print_r($a, true));
	if ($level < RC_LOG_WARNING) 
		fprintf($fd, ": %s", print_r(error_get_last(), true));
	
	fprintf($fd, "\n");
	fclose($fd);	
}

function TDEBUG($res)
{
	$args = func_get_args();	
	if($res) {
		echo "OK<br>";
	}
	foreach ($args as $a)
		var_dump($a);		
	echo "<br>";	
}



/**
 * 日志
 *
 * @param mixed $$level This is a description
 * @param mixed $$message This is a description
 * @return mixed This is the return value description
 *
 */
function slog($level, $errno=0, $message='', $oldParams='')
{	
	$uid = get_uid();
	
	//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "$level/$action/$status", $oldParams, $newParams);
	
	$m = Factory::GetModel('log');
	
	//ts, ip, des, uid, subsys, loglevel, cmd, object, oid
	$ip = get_client_ip();
	
	$desc = $message; 
	
	$newobj = '';
	if ($errno === false) {
		$errno = -1;
	} else if ($errno === true) {
			$errno = 0;
		} else {
			$errno = intval($errno);
		}	
	$status = $errno >= 0?1:0;
	
	
	$oldobj = $oldParams?serialize($oldParams):'';
	
	$params = array(
			'uid'=>$uid,
			'ip'=>$ip,
			'level'=>$level,
			'description'=>$desc,
			'action'=>$action,
			'errno'=>$errno,
			'status'=>$status,
			'oldobj'=>$oldobj,
			);
	
	$res = $m->set($params);
	
	return $res;
}

function slog_debug($message)
{
	$message = i18n($message);
	$args = func_get_args();
	$phrase = array_shift($args);
	$message = vsprintf($message, $args);		
	slog(RC_LOG_DEBUG, 0, $message);
}

function slog_info($message)
{
	$message = i18n($message);
	$args = func_get_args();
	$phrase = array_shift($args);
	$message = vsprintf($message, $args);		
	slog(RC_LOG_INFO, 0, $message);
}

function slog_error($message)
{
	$message = i18n($message);
	$args = func_get_args();
	$phrase = array_shift($args);
	$message = vsprintf($message, $args);		
	slog(RC_LOG_ERROR, -1, $message);
}


function setMsg($errno)
{
	$key = is_numeric($errno)?'status_'.$errno:$errno;
	$msg = get_i18n($key);
	if ($msg) {
		$args = func_get_args();
		$data = array_shift($args);
		$msg = vsprintf($msg, $args);		
		$app =  Factory::GetApp();
		if ($app)
			$app->setMsg(RC_LOG_INFO, $msg);
	}
	
	slog(RC_LOG_INFO, $errno, $msg?$msg:$key, $args);	
}

function setErr($errno)
{
	$key = is_numeric($errno)?'status_'.$errno:$errno;
	$msg = get_i18n($key);
	if ($msg) {
		$args = func_get_args();
		$phrase = array_shift($args);
		$msg = vsprintf($msg, $args);	
		$app =  Factory::GetApp();
		if ($app)
			$app->setMsg(RC_LOG_ERROR, $msg);
	}		
	slog(RC_LOG_ERROR, $errno, $msg?$msg:$key, $args);
}

///////////////////////////////////////////////文件操作///////////////////////////

function s_filename($filename)
{
	$name = str_replace(DS, '/', $filename);
	$pos = strrpos($name, '/');
	if ($pos !== false) {
		$name = substr($name, $pos+1);
	}
	return $name;
	
}

function s_url2filename($url)
{
	/*
	Array
	(
	   [scheme] => http
	   [host] => hostname
	   [user] => username
	   [pass] => password
	   [path] => /path
	   [query] => arg=value
	   [fragment] => anchor
	)
	/path*/
	
	$udb = parse_url($url);
	
	if (!isset($udb['path'])) {
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "invalid url=$url");
		return false;
	}
	
	$path = $udb['path'];
	
	$name = $path;
	$pos = strrpos($path, '/');
	if ($pos > 0) {
		$name = substr($path, $pos+1);
	}
	return $name;
	
}

function s_uri2filename($uri, &$outpath='')
{
	$name = $uri;
	$pos = strrpos($uri, '/');
	if ($pos !== false) {
		$name = substr($uri, $pos+1);
		$outpath = substr($uri, $pos);
	}
	return $name;
	
}


function s_url2hostname($url)
{
	/*
	Array
	(
	   [scheme] => http
	   [host] => hostname
	   [user] => username
	   [pass] => password
	   [path] => /path
	   [query] => arg=value
	   [fragment] => anchor
	)
	/path*/
	
	$udb = parse_url($url);
	
	if (!isset($udb['host'])) {
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "no host! url=$url");
		return false;
	}
	
	$host = $udb['host'];
	
	$name = $host;
	$pos = strpos($host, ':');
	if ($pos > 0) {
		$name = substr($host, 0, $pos);
	}
	return $name;	
}


function s_dirname($filename)
{
	return dirname($filename);
}


function s_fileext($filename)
{
	$pos = strrpos($filename, '.');
	$ext = strtolower(substr($filename, $pos+1));
	return  $ext;
}



/**
 * 取扩展名，回带不带扩展名的文件名
 *
 * @param mixed $name This is a description
 * @return mixed This is the return value description
 *
 */
function s_extname(&$name)
{
	$pos = strrpos($name, '.');
	if ($pos === false)
		return '';
	
	$ext = strtolower(substr($name, $pos+1));
	$name = substr($name, 0, $pos);
	return  $ext;
}

function s_filename2name(&$filename)
{
	$name = $filename;
	$pos = strrpos($name, '.');
	if ($pos === true) {
		$ext = strtolower(substr($name, $pos+1));
		$name = substr($name, 0, $pos);
	}
	return  $name;
}

function s_extnames($filename)
{
	$extnames = array();
	
	$extname = s_extname($filename);
	$extname2 = s_fileext($filename);
	$fullextname = $extname;
	if (CFileType::ext2typeid($extname2) > 0) {//是扩展名
		$extname2 = s_extname($filename);
		$fullextname = $extname2.'.'.$extname;
	} else {
		$extname2 = '';
	}
	
	$extnames['extname'] = $extname;
	$extnames['extname2'] = $extname2;
	$extnames['fullextname'] = $fullextname;
	
	return $extnames;
}



function s_extname2(&$name, &$fullextname='')
{
	$extname = s_extname($name);
	$extname2 = s_fileext($name);
	$fullextname = $extname;
	if (CFileType::ext2typeid($extname2) > 0) {//是扩展名
		$extname2 = s_extname($name);
		$fullextname = $extname2.'.'.$extname;
	} 	
	
	return $extname;
}



function s_unslash($path) 
{
	if ($path[strlen($path)-1] == DS) {
		$path = substr($path, 0, strlen($path) -1);
	}
	return $path;
}

function s_hslash($path) 
{
	if ($path[0] != '/') 
		$path = '/'.$path;
	return $path;
}

function s_slashify($path) 
{
	if ($path[strlen($path)-1] != '/') {
		$path = $path."/";
	}
	return $path;
}

/*
 '/' 开头
 */
function is_start_slash($path) 
{
	return $path[0] == '/';
}

function is_start_with($str, $c) 
{
	return substr($str, 0, strlen($c)) == $c;
}

function is_start_end($str, $c) 
{
	return $str[strlen($str)-strlen($c)] == $c;
}

/*
 'http' or 'https'开头
 */
function is_url($path) 
{
	$udb = parse_url($path);
	if (!$udb)
		return false;
	if (!isset($udb['scheme']))
		return false;
	
	$schema = $udb['scheme'];
	return $schema == 'http' ||  $schema == 'https';
}

/**
 * 读文件
 *
 * @param mixed $filename This is a description
 * @param mixed $method This is a description
 * @return mixed This is the return value description
 *
 */
function s_read($filename, $method = "rb")
{
	$data = false;
	if ($handle=@fopen($filename, $method)) 	{
		flock($handle, LOCK_SH);
		$sz = filesize($filename);
		if ($sz > 0) {
			$data = fread($handle, $sz);
			fclose($handle);
		}
	}
	return $data;
}


/**
 * 写文件
 *
 * @param mixed $filename This is a description
 * @param mixed $data This is a description
 * @param mixed $method This is a description
 * @param mixed $iflock This is a description
 * @param mixed $check This is a description
 * @param mixed $chmod This is a description
 * @return mixed This is the return value description
 *
 */
function s_write($filename, $data, $method="rb+",$iflock=1, $check=1, $chmod=1)
{
	touch($filename);
	$handle = fopen($filename,$method);
	if ($iflock)
		flock($handle, LOCK_EX);
	
	$res = fwrite($handle,$data);
	if ($res === false) {
		fclose($handle);
		return false;
	}
	
	if ($method == "rb+") 
		ftruncate($handle, strlen($data));
	
	fclose($handle);
	if ($chmod)
		@chmod($filename, 0777);
	
	return $res;
}

/**
 * s_rmdir
 * 删除目录
 *
 * @param mixed $dir This is a description
 * @return mixed This is the return value description
 *
 */
function s_rmdir($dir) 
{
	if (!is_dir($dir))     
		return  false;     
	
	if (($path = realpath($dir)) !== FALSE) {
		if (is_dir($path)) {
			$dh = opendir($path);
		} else {
			return false;
		}
		
		while (($file = readdir($dh)) !== false) {
			if ($file != '.' && $file != '..') {
				if (is_dir($path . DS . $file)) {
					s_rmdir($path . DS . $file);
				} else {
					$filepath = $path . DS . $file;
					//rlog(RC_LOG_DEBUG, __FILE__, __LINE__,"unlink '$filepath'");
					@unlink($filepath);
				}
			}
		}
		closedir($dh);
		@rmdir($path);			
	} else {
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__,"call realpath $dir={$dir} failed!");
	}
	return true;
}

/**
 * s_copy
 * 
 * 复制目录
 *
 * @param mixed $srcdir This is a description
 * @param mixed $dstdir This is a description
 * @return mixed This is the return value description
 *
 */
function s_copy($srcdir, $dstdir)
{
	if (!is_dir($dstdir))
	{
		$nowdir='';
		$dstdirarray = explode(DS, $dstdir);
		foreach ($dstdirarray as $newdir) {
			$nowdir.=$newdir.DS;
			if (!is_dir($nowdir)) 
				@mkdir($nowdir);
		}
	}
	
	$dir = @opendir($srcdir);
	while (($file = readdir($dir)) !== false) {
		if ($file=='.' || $file=='..') 
			continue;
		if (is_dir($srcdir.DS.$file)) {
			s_copy($srcdir.DS.$file,$dstdir.DS.$file);
		} else {
			$res = copy($srcdir.DS.$file, $dstdir.DS.$file);
			if (!$res) {
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "call copy failed! src=".$srcdir.DS.$file.', dst='.$dstdir.DS.$file);
			}
		}
	}
	closedir($dir);
	return true;
}

/**
 * s_mkdir
 * 
 * 创建目录及子目录
 *
 * @param mixed $dirs This is a description
 * @return mixed This is the return value description
 *
 */
function s_mkdir($dirs)
{
	return @mkdir($dirs, 0777, true);
	/*if (!is_dir($dirs)) {
		$nowdir='';
		$dstdirarray = explode(DS, $dirs);
		foreach ($dstdirarray as $key=>$newdir) {
			$nowdir .= $newdir.DS;
			if (!is_dir($nowdir)) 
				@mkdir($nowdir);
		}
	}
	return true;*/
}


/**
 * s_tmpdir
 * 
 * Create and return a tmp directory
 * 
 * @return string Path to tmp directory
*/
function s_tmpdir()
{
	$path = tempnam('', 'RELAX_');
	unlink($path);
	mkdir($path);
	return $path;
}


/**
 * s_clean_dirname
 * 
 * 清理目录名中的不合法了字符
 *
 * @param mixed $dirname This is a description
 * @param mixed $ds This is a description
 * @return mixed This is the return value description
 *
 */
function s_clean_dirname($dirname, $ds=DS)
{
	$dirname = trim($dirname);		
	if (empty($dirname)) {
		$dirname = RPATH_BASE;
	} else {
		$dirname = preg_replace('#[/\\\\]+#', $ds, $dirname);
	}		
	return $dirname;
}


/**
 * s_unlink
 * 
 * 删除文件，支持*通配符，如
 *
 * @param mixed $filename /tmp/data/*.sql
 * @return mixed This is the return value description
 *
 */
function s_unlink($filename)
{
	if (($pos = strpos($filename, '*'))  === false) {
		@unlink($filename);
	} else {
		$dir = dirname($filename);
		if (!is_dir($dir))
			return false;
		
		$suffix = substr($filename, $pos, -1);
		$d = dir($dir);
		if (!$d)
			return false;
		
		$len = strlen($suffix);
		
		while (false !== ($entry = $d->read())) {
			if ($entry == "." || $entry == "..")
				continue;
			
			$file_ext = substr($entry, -$len, $len);
			if ($file_ext == $suffix)
				unlink($dir.DS.$entry);
		}
		
		$d->close();
	}
	
	return true;
}


/**
 * s_readdir
 *
 * @param mixed $dir This is a description
 * @param mixed $opt This is a description
 * @return mixed This is the return value description
 *
 */
function s_readdir($dir, $opt=false)
{
	if (!is_dir($dir))     
		return false;     
	
	$res = array();
	$handle = @opendir($dir);
	while(($file = @readdir($handle)) !== false) 	{
		if ($file != '.' && $file != '..') {
			$is_dir = is_dir($dir . '/' . $file);				
			switch($opt) {
				case 'files':
					!$is_dir && $res[] = $file;
					break;
				case 'dirs':
					$is_dir && $res[] = $file;
					break;
				case 'all':
					$res[] = $file;
					break;
				default:
					$res[] = $file;
					break;
			}
		}
	}
	closedir($handle);
	return $res; 
}

///////////////////////////////页面//////////////////////////////////

/**
 * 分页HTML
 *
 * @param mixed $page 页号
 * @param mixed $total 总页数
 * @param mixed $url 点击下一页URL
 * @param mixed $stc 是否静态化
 * @return mixed 成功返回分页HTML格式字串
 *
 */
function mk_page_html($start, $count_per_page, $total, $page, $page_total, $keyword, $url, $stc=false)
{
	$first = "";
	$tmp = "";
	$pages = "";
	
	$end = $start + $count_per_page;
	if ($end > $total)
		$end = $total;
	
	$pargesuminfo = i18n('str_page_format', $total, $start+1, $end, $page, $page_total);
	
	if ($page_total <= 1 || !is_numeric($page))
	{
		return '';
	}
	else
	{
		$flag = 0;
		if ($stc)
		{
			$tmp = $url.".htm";
		}
		else
		{
			$tmp = $url."/1";
		}
		
		$pages = "<div class='pages'><a href='$tmp' style='font-weight:bold'>&laquo;</a>";
		for ($i=$page-3; $i<=$page-1; $i++)
		{
			if ($i < 1) continue;
			
			if ($stc)
			{
				if ($i == 1)
				{
					$tmp = $url.".htm";
				}
				else
				{
					$tmp = $url."_$i.htm";
				}
			}
			else
			{
				$tmp = $url."/$i";
			}
			
			$pages .= "<a href='$tmp'>$i</a>";
		}
		
		$pages.="<b> $page </b>";
		if($page < $page_total)
		{
			for($i=$page+1; $i<=$page_total; $i++)
			{
				
				if ($stc == 1)
				{
					$tmp = $url."_$i.htm";
				}
				else
				{
					$tmp = $url."/$i";
				}
				
				$pages .= "<a href='$tmp'>$i</a>";
				
				$flag++;
				if($flag == 4) break;
			}
		}
		
		if ($stc == 1)
		{
			$tmp = $url."_$page_total.htm";
		}
		else
		{
			$tmp = $url."/$page_total";
		}
		
		$pages .= "<a href='$tmp' style='font-weight:bold'>&raquo;</a> <span class='fl'> $pargesuminfo <span></div>";
		
		return $pages;
	}
}


function mk_page_html2($page, $total, $url, $stc=0)
{
	$first = "";
	$tmp = "";
	$pages = "";
	
	if($total <= 1 || !is_numeric($page))
	{
		return '';
	}
	else
	{
		
		$flag = 0;
		if ($stc == 1)
		{
			$arr = explode('.', $url, 2);
			$list_name = $arr[0];
			$ext_name = $arr[1];
			$tmp = $url;
		}
		else
		{
			$tmp = $url."page=1";
		}
		
		$pages = "<a href='$tmp' style='font-weight:bold'>&laquo;</a>";
		for ($i=$page-3; $i<=$page-1; $i++)
		{
			if ($i < 1) continue;
			
			if ($stc == 1)
			{
				if ($i == 1)
				{
					$tmp = $url;
				}
				else
				{
					$tmp = $list_name."_$i.$ext_name";
				}
			}
			else
			{
				$tmp = $url."page=$i";
			}
			
			$pages .= "<a href='$tmp'>$i</a>";
		}
		
		$pages.="<b> $page </b>";
		if($page < $total)
		{
			for($i=$page+1; $i<=$total; $i++)
			{
				
				if ($stc == 1)
				{
					$tmp = $list_name."_$i.$ext_name";
				}
				else
				{
					$tmp = $url."page=$i";
				}
				
				$pages .= "<a href='$tmp'>$i</a>";
				
				$flag++;
				if($flag == 4) break;
			}
		}
		
		if ($stc == 1)
		{
			$tmp = $list_name."_$total.$ext_name";
		}
		else
		{
			$tmp = $url."page=$total";
		}
		
		$pages .= "<a href='$tmp' style='font-weight:bold'>&raquo;</a> $str_page_no:$page/$total";
		
		return $pages;
	}
	
}

//首页 上一页 下一页 尾页　共 N 页
function mk_page_html3($page, $total, $url, $stc=0)
{
	$first = "";
	$tmp = "";
	$pages = "";
	
	$str_page_first = i18n('str_page_first');
	$str_page_last = i18n('str_page_last');
	$str_page_next = i18n('str_page_next');
	$str_page_prev = i18n('str_page_prev');	
	$str_page_total = i18n('str_page_total');	
	$str_page = i18n('str_page');	
	
	$flag = 0;
	if ($total  <= 1) {
		$first = $str_page_first;
		$last = $str_page_last;
		$next = $str_page_next;
		$prev = $str_page_prev;			
	} else {
		if ($stc == 1)
		{
			$tmp = $url.".htm";
			$first = "<a href='$tmp'>".$str_page_first."</a>";
			$last = "<a href='$tmp'>".$str_page_last."</a>";
		}
		else
		{
			$first = "<a href='$url&page=1'>".$str_page_first."</a>";
			$last = "<a href='$url&page=$total'>".$str_page_last."</a>";
		}
		
		if ($page < $total) { 
			$p = $page + 1;
			$next = "<a href='$url&page=$p'>".$str_page_next."</a>";
		} else {
			$next = $str_page_next;
		}
		
		if ($page > 1) { 
			$p = $page - 1;
			$prev = "<a href='$url&page=$p'>".$str_page_prev."</a>";
		} else {
			$prev = $str_page_prev;
		}
	}	
	
	$res = "$first $prev $next  $last $str_page_total $total str_page"; 
	return $res;
}

function ifcheck($var, $out)
{
	$checks = array();
	if ($var == 1)
	{
		$checks[$out.'_Y']="CHECKED";
		$checks[$out.'_N']="";
	}
	else
	{
		$checks[$out.'_Y']="";
		$checks[$out.'_N']="CHECKED";
	}
	
	return $checks;
}

function setchecked($key, &$params=array())
{
	$params[$key.'_checked'] = isset($params[$key]) && $params[$key]?'checked':'';	
}


/**
 * get_common_select
 * 通用selest
 *
 * @param mixed $key This is a description
 * @param mixed $default This is a description
 * @return mixed This is the return value description
 *
 */
function get_common_select($key, $default)
{
	$res = "";		
	$lang = get_i18n();
	if (substr($key, 0, 4) != 'sel_')
		$key = 'sel_'.$key;
	if (array_key_exists($key, $lang)){
		$udb = $lang[$key];			
		foreach ($udb as $k=>$v){
			$selected = $default == $k ? 'selected' : '';
			$res .= "<option value='$k' $selected > $v</option>";
		}
	}
	
	return $res;
}

function get_month_select($key, $default)
{
	$res = "";		
	for ($k=1; $k<=12; $k++) {
		$selected = $default == $k ? 'selected' : '';
		$res .= "<option value='$k' $selected > $k </option>";
	}
	
	return $res;
}

function get_day_select($key, $default)
{
	$res = "";		
	for ($k=1; $k<=31; $k++) {
		$selected = $default == $k ? 'selected' : '';
		$res .= "<option value='$k' $selected > $k </option>";
	}
	
	return $res;
}

function get_hour_select($key, $default)
{
	$res = "";		
	for ($k=0; $k<=23; $k++) {
		$selected = $default == $k ? 'selected' : '';
		$res .= "<option value='$k' $selected > $k </option>";
	}
	
	return $res;
}


function get_minute_select($key, $default)
{
	$res = "";		
	for ($k=0; $k<=59; $k++) {
		$selected = $default == $k ? 'selected' : '';
		$res .= "<option value='$k' $selected > $k </option>";
	}
	
	return $res;
}


function get_second_select($key, $default)
{
	$res = "";		
	for ($k=0; $k<=59; $k++) {
		$selected = $default == $k ? 'selected' : '';
		$res .= "<option value='$k' $selected > $k </option>";
	}
	
	return $res;
}


/**
 * get_common_name
 * 提取公共选项名称
 *
 * @param mixed $key This is a description
 * @param mixed $value This is a description
 * @return mixed This is the return value description
 *
 */
function get_common_name($key, $value)
{
	$res = "";		
	$lang = get_i18n();
	if (substr($key, 0, 4) != 'sel_')
		$key = 'sel_'.$key;
	if (isset($lang[$key])) {
		foreach ($lang[$key] as $k=>$v){
			if ($k == $value) 
				return $v;
		}
	}
	
	return $value;
}


/**
 * get_common_key
 *
 * @param mixed $key This is a description
 * @param mixed $value This is a description
 * @return mixed This is the return value description
 *
 */
function get_common_key($key, $value)
{
	$res = "";		
	$lang = get_i18n();
	if (substr($key, 4) != 'sel_')
		$key = 'sel_'.$key;
	if (array_key_exists($key, $lang)){
		$udb = $lang[$key];			
		foreach ($udb as $k=>$v){
			if ($v == $value) 
				return $k;
		}
	}
	
	return false;
}


/**
 * get_common_checkbox
 *
 * @param mixed $key This is a description
 * @param mixed $mask This is a description
 * @param mixed $disabe This is a description
 * @return mixed This is the return value description
 *
 */
function get_common_checkbox($key, $mask, $disabe='')
{
	$res = "";
	$lang = get_i18n();
	if (substr($key, 4) != 'sel_')
		$key = 'sel_'.$key;
	
	$udb = $lang[$key];
	if (!$udb)
		return false;
	
	foreach($udb as $k=>$v) {
		$flag = ($mask & (1 << $k));
		if($flag) {
			$res .= " <input type='checkbox' name='mdb_{$key}[]' value='$k' checked $disabe > $v ";
		}else{
			$res .= " <input type='checkbox' name='mdb_{$key}[]' value='$k' $disabe> $v ";	
		}
	}
	
	return $res;	
}


/**
 * get_common_checkbox_name
 *
 * @param mixed $key This is a description
 * @param mixed $mask This is a description
 * @return mixed This is the return value description
 *
 */
function get_common_checkbox_name($key, $mask)
{
	$res = "";
	$lang = get_i18n();
	if (substr($key, 4) != 'sel_')
		$key = 'sel_'.$key;
	
	$udb = $lang[$key];
	if (!$udb)
		return false;
	
	foreach($udb as $k=>$v) {
		$flag = ($mask & (1 << $k));
		if ($flag) {
			$res .= "$v, ";
		} else {
			$res .= "__, ";
		}
	}
	$res = substr($res, 0, -2);
	
	return $res;	
}


/**
 * get_common_checkbox_value
 *
 * @param mixed $key This is a description
 * @return mixed This is the return value description
 *
 */
function get_common_checkbox_value($key)
{
	$newkey = 'mdb_sel_'.$key;
	$mdb = get_var($newkey);
	if (!$mdb)
		return 0;
	
	$mb = 0x0;				
	foreach($mdb as $k=>$v) {
		$mb |= 0x1<<$v;
	}		
	return $mb;
}


/**
 * get_common_mask
 *
 * @param mixed $mdb This is a description
 * @return mixed This is the return value description
 *
 */
function get_common_mask($mdb)
{
	$mb = 0x0;				
	foreach($mdb as $key=>$v) {
		$mb |= 0x1<<$v;
	}
	
	return $mb;
}



/**
 * get_birth_year_select
 *
 * @param mixed $year This is a description
 * @return mixed This is the return value description
 *
 */
function get_birth_year_select($year=1996)
{
	$start = 1960;
	
	$str = "";
	
	$curr = date('Y',time(0));
	for($start=1960; $start<$curr; $start++)
	{
		$ifselect = $year==$start ? 'selected' : '';
		$str .= "<option value='$start' $ifselect >$start</option>";
	}	
	
	return $str;
}


/**
 * get_birth_month_select
 *
 * @param mixed $month This is a description
 * @return mixed This is the return value description
 *
 */
function get_birth_month_select($month=1)
{
	$str = "";
	
	for($start=1; $start<=12; $start++)
	{
		$ifselect = $month==$start ? 'selected' : '';
		$str .= "<option value='$start' $ifselect >$start</option>";
	}	
	
	return $str;
}


/**
 * is_var_mask
 *
 * @param mixed $bit This is a description
 * @param mixed $status This is a description
 * @return mixed This is the return value description
 *
 */
function is_var_mask($bit, $status)
{
	$mask = 0x1 << ($bit -1);
	
	if (($status & $mask ) === $mask)
	{
		return true;
	}
	
	return false;
}


/**
 * is_search
 *
 * @param mixed $status This is a description
 * @return mixed This is the return value description
 *
 */
function is_search($status)
{
	if ($status & 0x10) 
		return true;		
	return false;
}

// input: 0,1,3

/**
 * get_csb_mask
 *
 * @param mixed $csb This is a description
 * @return mixed This is the return value description
 *
 */
function get_csb_mask($csb)
{
	if (!is_array($csb))
	{
		$csb = explode(",", $csb);
	}
	
	$mask = 0x0;
	
	foreach($csb as $key=>$v)
	{
		$mask |= (0x1 << $v);
	}
	
	return $mask;
}


/**
 * get_csb_name
 *
 * @param mixed $sb This is a description
 * @return mixed This is the return value description
 *
 */
function get_csb_name($sb)
{
	$cf = get_config();
	require RPATH_I18N.DS.$cf['i18n'].DS."csb.php";
	return $csb[$sb];
}



/**
 * get_attach_select
 *
 * @param mixed $at This is a description
 * @return mixed This is the return value description
 *
 */
function get_attach_select($at)
{
	$attach_select = "";
	
	$cf = get_config();
	require RPATH_I18N.DS.$cf['i18n'].DS."filetype.php";
	
	foreach ($filetype as $key=>$t){
		$ifselect = $key==$at ? 'selected' : '';
		$attach_select .= "<option value='$key' $ifselect > $t</option>";
	}
	
	return $attach_select;
}



/**
 * get_task_select
 *
 * @param mixed $j This is a description
 * @return mixed This is the return value description
 *
 */
function get_task_select($j)
{
	$task_select = "";
	
	$cf = get_config();
	require RPATH_I18N.DS.$cf['i18n'].DS."task.php";
	
	foreach ($task as $key=>$a){
		$ifselect = $j==$key ? 'selected' : '';
		$task_select .= "<option value='$key' $ifselect > $a</option>";
	}
	
	return $task_select;
}

function get_template_select($style, $tpl, $type=0)
{
	$tpl_select = "";
	
	$file = RPATH_CACHE.DS."templates".DS."template_$style.php";
	if (file_exists($file))
	{
		require $file;			
		foreach ($tpls as $key=>$v)
		{
			if($type != 0 && $type != $v['type']) continue;
			$ifselect = $tpl==$v['template'] ? 'selected' : '';
			$tpl_select .= "<option value='".$v['template']."' $ifselect > ".$v['title']."</option>";
		}
		
		return $tpl_select;
	}
	return null;
}


function get_tpls($key=null)
{
	$file = RPATH_CONFIG.DS.'templates.php';
	if (file_exists($file)) {
		require $file;
		if ($templates) {
			if ($key) {
				return $templates[$key];
			} else {
				return $templates;
			}
		}
	}
	return array(); 
}

function get_tpl_select($tpl)
{
	$res = "";
	$templates = get_tpls();
	foreach ($templates as $key=>$v) {
		$selected = $key == $tpl ? 'selected': '';
		$res .= "<option value='$key' $selected>$v[title]</option>";
	}		
	return $res;
}

function get_template_name($tid)
{
	$t = "";
	
	require RPATH_CACHE_SITE.DS."template.php";
	
	$t = $tpls[$tid];
	return $t['tpl_path'];
}

function get_template_text($style, $tpl)
{
	$file = RPATH_CACHE_SITE.DS."templates".DS."template_$style.php";
	rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "style:$style, $tpl, $file");
	if (file_exists($file))
	{
		require $file;
		foreach ($tpls as $key=>$v)
		{
			if ($tpl === $v['template'])
			{
				return $v['title'];					
			}				
		}
	}
	return "默认";
}



function get_template_resize($style, $tpl)
{
	$t = "";
	
	$file = RPATH_CACHE_SITE.DS."templates".DS."template_$style.php";
	if (file_exists($file))
	{
		require $file;			
		foreach ($tpls as $key=>$v)
		{
			if ($tpl == $v['template'])
			{
				return $v['preview'];
			}
		}
	}
	
	return null;
}
///////////////////////////////////////////////VAR///////////////////////////////////////////////////////////////////////////////
function get_var_select($key, $default=null)
{
	$m = Factory::GetModel('var');
	return $m->get_var_select($key, $default);
}	

function is_var_title($key, $default)
{
	$m = Factory::GetModel('var');
	return $m->is_var_title($key, $default);
}	

function get_var_checkbox($vid, $status)
{
	$m = Factory::GetModel('var');
	return $m->get_var_checkbox($vid, $status);
}

function get_var_checkbox_name($vid, $mask)
{
	$m = Factory::GetModel('var');
	return $m->get_var_checkbox_name($vid, $mask);
}

//返回查询用掩码 
function get_var_mask($var_array)
{
	$m = Factory::GetModel('var');
	return $m->get_var_mask($var_array);
}

function get_var_name($key, $var)
{
	$m = Factory::GetModel('var');
	return $m->get_var_name($key, $var);
}

function get_var_table($key)
{
	$m = Factory::GetModel('var');
	return $m->get_var_table($key);
}

///////////////////////////////////////////////cache///////////////////////////////////////////////////////////////////////////////
//提取缓存组织select option形式返回
function get_cache_select($cache_name, $default)
{
	$res = "";
	
	$cache_file = RPATH_CACHE.DS."$cache_name.php";	
	if (!file_exists($cache_file))
		return false;
	
	require ($cache_file);		
	foreach (${$cache_name.'db'} as $key=>$v) {			
		$ifselect = $default==$key ? 'selected' : '';
		$res .= "<option value='$key' $ifselect > ".$v['title']."</option>";
	}		
	return $res;
}

function get_cache_name($cache_name, $default)
{
	$res = "";
	
	$cache_file = RPATH_CACHE_TABLE.DS."$cache_name.php";	
	if (!file_exists($cache_file))
		return false;
	
	require ($cache_file);		
	foreach (${$cache_name.'db'} as $key=>$v) {
		if ($key == $default) 
			return $v['title'];
	}
	
	return false;
}

function get_cache_value($cache_name, $pid, $key='title')
{
	$res = "";
	
	$cache_file = RPATH_CACHE.DS."$cache_name.php";	
	if (!file_exists($cache_file))
		return false;
	
	require ($cache_file);		
	foreach (${$cache_name.'db'} as $k=>$v) {
		if ($k == $pid) 
			return $v[$key];
	}
	
	return false;
}

function get_cache_checkbox($cache_name, $defaults)
{
	$res = "";
	
	$cache_file = RPATH_CACHE.DS."$cache_name.php";	
	if (!file_exists($cache_file))
		return false;
	
	if (!is_array($defaults))
		$defaults = explode(',', $defaults);
	
	require ($cache_file);		
	foreach (${$cache_name.'db'} as $key=>$v) {
		$checked = "";
		if (in_array($key, $defaults))
			$checked = "checked";
		$res .= "<span> <input type='checkbox' name='{$cache_name}[]' value='$key' $checked /> ".$v['title']."</span>";
	}		
	return $res;
}



function cache_table($table, $suffix='order by title', $filename='')
{
	$cache = Factory::GetCache();
	return $cache->cache_table($table, $suffix, $filename);
}

function get_cache_table($table)
{
	$cache = Factory::GetCache();
	return $cache->get_cache_table($table);
}

function get_cache_title($table, $key)
{
	$cache = Factory::GetCache();
	$db = $cache->get_cache_table($table);
	$v = $db[$key];
	return $v['title'];
}


function cache_var_value($vid)
{
	$cache = Factory::GetCache();
	return $cache->cache_var_value($vid);
}
function cache_var()
{
	$cache = Factory::GetCache();
	return $cache->cache_var();
}

function cache_array($name, $arr=null, $cachefile=null)
{
	$cache = Factory::GetCache();
	return $cache->cache_array($name, $arr, $cachefile);
}

function get_cache_array($name, $cachefile=null)
{
	return cache_array($name, null, $cachefile);
}


function probe_apps()
{
	//编历目录
	$udb = s_readdir(RPATH_APPS);
	$hdb = array('.svn');
	
	$adb = array();
	foreach ($udb as $key=>$v) {
		if (in_array($v, $hdb))
			continue;
		
		$appcfgfile = RPATH_APPS.DS.$v.DS.'config.php';
		if (!file_exists($appcfgfile)) 
			continue;
		
		require_once($appcfgfile);
		if (!isset($appcfg))		
			continue;
		
		$appcfg['dirname'] = $v;
		$adb[$v] = $appcfg;
	}
	
	return $adb;
}

/**
 * 缓存插件配置
 *
 * @param mixed $plugins This is a description
 * @param mixed $basedir This is a description
 * @return mixed This is the return value description
 *
 */
function cache_plugins($plugins, $basedir=null)
{
	$plgcfg = array();
	foreach ($plugins as $key=>$v) {
		$plg = Factory::GetPlugin($key);
		if (!$plg)
			continue;
		$plgcfg[$key] = $plg->getConfig();
	}
	$file = RPATH_CONFIG.DS."plugins.php";
	s_unlink($file);
	cache_array('plugins', $plgcfg, $file);
}

function cache_apps($apps, $basedir=null)
{
	$appcfg = array();
	foreach ($apps as $key=>$v) {
		$app = Factory::GetApp($key);
		if (!$app)
			continue;
		$appcfg[$key] = $app->getAppCfg();
	}
	$file = RPATH_CONFIG.DS."apps.php";
	s_unlink($file);
	
	array_sort_by_field($appcfg, 'id');
	cache_array('apps', $appcfg, $file);
}





function get_tpl_list($mid)
{
	$tpl_list = "";
	$path = RPATH_CACHE_SITE.DS."template.php";
	require $path;
	
	foreach($tpls as $key=>$v){
		if( $v[mid] != $mid) continue;
		$tpl_list .= "<ul><li><img src='templates/site/".$v[tpl_path]."/preview.png' /></li>";
		$tpl_list .= "<li><input type='radio' name='template' value='$key'>".$v[title]."</li>";
		$tpl_list .= "</ul>";
	}
	
	return $tpl_list;
	
}


function get_style_preview($s)
{
	$tpl_list = "";
	$path = RPATH_CACHE_SITE.DS."style.php";
	require $path;
	
	foreach($style as $key=>$v){
		if( $v[style] != $s) continue;
		$tpl_list = "<img src='attachments/".$v[preview]."' />";
	}
	
	return $tpl_list;
}


//查看风络
function get_style_select($s, $share=255)
{
	$tid_select = "";
	
	$path = RPATH_CACHE_SITE.DS."style.php";
	require $path;
	
	foreach($style as $key=>$v)
	{
		if($share !== 255 && $v['share'] != $share && $s != $v['style'])
		{
			continue;
		}
		
		$ifselect = $s == $v['sid'] ? 'selected' : '';
		$tid_select .= "<option value='$v[sid]' $ifselect >".$v['title']."</option>";
	}
	
	return $tid_select;
}

function get_style_select2($s, $share=255)
{
	$tid_select = "";
	
	$path = RPATH_CACHE.DS."style.php";
	if (!file_exists($path)){
		return false;
	}
	
	require $path;		
	foreach($style as $key=>$v) {
		if($share !== 255 && $v['share'] != $share && $s != $v['style']) {
			continue;
		}
		
		$ifselect = $s == $v['style'] ? 'selected' : '';
		$tid_select .= "<option value='$v[style]' $ifselect >".$v['title']."</option>";
	}
	
	return $tid_select;
}

function get_style_name($sid)
{
	$file = RPATH_CACHE_SITE.DS."style.php";
	if ( file_exists($file))
	{
		require $file;
		foreach($style as $key=>$v)
		{
			if ($v['sid'] == $sid)
			{
				return $key;
			}
		}			
	}
	return null;
}


function get_style_radio($s, $share=255)
{
	$style_radio = "";
	
	$path = RPATH_CACHE_SITE.DS."style.php";
	require $path;
	$style_radio = "";
	foreach($style as $key=>$v)
	{
		if($share !== 255 && $v['share'] != $share && $s != $v['style'])
		{
			continue;
		}
		
		$style_radio .= "<ul>";
		$style_radio .= "<li><img src='attachments/$v[preview]' /></li>";
		$ifselect = $s == $v['style'] ? 'checked' : '';
		$style_radio .= "<li><input type='radio' name='style' value='$v[style]' $ifselect >".$v['title']."</li>";
		$style_radio .= "</ul>";
	}
	
	return $style_radio;
}

//////////////////////////////////////////////// 数值格式化 /////////////////////////////////////////////////////////////
function nformat($num, $fmt=1, $decimals=0)
{
	switch($fmt)
	{
		case 1 : 
			return number_format($num,$decimals);
		case 2:	
			
			if ($num > 1024.0*1024*1024)
			{
				return sprintf("%.2f", $num/1024.0/1024.0/1024.0)." GB";				
			}
			
			if ($num > 1024.0*1024)
			{
				return sprintf("%.2f", $num/1024.0/1024.0)." MB";
			}
			
			if ($num > 1024)
			{
				return sprintf("%.2f", $num/1024.0)." KB";
			}
			
			
			return $num;
		
		default:
			break;
	}
}

function nformat_size($num)
{
	$v = floatval($num);
	return nformat($v, 2, 2);
}



/**
 * nformat_human_file_size 格式化字节，如：输入：1024，输出: 1 KB
 *
 * @param mixed $bytes 字节
 * @return mixed 格式化字节字串，如：输入：1024，输出: 1 KB
 *
 */
function nformat_human_file_size($bytes, $nr=1) 
{
	if (!$bytes)
		return '0';
	
	if ($bytes < 0) {
		return "?";
	}
	if ($bytes < 1024) {
		return "$bytes B";
	}
	$bytes = round($bytes / 1024, $nr);
	if ($bytes < 1024) {
		return "$bytes KB";
	}
	$bytes = round($bytes / 1024, $nr);
	if ($bytes < 1024) {
		return "$bytes MB";
	}
	$bytes = round($bytes / 1024, $nr);
	if ($bytes < 1024) {
		return "$bytes GB";
	}
	$bytes = round($bytes / 1024, $nr);
	if ($bytes < 1024) {
		return "$bytes TB";
	}
	
	$bytes = round($bytes / 1024, $nr);
	return "$bytes PB";
}


/**
 * nformat_get_human_file_size 可读性带单位的字节字串转换字节数值，如：1KB=1024
 *
 * 注：从非数字字串将被截断
 * 
 * @param mixed $val 单位的字节字串，当输入是 double或int型直接返回, eg: 1KB
 * @return mixed 字节数值, eg: 1024
 *
 */
function nformat_get_human_file_size($val) 
{
	if (is_float($val))
		return $val;
	if (is_int($val))
		return $val;
	
	$len = strlen($val);
	for($i=0; $i<$len; $i++) {
		if ($val[$i] == '.')// 51.4G
			continue;
		if ($val[$i] < '0' || $val[$i] > '9')//非数字
			break;			
	}
	
	$sz = floatval(substr($val, 0, $i));
	$scale = substr($val, $i);
	$scale = strtoupper(trim($scale));
	switch($scale) {
		case 'P':
		case 'PB':
			$sz *= 1024;
		case 'T':
		case 'TB':
			$sz *= 1024;
		case 'G':
		case 'GB':
			$sz *= 1024;
		case 'M':
		case 'MB':
			$sz *= 1024;
		case 'K':
		case 'KB':
			$sz *= 1024;
		case 'B':
			break;
	}
	return floor($sz);
}



define('BYTES_PER_GBPS', 125000000);
define('BYTES_PER_MBPS', 125000);
define('BYTES_PER_KBPS', 125);

function nformat_bps($bytes) 
{
	if ($bytes > BYTES_PER_MBPS) {
		$res = round($bytes/BYTES_PER_MBPS,2)."Mbps";
	}
	else if ($bytes > BYTES_PER_KBPS) {
			$res = round($bytes/BYTES_PER_KBPS, 2)."Kbps";
		}
		else {
			$res =  round($bytes, 2)."bps";
		}
	
	return $res;
}

function nformat_speed($bytes) 
{
	if ($bytes >= BYTES_PER_GBPS) {
		$res = round($bytes/BYTES_PER_GBPS,0)."G";
	} else if ($bytes >= BYTES_PER_MBPS) {
		$res = round($bytes/BYTES_PER_MBPS,0)."M";
	} else if ($bytes >= BYTES_PER_KBPS) {
		$res = round($bytes/BYTES_PER_KBPS, 2)."K";
	} else {
		$res =  round($bytes, 2)."b";
	}	
	return $res;
}


///////////////////////////////////////////////DATETIME 时间///////////////////////////////////////////////////////////////////////////////

define ("RC_TIMESEC_MIN", 60);
define ("RC_TIMESEC_HOUR", 3600);
define ("RC_TIMESEC_DAY", 86400);
define ("RC_TIMESEC_WEEK", 604800);
define ("RC_TIMESEC_MONTH", 2592000);
define ("RC_TIMESEC_YEAR", 31104000);

function tformat($ts=0, $format = 'Y-m-d H:i:s', $offset=8)
{
	!$ts && $ts = time();
	!$format && $format = 'Y-m-d H:i:s';
	return gmdate($format, $ts+ 3600 * $offset);	
}

function tformat_vtime($ts=0, $format = 'Y-m-d H:i:s', $offset=8)
{
	$dt = tformat($ts, 'Y:m:d:H:i:s:N');
	$vt = explode(':',$dt);
	$vt['year'] = $vt[0];
	$vt['month'] = $vt[1];
	$vt['day'] = $vt[2];
	$vt['hour'] = $vt[3];
	$vt['minute'] = $vt[4];
	$vt['second'] = $vt[5];
	$vt['week'] = $vt[6]; //6,7
	
	return $vt;
}


/**
 * tformat_month2days 一个月有多少天
 *
 * @param mixed $ts 时间截（缺省为当前月）
 * @return mixed This is the return value description
 *
 */
function tformat_month2days($ts=0, &$weekdays=array())
{
	$vt = tformat_vtime($ts);
	
	$year = $vt['year'];
	$month = $vt['month'];
	
	$days = 0;	
	//php7.4 : Invalid numeric literal in /opt/crab/var/www/lib/common.php on line 2059	
	if (in_array($month, array(1, 3, 5, 7, 8, 1, 3, 5, 7, 8, 10, 12))) {
		$days = 31;
	} else if ( $month == 2 ) {
			if ($year%400 == 0 || ($year%4 == 0 && $year%100 !== 0)) { //判断是否是闰年		
				$days = 29;
			} else {
				$days = 28;
			}
		} else {
			$days = 30;
		}
	
	$d = $days;
	
	//weekdays
	$weekdays = array(1=>0,0,0,0,0,0,0);
	for ($d; $d >0 ; $d--) { 
		$date = $year.'-'.$month.'-'.$d;
		$t = tformat_ts($date);
		//N - 星期几的 ISO-8601 数字格式表示（1表示Monday[星期一]，7表示Sunday[星期日]）
		$n = tformat($t, 'N');
		
		switch ($n) {
			case '1':
				$weekdays[1] += 1; 
				break;
			case '2':
				$weekdays[2] += 1; 
				break;
			case '3':
				$weekdays[3] += 1; 
				break;
			case '4':
				$weekdays[4] += 1; 
				break;
			case '5':
				$weekdays[5] += 1; 
				break;
			case '6':
				$weekdays[6] += 1; 
				break;
			case '7':
				$weekdays[7] += 1; 
				break;
		}
	}
	
	return $days;
}

function tformat_month2season($month)
{
	switch($month) {
		case 1:
		case 2:
		case 3:
			return 1;
		case 4:
		case 5:
		case 6:
			return 2;
		case 7:
		case 8:
		case 9:
			return 3;
		case 10:
		case 11:
		case 12:
		default:
			return 4;
	}
	
}

/**
 * 时间截离现大多远
 *
 * @param mixed $ts This is a description
 * @return mixed This is the return value description
 *
 */
function tformat_timelong($ts)
{
	$now = time();
	$sec = $now - $ts;
	
	$timeunit = get_i18n('timeago');
	//just now, 1min, 2mins,...1hour, 2hour, 24hours
	if ($sec < RC_TIMESEC_MIN) {
		return $timeunit['just now'];
	} else if ($sec <RC_TIMESEC_HOUR) {
		$min = floor($sec/RC_TIMESEC_MIN);
		return $min.($min == 1?$timeunit['min']:$timeunit['min']);
	} else if ($sec <RC_TIMESEC_DAY) {
		$hour = floor($sec/RC_TIMESEC_HOUR);
		return $hour.($hour == 1?$timeunit['hour']:$timeunit['hours']);
	} else if ($sec <RC_TIMESEC_WEEK) {
		$day = floor($sec/RC_TIMESEC_DAY);
		return $day.($day == 1?$timeunit['day']:$timeunit['days']);
	} else if ($sec <RC_TIMESEC_MONTH) {
		$week = floor($sec/RC_TIMESEC_WEEK);
		return $week.($week == 1?$timeunit['week']:$timeunit['weeks']);
	} else if ($sec <RC_TIMESEC_YEAR) {
			$month = floor($sec/RC_TIMESEC_MONTH);
			return $month.($month == 1?$timeunit['mon']:$timeunit['mons']);
		}  else {
			$year = floor($sec/RC_TIMESEC_YEAR);
			return $year.($year == 1?$timeunit['year']:$timeunit['years']);
		}
}


function tformat_expired($ts)
{
	$now = time();
	$sec = $ts - $now;
	
	$timeunit = get_i18n('timelater');
	//just now, 1min, 2mins,...1hour, 2hour, 24hours
	if ($sec < 0) {
		return $timeunit['expried'];
	} else if ($sec < RC_TIMESEC_MIN) {
		return $timeunit['just'];
	} else if ($sec <RC_TIMESEC_HOUR) {
		$min = floor($sec/RC_TIMESEC_MIN);
		return $min.($min == 1?$timeunit['min']:$timeunit['min']);
	} else if ($sec <RC_TIMESEC_DAY) {
		$hour = floor($sec/RC_TIMESEC_HOUR);
		return $hour.($hour == 1?$timeunit['hour']:$timeunit['hours']);
	} else if ($sec <RC_TIMESEC_WEEK) {
		$day = floor($sec/RC_TIMESEC_DAY);
		return $day.($day == 1?$timeunit['day']:$timeunit['days']);
	} else if ($sec <RC_TIMESEC_MONTH) {
		$week = floor($sec/RC_TIMESEC_WEEK);
		return $week.($week == 1?$timeunit['week']:$timeunit['weeks']);
	} else if ($sec <RC_TIMESEC_YEAR) {
			$month = floor($sec/RC_TIMESEC_MONTH);
			return $month.($month == 1?$timeunit['mon']:$timeunit['mons']);
		}  else {
			$year = floor($sec/RC_TIMESEC_YEAR);
			return $year.($year == 1?$timeunit['year']:$timeunit['years']);
		}
}


function tformat_cstdate($t)
{
	return tformat($t, 'Y年n月j日');
}

function tformat_N($t)
{
	//N - 星期几的 ISO-8601 数字格式表示（1表示Monday[星期一]，7表示Sunday[星期日]）
	$n = tformat($t, 'N');
	switch($n) {
		case 1:
			return '星期一';
		case 2:
			return '星期二';
		case 3:
			return '星期三';
		case 4:
			return '星期四';
		case 5:
			return '星期五';
		case 6:
			return '星期六';
		case 7:
			return '星期日';
	}
	return "";	
	
}
function tformat_ym($t)
{
	return tformat($t, 'Y-m');
}

function tformat_cstdatetime($t)
{
	return tformat($t, 'Y年n月j日 H:i:s');
}

function tformat_date($t)
{
	return tformat($t, 'Y-m-d');
}

function tformat_current($format = 'Y-m-d H:i:s', $offset=8)
{
	return gmdate($format, time() + 3600 * $offset);
}

function tformat_today()	
{
	return s_mktime(tformat_date(time()));
}


function tformat_ts2datets($ts)	
{
	return tformat_ts(tformat_date($ts));
}

function current_microtime()	
{
	list($usec, $sec) = explode(" ", microtime());		
	return ((float)$usec + (float)$sec);
}

function s_mktime($datetime)
{
	if (!$datetime)
		return false;
	
	$d = explode(" ",$datetime);
	if (count($d) < 2) 
	{
		if (strpos($datetime, '-') === false)
			return $datetime;
		
		$datetime .= " 00:00:00";
		$d = explode(" ",$datetime);
		if (count($d) < 2) 
		{
			return false;
		}
	}
	
	$date = $d[0];
	$time = $d[1];
	
	$date_x = explode("-",$date);
	
	//rlog($date_x); 
	$year = $date_x[0];
	$month = $date_x[1];
	$day = $date_x[2];
	if (!$day)
		$day = 1;
	
	if (strstr($time, ':'))
	{
		$time_x = explode(":",$time);
		$hour = $time_x[0];
		$minute = $time_x[1];
		$second = $time_x[2];
	}
	else
	{
		$hour = 0;
		$minute = 0;
		$second = 0;
	}
	
	$cf = get_config();
	$hour = $hour-$cf['timediff'];
	
	// create UNIX TIMESTAMP of the GMT above
	$ts = mktime($hour, $minute, $second, $month, $day, $year);
	//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, 'datetime='.$datetime.',ts='.$ts);
	
	return $ts;
}

/*

["date"]=> int(20200526) 
["time"]=> int(154523)

20210326, 95502

*/
function __mktime($date, $time)
{
	$year = substr($date, 0, 4);
	$month = substr($date, 4, 2);
	$day = substr($date, 6, 2);
	
	if (strlen($time) == 5 ) { //eg:95502
		$hour = substr($time, 0, 1);
		$minute = substr($time, 1, 2);
		$second = substr($time, 3, 2);
	}  else {// eg: 154523
		$hour = substr($time, 0, 2);
		$minute = substr($time, 2, 2);
		$second = substr($time, 4, 2);
	}
	
	// rlog("$date, $time, $hour, $minute, $second, $month, $day, $year");
	// create UNIX TIMESTAMP of the GMT above
	$ts = tformat_mktime("$year-$month-$day $hour:$minute:$second");
	
	return $ts;
}


//把格式如 2012-01-01 11:20:00转换成时间截
function my_mktime($gmt, $dtsplitchr=' ')
{
	if (!$gmt)
		return false;
	
	$d = explode($dtsplitchr, $gmt);
	if(count($d) < 2) 
	{
		$gmt .= " 00:00:00";
		$d = explode(" ",$gmt);
		if(count($d) < 2) 
		{
			return false;
		}
	}
	
	$date = $d[0];
	$time = $d[1];
	
	$date_x = explode("-",$date);
	$year = $date_x[0];
	$month = $date_x[1];
	$day = $date_x[2];
	
	if(strstr($time, ':'))
	{
		$time_x = explode(":", $time);
		$hour = $time_x[0];
		$minute = $time_x[1];
		$second = $time_x[2];
	}
	else
	{
		$hour = 0;
		$minute = 0;
		$second = 0;
	}
	
	$cf = get_config();
	$hour = $hour-$cf['timediff'];
	
	// create UNIX TIMESTAMP of the GMT above
	$ts = mktime($hour, $minute, $second, $month, $day, $year);
	
	return $ts;
}

function tformat_unixtimestamp($datetime)
{
	return s_mktime($datetime);
}

function tformat_ts($datetime)
{
	return s_mktime($datetime);
}
function tformat_mktime($datetime)
{
	return s_mktime($datetime);
}


///////////////////////////////////////////////LOG 日志///////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////

///////////////////////////////////////////////STR 字串///////////////////////////////////////////////////////////////////////////////

// s--每一个字符长度，默认为一个字节．s=0 截取字节 s=1截取个数
function my_substr($content, $length, $s=0)
{
	
	if($length && strlen($content)>$length){
		$retstr='';
		for($i = 0; $i < $length - 2; $i++) {
			if(ord($content[$i]) > 127){
				if($s){
					$retstr .=$content[$i].$content[$i+1];
					$i++;
					$length++;
				}else{
					if(($i+1<$length - 2)){
						$retstr .=$content[$i].$content[$i+1];
						$i++;
					}
				}
			}else{
				$retstr .=$content[$i];
			}
		}
		return $retstr;
	}
	return $content;
}


function attr2array2($str) 
{
	$attr = array();
	$str = trim($str);
	if (!$str)
		return false;
	
	$p1 = strpos($str, '=');
	while ($p1 !== false) {
		$name = trim(substr($str, 0, $p1));
		$val = trim(substr($str, $p1+1));
		$ch = $val[0];
		
		if ($ch == '\'') {//定界符 '
			$val = substr($val, 1);
			$p2 = strpos($val, '\'');
		} else if ($ch == '"') {
				$val = substr($val, 1);
				$p2 = strpos($val, '"');
			} else {
				$p2 = strpos($val,' '); //空格
			}
		if ($p2 !== false) {
			$str = substr($val, $p2+1);
			$p1 = strpos($str, '=');
			$val = substr($val, 0, $p2);
		} else {
			$p1 = false;
		}
		
		$attr[$name] = $val;
	}
	
	
	
	return $attr;
}

function attr2array($str) 
{
	//Initialize variables
	$attr		= array();
	$retarray	= array();
	// Lets grab all the key/value pairs using a regular expression
	preg_match_all( '/([\w:-]+)[\s]?=[\s]?"([^"]*)"/i', $str, $attr );		
	if (is_array($attr))
	{
		$numPairs = count($attr[1]);
		for($i = 0; $i < $numPairs; $i++ )
		{
			$retarray[$attr[1][$i]] = $attr[2][$i];
		}
	}
	
	preg_match_all( "/([\w:-]+)[\s]?=[\s]?'([^']*)'/i", $str, $attr );		
	if (is_array($attr))
	{
		$numPairs = count($attr[1]);
		for($i = 0; $i < $numPairs; $i++ )
		{
			$retarray[$attr[1][$i]] = $attr[2][$i];
		}
	}
	
	return $retarray;
}

function array2attr($udb) 
{
	if (!$udb)
		return "";
	
	$str = "";
	foreach ($udb as $key=>$v) {
		$str .= " $key=\"$v\"";
	}
	
	return $str;
}


function attr2mid($attribs)
{
	//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $attribs);
	
	$name = $attribs['name'];
	$tag = isset($attribs['tag']) ? $attribs['tag'] : '';
	//$tplfile = isset($attribs['tplfile']) ? $attribs['tplfile'] : '';
	$mid = md5($name.$tag.'_rcmodule');
	
	return $mid;
}

function parseTplLinkContent($content, &$attrs)
{
	$content = trim($content);
	
	//探测 img
	$matches = array();
	$res = preg_match_all("/<img[^>]*src\b\s*=\s*[\s]*[\'\"]?([^\'\"]*)[\'\"]?/i", $content, $matches);
	if ($res && count($matches[1]) == 1) {
		$attrs['src'] = $matches[1][0];
		$attrs['ctype'] = 4;//img
		return true;
	}  
	
	//探测 video
	$res = preg_match_all("/<video[^>]*src\b\s*=\s*[\s]*[\'\"]?([^\'\"]*)[\'\"]?/i", $content, $matches);
	if ($res && count($matches[1]) == 1) {
		$attrs['src'] = $matches[1][0];
		$attrs['ctype'] = 5;//video
		return true;
	}
	
	$attrs['ctype'] = 3;
	$attrs['src'] = '';
	$attrs['title'] = $content;
	
	return true;
}

function parseTplLinkData($data)
{
	//t
	$replace = array();
	$matches = array();	
	$_content = stripslashes($data);
	$res = preg_match_all("/<a\b\s*href\b\s*=\s*[\s]*[\'\"]?([^\'\"]*[^\'\"]+)[\'\"]?.+ttag=\s*[\s]*[\'\"]?([^\'\"]*[^\'\"]+)[\'\"]?.+>(.+)<\/a>/i", $_content, $matches);
	if (!$res) {
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "no match tag!");
		return false;
	}
	//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $matches);	
	
	$mdb = array();
	
	$nr = count($matches[1]);
	for ($i=0; $i<$nr; $i++) {
		$content = trim($matches[0][$i]);
		$url = trim($matches[1][$i]);
		$tag = trim($matches[2][$i]);
		$contentData = $matches[3][$i];
		
		$res = parseTplLinkContent($contentData, $attrs);
		
		$name = 'a';
		$ctype = $attrs['ctype'];
		$src = trim($attrs['src']);
		$title = empty($attrs['title'])?$tag:$attrs['title'];
		
		$params = array();
		//$params['tplfile'] = $tplfile;
		$params['name'] = $name;
		$params['tag'] = $tag;
		$params['type'] = $name;
		
		$params['url'] = $url;				
		$params['title'] = $title;				
		$params['content'] = $content;
		$params['src'] = $src;
		$params['ctype'] = $ctype;
		
		$mid = attr2mid($params);
		$params['mid'] = $mid;
		
		$mdb[] = $params;
	}
	
	$res = array();
	$res['matches'] = $matches;
	$res['mdb'] = $mdb;
	
	return $res;
}

function parseTplLink($tplfile)
{
	$matches = array();
	
	$data = s_read($tplfile);
	if (!$data) {
		rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "read file '$tplfile' failed!");
		return false;
	}
	$res = parseTplLinkData($data);
	
	if (!$res) {
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "no match or parse tpl file '$tplfile' failed!");
		return false;
	}
	
	return $res;
}

function matchModule($data)
{
	$matches = array();	
	$_content = stripslashes($data);
	
	//type='a' or type="a"
	//if (!($res = preg_match_all('#<rdoc:include\b\s*type="([^"]+)" (.*)((\/>)|(\s*>(.*)</rdoc:include>))#isU', 
	if (!($res = preg_match_all('#<rdoc:include\b\s*type=(\'|")([^(\1)]+)(\1)\s+(.*)((\/>)|(\s*>(.*)</rdoc:include>))#isU', 
					$_content, $matches))) {
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "no modules match!");
		return false;
	}
	
	//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $matches);	
	
	$matches[0] = array_reverse($matches[0]); // all, eg: "<rdoc:include type='module' name='myprofile' />"
	//$matches[1] = array_reverse($matches[1]); // "'"
	$matches[2] = array_reverse($matches[2]); //"module"			
	//$matches[3] = array_reverse($matches[3]); // "'"		
	$matches[4] = array_reverse($matches[4]); //"name='myprofile' "		
	
	
	return $matches;
}

function parseTplModuleData($data)
{
	$matches = array();	
	$_content = stripslashes($data);
	
	//type='a' or type="a"
	//if (!($res = preg_match_all('#<rdoc:include\b\s*type="([^"]+)" (.*)((\/>)|(\s*>(.*)</rdoc:include>))#isU', 
	if (!($matches = matchModule($data))) {
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "no modules match!");
		return false;
	}
	
	//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $matches);	
	
	$matches[0] = array_reverse($matches[0]); // all, eg: "<rdoc:include type='module' name='myprofile' />"
	//$matches[1] = array_reverse($matches[1]); // "'"
	$matches[2] = array_reverse($matches[2]); //"module"			
	//$matches[3] = array_reverse($matches[3]); // "'"		
	$matches[4] = array_reverse($matches[4]); //"name='myprofile' "		
	
	$nr = count($matches[0]);
	
	$mdb = array();
	for($i=0; $i<$nr; $i++) {
		
		$content = $matches[0][$i];
		$type  = $matches[2][$i];
		$attribsstr = $matches[4][$i];
		$attribs = attr2array($attribsstr);
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $attribs);
		
		if ($type != 'module') {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "type is not module!");
			continue;			
		}
		if (!isset($attribs['ttag'])) {
			//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "no tpl tag '$content'!");
			continue;
		}
		
		$params = $attribs;
		
		$name = trim($attribs['name']);
		$tag = trim($attribs['ttag']);
		$title = isset($attribs['title'])?$attribs['title']:($tag?$tag:$name);		
		
		
		$params['name'] = $name;
		$params['tag'] = $tag;
		$params['type'] = $type;
		
		$params['content'] = $content;
		$params['title'] = $title;
		$params['ctype'] = 1; //content type
		$params['attribs'] = $attribsstr;
		
		$mid = attr2mid($params);
		$params['mid'] = $mid;
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $params);
		
		$mdb[] = $params;
	}	
	
	$res = array();
	$res['matches'] = $matches;
	$res['mdb'] = $mdb;
	
	return $res;
}

function parseTplModule($tplfile)
{
	$matches = array();
	
	$data = s_read($tplfile);
	if (!$data) {
		rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "read file '$tplfile' failed!");
		return false;
	}
	
	$res = parseTplModuleData($data);
	if (!$res) {
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "no match or parse tpl file '$tplfile' failed!");
		return false;
	}
	
	return $res;
}


function parseTplFile($tplfile)
{
	$res = array();
	
	$res1 = parseTplLink($tplfile);
	if ($res1) {
		$res['links'] = $res1;
	}
	
	$res2 = parseTplModule($tplfile);
	if ($res2) {
		$res['modules'] = $res2;
	}
	
	return $res;
}


function is_email($email)
{
	return check_email($email);
}

function is_mobile($mobile)
{
	$res =  preg_match("/^1[34578]\d{9}$/", $mobile);
	rlog($res);
	return $res;
}


//去BOM
function strim_bom($data)
{
	$cs1 = substr($data, 0, 1);
	$cs2 = substr($data, 1, 1);
	$cs3 = substr($data, 2, 1);
	
	if (ord($cs1) == 239 && ord($cs2) == 187 && ord($cs3) == 191) 
	{
		$data = substr($data, 3);
	}
	
	return $data;	
}

//开头与结尾的' " 定界符去掉
function trimfilename($fname) 
{
	$fname = ltrim($fname, " \t'\"");
	return rtrim($fname, " \t'\"");	
}


function utf8_substr($str, $start, $length)
{
	mb_internal_encoding("UTF-8");				
	return mb_substr($str, $start, $length);
}

//检查email是否有效
function check_email($email)
{
	$atom = '[-a-z0-9!\#\$%&\'*+/=?^_`{|}~]';    // allowed characters for part before "at" character
	$domain = '([a-z0-9]([-a-z0-9]*[a-z0-9]+)?)'; // allowed characters for part after "at" character
	$pattern = '#^' . $atom . '+' .         // One or more atom characters.
		'(\.' . $atom . '+)*'.               // Followed by zero or more dot separated sets of one or more atom characters.
		'@'.                                 // Followed by an "at" character.
		'(' . $domain . '{1,63}\.)+'.        // Followed by one or max 63 domain characters (dot separated).
		$domain . '{2,63}'.                  // Must be followed by one set consisting a period of two
		'$#i'; 
	// or max 63 domain characters.
	$res = preg_match($pattern, $email);		
	return $res;
	
}



/**
 * 强密码检测
 * 
 * 最小8位，最大64位
 * 密码应包括大写字母、小写字母、数字和特殊字符
 *
 * @param mixed $passwd This is a description
  * @return 成功true, 失败false
 */
function check_passwd($passwd)
{
	$cf = get_config();
	$safepwd = $cf['safepwd'];
	$passwd_min_length = $cf['min_passwd_length'];
	!$passwd_min_length && $passwd_min_length = 8;
	
	if ($safepwd)
	{
		if (strlen($passwd) < $passwd_min_length)
		{
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "passwd too short");
			return false;
		}
		
		//包含数字
		if (!preg_match('/\d+/', $passwd)) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no digital");
			return false;
		}
		
		//包含小写字母
		if (!preg_match('/[a-z]+/', $passwd)) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no small alpha");
			return false;
		}
		
		//包含小写字母
		if (!preg_match('/[A-Z]+/', $passwd)) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no big alpha");
			return false;
		}
		
		//特殊字符
		if (!preg_match('/[-`=\\\[\];\',\.\/~!@#$%^&\*\(\)_\+\|\{\}:"<>\?]+/', $passwd)) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no other alpha, passwd=$passwd!");
			return false;
		}
		
		/*
		if(item.getProperty("passwd")){
				if(sysPasswsStrong==1){
					if(item.value.match(/\d+/)&&item.value.match(/[a-z]+/)&&item.value.match(/[A-Z]+/)&&item.value.match(/[-`=\\\[\];',\.\/~!@#$%^&\*\(\)_\+\|\{\}:"<>\?]+/)){
						if(item.getNext(".pstatus")){
							item.getNext(".pstatus").set("text", "安全").setProperty("style", "padding:0 0 0 10px; color:green;");
						}
					}else{
						this.error(item, "密码应包括大写字母、小写字母、数字和特殊字符");
					}
				}
				if(item.value.length<sysMinPasswd || item.value.length>88){
					this.error(item, "密码长度在"+sysMinPasswd+"~88个字符");
				}
			}*/
	}
	
	
	return true;	
}


function check_username($username)
{
	$pattern = '#[a-z0-9\_]{4,16}$#i'; 
	$res = preg_match($pattern, $username);		
	return $res;
}

function is_mac($str)
{
	$nr = 0;
	$mdb = explode(':', $str);
	foreach ($mdb as $key => $value) {
		if (strlen($value) > 2)
			return false;
		if (!preg_match('/^[0-9A-F]*$/i', $value)) {
			return false;
		}
		$nr ++;
	}
	if ($nr > 6)
		return false;
	
	return true;
}

//是不是IP
function is_ip($in)
{
	$arr = explode('.', $in);
	if (count($arr) != 4)
		return false;
	
	foreach ($arr as $key=>$v) {
		if (!is_numeric($v))
			return false;
		$val = intval($v);
		if ($val < 0 || $val > 255 )
			return false;
		
	}
	return true;		
}


/**
 * 是否为有效IPV6地址
 *
 * @param mixed $in 输入的地址字符串
 * @return mixed 有效返回true, 否则false
 *
 */
function is_ip6($in)
{
	$total_dim_nr = 0;
	$total_hex_nr = 0;
	$dim_nr = 0;
	$hex_nr = 0;
	
	$len = strlen($in);
	
	for($i=0; $i<$len; $i++) {		
		$c = $in[$i];
		$val = ord($c);		
		if ($val === 58) {
			$dim_nr ++;
			if ($dim_nr > 2)
				return false;
			if ($dim_nr === 2) {
				$total_dim_nr ++ ;
				if ($total_dim_nr > 1)
					return false;
			} else {
				$total_hex_nr ++;	
				if ($total_hex_nr > 8)
					return false;	
			}
			
			$hex_nr = 0;	
			continue;
		}
		
		if (($val >= 48 && $val <= 57) 
				|| ($val >= 65 && $val <= 70)
				|| ($val >= 97 && $val <= 102)) {
			$hex_nr ++;
			if ($hex_nr > 4)
				return false;
			
			$dim_nr = 0;
			continue;
		}
		
		return false;		
	}
	if ($total_dim_nr === 0 && $total_hex_nr != 7) 
		return false;
	
	return true;
}
function is_tip($addr)
{
	return is_ip($addr) || is_ip6($addr);
}

function ip4_netmask_bit($netmask)
{
	$ip = 0xffffffff;
	$netmask = ip2long($netmask);
	for ($i=0; $i<32; $i++) {
		if ($netmask === ($ip<<$i))
			return 32-$i;
		
	}
	return false;
	/*
	/0 	0.0.0.0 	0x00000000 	00000000 00000000 00000000 00000000
	/1 	128.0.0.0 	0x80000000 	10000000 00000000 00000000 00000000
	/2 	192.0.0.0 	0xc0000000 	11000000 00000000 00000000 00000000
	/3 	224.0.0.0 	0xe0000000 	11100000 00000000 00000000 00000000
	/4 	240.0.0.0 	0xf0000000 	11110000 00000000 00000000 00000000
	/5 	248.0.0.0 	0xf8000000 	11111000 00000000 00000000 00000000
	/6 	252.0.0.0 	0xfc000000 	11111100 00000000 00000000 00000000
	/7 	254.0.0.0 	0xfe000000 	11111110 00000000 00000000 00000000
	/8 	255.0.0.0 	0xff000000 	11111111 00000000 00000000 00000000
	/9 	255.128.0.0 	0xff800000 	11111111 10000000 00000000 00000000
	/10 	255.192.0.0 	0xffc00000 	11111111 11000000 00000000 00000000
	/11 	255.224.0.0 	0xffe00000 	11111111 11100000 00000000 00000000
	/12 	255.240.0.0 	0xfff00000 	11111111 11110000 00000000 00000000
	/13 	255.248.0.0 	0xfff80000 	11111111 11111000 00000000 00000000
	/14 	255.252.0.0 	0xfffc0000 	11111111 11111100 00000000 00000000
	/15 	255.254.0.0 	0xfffe0000 	11111111 11111110 00000000 00000000
	/16 	255.255.0.0 	0xffff0000 	11111111 11111111 00000000 00000000
	/17 	255.255.128.0 	0xffff8000 	11111111 11111111 10000000 00000000
	/18 	255.255.192.0 	0xffffc000 	11111111 11111111 11000000 00000000
	/19 	255.255.224.0 	0xffffe000 	11111111 11111111 11100000 00000000
	/20 	255.255.240.0 	0xfffff000 	11111111 11111111 11110000 00000000
	/21 	255.255.248.0 	0xfffff800 	11111111 11111111 11111000 00000000
	/22 	255.255.252.0 	0xfffffc00 	11111111 11111111 11111100 00000000
	/23 	255.255.254.0 	0xfffffe00 	11111111 11111111 11111110 00000000
	/24 	255.255.255.0 	0xffffff00 	11111111 11111111 11111111 00000000
	/25 	255.255.255.128 	0xffffff80 	11111111 11111111 11111111 10000000
	/26 	255.255.255.192 	0xffffffc0 	11111111 11111111 11111111 11000000
	/27 	255.255.255.224 	0xffffffe0 	11111111 11111111 11111111 11100000
	/28 	255.255.255.240 	0xfffffff0 	11111111 11111111 11111111 11110000
	/29 	255.255.255.248 	0xfffffff8 	11111111 11111111 11111111 11111000
	/30 	255.255.255.252 	0xfffffffc 	11111111 11111111 11111111 11111100
	/31 	255.255.255.254 	0xfffffffe 	11111111 11111111 11111111 11111110
	/32 	255.255.255.255 	0xffffffff 	11111111 11111111 11111111 11111111*/
	
}

function isCIDR(&$in)
{
	$bit = 0;
	$mask = 0;
	if (strstr($in, '/'))
		list($ip, $mask) = explode('/', $in);
	else 
		$ip = $in;
	
	if ($mask) {
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, $mask);
		if (!is_numeric($mask)) { //掩码长度必须是数字
			if (($res = ip4_netmask_bit($mask)) === false) {
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__,"Invalid netmask '$mask' of '$in'");
				return false;
			}
			$mask = $res;
			$in = $ip.'/'.$mask;
		}
		$bit = intval($mask);	
		if ($bit < 0)
			return false;
	}
	
	if (is_ip($ip) ) {
		if ($bit > 32 )
			return false;
	} else if (is_ip6($ip)) {
			if ($bit > 128 )
				return false;
		} else {
			return false;
		}
	return true;
}

/**
 * 合法
 * a.com
 * a.com:8080
 * *.a.com
 * *.a.com:8080
 */
function is_host($host)
{
	$pattern = "/^(([a-z0-9][a-z0-9-]*|\*)\.)+(com|net|cn|info|cc|me|tv|mobi|so|org|name|co|tel|biz|hk|asia|公司|网络|中国|tv|qptv)?(:\d+)?$/i";
	if (preg_match($pattern, $host)){
		return true;
	}
	
	//var_dump($pattern)
	
	if (is_ip($host)) {
		return true;		
	}
	if (is_ip6($host)) {
		return true;		
	}
	
	return false;
}

function ip_ver($addr)
{
	if (is_ip($addr)) 
		return IPVER_INET4;
	else if (is_ip6($addr))
		return IPVER_INET6;
	return false;	
}

function tip_addr_equal_prefix($addr1, $addr2, $prelen) 
{
	$v1 = ip_ver($addr1);
	$v2 = ip_ver($addr2);
	
	if (!$v1 || !$v2)
		return false;
	if ($v1 != $v2)
		return false;
	if ($v1 == IPVER_INET4) {
		!$prelen && $prelen = 32;
		if (ip2long($addr1) >> (32 - $prelen) == ip2long($addr2) >> (32 - $prelen)) { //同一子网		
			return true;	
		}
		return false;
	}
	//IP6
	
	return true;
}

function tip_addr_in($ip, $addrs) 
{
	list($addr, $mask) = explode('/', $addrs);
	if (tip_addr_equal_prefix($ip, $addr, $mask)) {						
		return true;
	}	
	return false;
}

////////////////////////////////////////////////MISC//////////////////////////////
//缩略图
function image_resize($src, $width, $height, $quality=85)
{
	$cf= get_config();		
	if ($cf['skipgif']) { //忽略对Gif图片的处理
		if (strtolower(end(explode('.', $src))) == 'gif') 
			return $src;
	}
	
	$att = Factory::GetAttach();
	return $att->resize_image($src, $width, $height, $quality);;
}


//提取状态图片
function get_status_image($status)
{
	switch($status)
	{
		case 1:
			return "enable.gif";
			break;
		default:
			return "disable.gif";
			break;
	}
}

//从数组中提取指定数量的图片
function filter_image_array(&$arr, $c, $rm=true)
{
	$udb = array();
	$idb = array();
	$i = 0;
	
	foreach($arr as $key=>$v)
	{
		if ($v['photo'] && $i++ < $c)
		{
			$idb[] =  $v;
			if(!$rm)
			{
				$udb[]= $v;
			}
		}
		else
		{
			$udb[] = $v;
		}
	}
	
	$arr = 	$udb;
	
	return $idb;
}

//从头提取$c条记录
function shift_image_array(&$arr, $c)
{
	$udb = array();
	for($i=0; $i<$c; $i++)
	{
		$v = array_shift($arr);
		if ($v)
		{
			$udb[] = $v;
		}
		else
		{
			break;
		}
	}
	return $udb;
}


function get_dbtype_select($dbtype)
{
	$res = "";
	
	$arr = array('mysql');
	foreach ($arr as $key=>$v)
	{
		$ifselect = $dbtype==$v ? 'selected' : '';
		$res .= "<option value='$v' $ifselect > $v </option>";
	}
	
	return $res;
}


function get_var_mask_content_star($status, $tid, $star_no='images/star_no.gif', $star_ok='images/star_ok.gi')
{
	$res = "";		
	$file = RPATH_CACHE.DS.'var'.DS."var7.php";
	if (file_exists($file)) {
		require $file;
		$i = 0;
		foreach ($vardb as $key=>$v)	{
			$mb = 0x1 << $key; //status bit mask			
			if (($mb & $status) === 0) {
				$res .= "<img src ='$star_no' width='14' height='14' id='star_$tid"."_$key' onmouseover='this.src=\"$star_ok\"' onmouseout='this.src=\"$star_no\"' onclick='click_csb(\"$tid\", $key);' title='$v' />";
			} else {
				$res .= "<img src ='$star_ok' width='14' height='14' id='star_$tid"."_$key' onmouseover='this.src=\"$star_no\"' onmouseout='this.src=\"$star_ok\"' onclick='click_csb(\"$tid\", $key);' title='$v' />";
			}
			$i++;
		}
	}	
	return $res;		
}	

function is_pathname($pathname)
{
	$udb = str_split($pathname, 1);
	foreach ($udb as $key=>$v)
	{
		if ( ($v >= 'A' && $v <= 'Z') || ($v >='a' && $v <= 'z')
				|| ($v >= '0' && $v <= '9')
				|| $v == '.'
				|| $v == '-'
				|| $v == '_'
			
			)
		{
			continue;
		}
		
		return false;
	}
	
	return true;
}


/**
 * is_name 是否合规名称定义：以字母或下划线开头，后面跟字母、数字或下划线
 *
 * @param mixed $name This is a description
 * @return mixed This is the return value description
 *
 */
function is_name($name)
{
	if (!$name)
		return false;
	
	$udb = str_split($name, 1);
	if (is_numeric($udb[0]))
		return false;
	
	foreach ($udb as $key=>$v)
	{
		if ( ($v >= 'A' && $v <= 'Z') || ($v >='a' && $v <= 'z')
				|| ($v >= '0' && $v <= '9')
				|| $v == '_'
			
			)
		{
			continue;
		}
		
		return false;
	}
	
	return true;
}


function is_username($name)
{
	if (!$name)
		return false;
	
	$udb = str_split($name, 1);
	if (is_numeric($udb[0]))
		return false;
	
	foreach ($udb as $key=>$v)
	{
		if ( ($v >= 'A' && $v <= 'Z') || ($v >='a' && $v <= 'z')
				|| ($v >= '0' && $v <= '9')
				|| $v == '_' || $v == '-'
			
			)
		{
			continue;
		}
		
		return false;
	}
	
	return true;
}


function is_uripath($pathname)
{
	$pathname = trim($pathname);
	$pathname = ltrim($pathname, '/');
	$pathname = rtrim($pathname, '/');
	
	$udb = explode('/', $pathname);
	foreach ($udb as $key=>$v)
	{
		if (!is_pathname($v)) 
			return false;
	}
	
	return true;
}

/**
* 检测字符串是否为UTF8编码
* @param string $str 被检测的字符串
* @return boolean
*/
function is_utf8($str)
{
	$len = strlen($str);
	for($i = 0; $i < $len; $i++){
		$c = ord($str[$i]);
		if ($c > 128) {
			if (($c > 247)) return false;
			elseif ($c > 239) $bytes = 4;
			elseif ($c > 223) $bytes = 3;
			elseif ($c > 191) $bytes = 2;
			else return false;
			if (($i + $bytes) > $len) 
				return false;
			while ($bytes > 1) {
				$i++;
				$b = ord($str[$i]);
				if ($b < 128 || $b > 191) 
					return false;
				$bytes--;
			}
		}
	}
	return true;
}


//发邮件确认
function send_email($to, $subject, $content)
{
	$cf = get_config();
	
	$params = array();
	$params['smtp_auth_type'] = $cf['smtp_auth_type'];
	$params['smtp_server_host'] = $cf['smtp_server_host'];
	$params['smtp_server_port'] = $cf['smtp_server_port'];
	$params['smtp_auth_account'] = $cf['smtp_auth_account'];
	$params['smtp_auth_passwd'] = $cf['smtp_auth_passwd'];
	
	if (!isset($params['smtp_auth_type']))
		$params['smtp_auth_type'] = '';
	
	$params['smtp_target'] = $to;
	$params['subject'] = $subject;
	$params['is_html'] = true;
	$params['content'] = $content;
	
	$mail = Factory::GetMail();			
	$res = $mail->send($params);
	if (!$res) {
		rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "call mail send failed!", $params);
	}
	return $res;
}


//获取配置对象
function get_config($reload=false)
{
	$cfg = Factory::GetConfig('system');
	
	$res =  $cfg->load($reload);
	
	return $res;
}

function set_config($cfgdb, $over=false)
{
	$cfg = Factory::GetConfig('system');
	return $cfg->save($cfgdb, $over);
}


function get_manager($reload=false)
{
	$cfg = Factory::GetConfig('manager');
	$res =  $cfg->load($reload);
	
	return $res;
}

function set_manager($cfgdb, $over=false)
{
	$cfg = Factory::GetConfig('manager');
	return $cfg->save($cfgdb, $over);
}

function get_dbconfig($name='db0', $dbtype='mysql')
{
	$cfg = Factory::GetDBConfig($name, array('dbtype'=>$dbtype));
	$res =  $cfg->load();	
	return $res;
}

function get_default_dbconfig($dbtype='mysql')
{
	return get_dbconfig('db0', $dbtype);
}

function set_dbconfig($name='db0', $cfgdb, $dbtype='mysql', $over=false)
{
	$cfg = Factory::GetDBConfig($name, array('dbtype'=>$dbtype));
	return $cfg->save($cfgdb, $over);
}


function set_default_dbconfig($cfgdb, $dbtype='mysql', $over=false)
{
	return set_dbconfig('db0', $cfgdb, dbtype, $over);
}


function get_license($reload=false)
{
	$m = Factory::GetModel('license');
	return $m->getLicense($reload);
}

function set_license($cfgdb, $over=false)
{
	$cfg = Factory::GetConfig('license');
	return $cfg->save($cfgdb, $over);
}


function get_version()
{
	$app = Factory::GetApp();
	return $app->getVersion();
}

function get_sys_name()
{
	$app = Factory::GetApp();
	return $app->getSysName();
}

function get_sys_version()
{
	return get_version();
}


function get_params($appname, $reload=false)
{
	$modname = $appname_.'_params';	
	$m = Factory::GetModel($modname);
	return $m->get($appname);
}

function set_params($appname, $params, $over=false)
{
	$modname = $appname.'_params';	
	$m = Factory::GetModel($modname);
	return $m->set($params);
}


function get_ioparams(&$ioparams=array())
{
	$r = Factory::GetRequest();
	$params = array();
	$r->getRequestParams($ioparams);
	return $ioparams;
}

function get_child_template_select($child, $root, $tpl, $permit_select_index=false)
{
	!$root && $root = 'default';
	$res = "";
	$templates = get_tpls();
	$template= $templates[$root];	
	if ($permit_select_index) {
		$childs = $template['index'];
		if ($childs) {
			foreach ($childs as $key=>$v) {
				$selected = $key == $tpl ? 'selected': '';
				$res .= "<option value='$key' $selected>$v</option>";
			}
		}
	}
	
	$childs = $template[$child];
	if ($childs) {
		foreach ($childs as $key=>$v) {
			$selected = $key == $tpl ? 'selected': '';
			$res .= "<option value='$key' $selected>$v</option>";
		}
	}		
	return $res;
}

//获取语言数组
function get_i18n($key='')
{
	$i18n = Factory::GetLanguage();
	if ($key) {
		if (isset($i18n[$key]))
			return $i18n[$key];
		else
			return array();
	}
	else
		return $i18n;
}

//提取查显示文本信息
function i18n($fmtstr, $default='')
{
	$lang = get_i18n();
	$t_i18ndb = $lang['t_i18ndb'];
	$str = $fmtstr;
	if ($str && (!empty($lang[$str])|| !empty($t_i18ndb[$str]))) {
		$str = !empty($lang[$str])?$lang[$str]:$t_i18ndb[$str];
		$args = func_get_args();
		if (count($args) > 1) {
			$phrase = array_shift($args);
			$str = vsprintf($str, $args);		
		}
	} else if ($default) {
			$str = $default;
		}	
	return $str;
}


//退出
function rexit($message = 0) 
{
	exit($message);
}


function get_userinfo()
{
	$userinfo = array();
	$app = Factory::GetApp();
	if ($app) {
		$res = $app->getUserInfo();
		//fixed
		if ($res) {
			$userinfo = $res;
			unset($userinfo['password']);
			unset($userinfo['flags']);
			unset($userinfo['rid']);
			unset($userinfo['permisions']);
		}
	}
	return $userinfo;
}

function get_uid()
{
	$app = Factory::GetApp();
	if ($app) {
		$userinfo = $app->getUserInfo();
		return $userinfo['id'];
	}
	return 0;
}

function hasPrivilegeOf($component, $task='show')
{
	$app = Factory::GetApp();
	if ($app)
		return $app->hasPrivilegeOf($component, $task);
	return false;	
}

function islogin()
{
	$app = Factory::GetApp();
	if (!$app)
		return false;
	return $app->isLogin();	
}


//获取管理员信息
function &get_admin()
{
	return get_userinfo('admin');
}

function &get_userinfo_by_uid($uid)
{
	$uid = intval($uid);
	$db = Factory::GetDBO();
	$sql = "select * from cms_user where uid=$uid";
	$res = $db->get_one($sql);
	return $res;
}

//获取错误信息
function get_error()
{
	$app = Factory::GetApp();
	$msg =  $app->getMessage();
	
	
	return $msg;
}

function get_errors_html()
{
	$app = Factory::GetApplication();
	$messages = $app->getMessages();
	if (!$messages || !is_array($messages))
		return false;
	
	/*$res = "<div class='t'><div class='sys-errors'><ul>";
	foreach ($errors as $key=>$v) {
		$level = $levels[$v];		
		$res .= "<li class='l$level'>".$v.'</li>';
	}
	$res .= "</ul></div></div>";*/
	
	$res = '';
	foreach ($messages as $level=>$v) {
		if ($level < RC_LOG_NOTICE)
			$label = 'danger';
		else
			$label = 'info';
		
		foreach($v as $k2=>$v2) {
			$res .= "<div class='alert alert-$label alert-dismissable'>";		
			$res .= "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'></button>
					$v2";
			$res .= "</div>";
		}
	}
	
	return $res;
}

//设置错误信息
function set_error($error)
{
	$errstr = i18n($error);
	
	$args = func_get_args();
	$phrase = array_shift($args);
	
	$res = vsprintf($errstr, $args);	
	rlog(RC_LOG_DEBUG, __FILE__, __LINE__, $res);
	
	$app =  Factory::GetApplication();
	return $app->setMessage($res, 1);
}

function set_message($msg)
{
	$strmsg = i18n($msg);
	
	$args = func_get_args();
	$phrase = array_shift($args);
	
	$res = vsprintf($strmsg, $args);	
	
	$app =  Factory::GetApplication();
	return $app->setMessage($res, 0);
}

//管理员记录日志
function mlog_error($error, $type=ETYPE_OPT, $data=null)
{
	set_error($error);
	rlog(RC_LOG_DEBUG, __FILE__, __LINE__,$error, $data, $type);
}

//显示错误信息
function show_error($msg, $backurl="", $target="_self", $ext=null)
{
	$app = Factory::GetApplication();
	return $app->showMessage($msg, $backurl, $target, $ext, "error");
}

//显示消息
function show_message($msg, $backurl="", $target="_self", $ext=null)
{
	$app =  Factory::GetApplication();
	return $app->showMessage($msg, $backurl, $target, $ext, "success");
}

function showMsg($status, $msg='', $backurl='')
{
	$msg = '';
	//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "status=$status");
	if ($status === false) {
		$status = RC_E_FAILED;
		$msg = get_error();	
		if (!$msg)
			$msg = get_error_string($status);
	}
	else if ($status === true){
		$status = 0;
		$msg = get_error_string($status);
	} else if ($status > 0) {
			$status = 0;
			$msg = get_error();	
			if (!$msg)
				$msg = get_error_string($status);
		} else {
			if ($status == RC_E_FAILED)
				$msg = get_error();	
			if (!$msg)
				$msg = get_error_string($status);
		}	
	
	$status = intval($status);
	
	return ($status >= 0)?show_message($msg, $backurl):show_error($msg,  $backurl);
}

function showErr($status=-1, $msg='', $backurl='')
{
	return showMsg($status, $msg, $backurl);
}


//重定向
function redirect($url)
{
	header( 'HTTP/1.1 301 Moved Permanently' );
	header( 'Location: ' . $url );
	exit;
}




//检查许可证
function check_license()
{
	$cf = get_config();
	if (!$cf['sid'])
		return false;
	//var_dump(defined(_RKEY)); exit;
	if (rkey_check_guid($cf['sid']) != true) 
		return false;
	
	$license_file = RPATH_CONFIG.DS."license.key";
	if (!file_exists($license_file)) 
		return false;
	
	$content = my_read($license_file);
	$key = substr(md5($cf['sid']), 10, 8);
	$res = mcrypt_des_decode($key, $content);
	if (!$res)
		return false;
	
	$res = unserialize($res);
	$pid = get_product_id();
	$_sid = $res['sid'];
	$_pid = $res['pid'];
	
	if ($_sid != $cf['sid']) { 
		rlog(RC_LOG_ERROR, __FILE__, __LINE__, "invalid serail id '$_sid'!");
		return false;
	}
	
	if ($pid != $_pid) {
		rlog(RC_LOG_ERROR, __FILE__, __LINE__, "invalid product id '$_pid'!");
		return false;
	}
	
	$expired = intval($res['expired']);
	if ($expired > 0) {
		if (time() > $expired) {
			//set_error('str_sn_expired');
			return false;
		}
	}	
	
	return $res;		
}


function get_install_dir()
{
	return RPATH_ROOT;	
}

function get_product_name()
{
	$app = Factory::GetApp();
	return $app->getProductName();
}

/**
 * get_product_id 读取产品ID
 *
 * @return mixed 产品ID
 *
 */
function get_product_id()
{
	$guid = get_guid();	
	$product_id = md5($guid.'_'.RPATH_ROOT);	
	return $product_id;
}

function get_product_version()
{
	$app = Factory::GetApp();
	return $app->getProductVersion();
}


function get_product_key($product_id)
{
	return substr(md5($product_id), 10, 8);
}

function get_sysinfo()
{
	$manager = get_manager();
	
	$params = array();
	$params['product_name'] = get_product_name();
	$params['product_id'] = get_product_id();
	$params['product_version'] = get_product_version();
	$params['sys_name'] = get_sys_name();
	$params['sys_version'] = get_sys_version();
	$params['guid'] = get_guid();
	$params['installdir'] = get_install_dir();		
	$params['email'] = $manager['manager_email'];
	$params['type'] = 3;//RELAXCMS
	
	return $params;
}

//得到拼装后的字符号
function getFillNumber($date,$code = NULL)
{
	if(empty($code))
	{
		$code = "0001";
	}
	else
	{
		$code = intval($code);
		$code += 1;
		$code = str_pad($code,4,"0",STR_PAD_LEFT);
	}
	return $date.$code;
}

function get_sid()
{
	$cf = get_config();
	$sid = $cf['sid'];
	
	if (!$sid)
		show_error("str_no_sid");
	return $sid;	
}


function format_datetime($datetime, $format='yyyy-mm-dd')
{
	$len = strlen($format);
	$res = substr($datetime, 0, $len);
	if ($res == '0000-00-00')
		$res = '';
	
	return $res;
}



function tformat_sec2hhmmss($sec)
{
	$hh = $sec/RC_TIMESEC_HOUR;
	$sec = $sec%RC_TIMESEC_HOUR;
	$mm = $sec/RC_TIMESEC_MIN;
	$ss = $sec%RC_TIMESEC_MIN;
	
	$ss = sprintf("%02d:%02d:%02d", $hh, $mm, $ss);
	
	return $ss;
}


function is_windows()
{
	//PHP> = 5.3
	$res = defined('PHP_WINDOWS_VERSION_MAJOR');
	return $res;
	
	//if (isset($_SERVER['WINDIR'])) //命令行执行时，此字段为ＮＵＬＬ
	//	return true;
	//return false;
}


function is_sbt($name)
{
	$sbt = isset($_REQUEST['sbt'])?$_REQUEST['sbt']:false;
	if (!$sbt) 
		return false;
	
	$cf = get_config();
	$hash = $cf["hash"];
	//session_start();
	//$skey = $_COOKIE['sbtkey'];
	$skey = $_SESSION['sbtkey_'.$name];
	$__sbt = md5($skey.'-'.$hash);
	
	if ($__sbt && $__sbt == $sbt) {
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__,"check sbt OK,sbt:".$sbt.", __sbt=".$__sbt);
		return true;
	} else {
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__,"check sbt failed sbt:".$sbt.", __sbt=".$__sbt.", name=$name");		
		return false;
	}
}

function mk_sbt($name='')
{
	$keyname = 'sbtkey_'.$name;
	
	$cf = get_config();
	$hash = $cf["hash"];
	if (isset($_SESSION[$keyname])) {
		$skey = $_SESSION[$keyname];
	} else {
		$skey = rand().'-'.time();	
	}
	
	$sbt = md5($skey.'-'.$hash);
	//$sbt = md5($hash);
	//session_start();
	$_SESSION[$keyname] = $skey;
	//setcookie("sbtkey", $skey);	
	//rlog(RC_LOG_DEBUG, __FILE__, __LINE__,"mk keyname=$keyname, sbtkey:".$skey);
	
	return $sbt;
}


function mk_hkey($seed='')
{
	$key = time().'_'.rand();
	$hkey = substr(md5($key.'-'.$seed), 8, 8);
	return $hkey;	
}

function is_rkey_support()
{
	return !!phpversion('rkey');
}

function check_lssid($lssid)
{
	$cf = get_config();
	$accesskey = $cf['accesskey'];
	$lssid = str_replace(' ', '+', $lssid);
	
	//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, 'accesskey='.$accesskey.', $lssid='.$lssid);
	$res = false;
	if (function_exists('rkey_check_lssid')) {
		$res = rkey_check_lssid($lssid, $accesskey);		
	} else {
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "WARNING: no rkey_check_lssid!");	
		$res = true;	
	}
	return $res;
}

function makeQuery($options)
{
	$query = "";
	foreach ($options as $key=>$v) {
		if ($query)
			$query .= "&";
		$query .= "$key=".urlencode($v);			
	}
	
	return $query;
}

function get_lssid()
{
	if (function_exists('rkey_get_lssid')) {
		if (!($lssid = rkey_get_lssid())) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "call rkey_get_lssid failed!");
			$lssid = md5('s.x.w.a.r.e'.time());
		} 
	} else {
		$lssid = md5('s.x.w.a.r.e'.time());		
	}
	$lssid = urlencode($lssid);
	
	return $lssid;
}


function requestSAPI($apiurl, $params=array())
{
	$cf = get_config();
	if (!is_url($apiurl)) {
		$url = isset($cf['crabd_url'])?$cf['crabd_url']:'http://127.0.0.1:20080/sapi';
		$url .= s_hslash($apiurl);
	} else {
		$url = $apiurl;
	}
	
	//lssid
	$lssid = get_lssid();
	
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl, CURLINFO_HEADER_OUT, true);
	curl_setopt($curl, CURLOPT_USERAGENT, "curl-rc");
	
	//COOKIE
	curl_setopt($curl, CURLOPT_HTTPHEADER, array("Cookie:ssid=".$lssid)); //把ssid放到header中发送
	curl_setopt($curl, CURLOPT_POST, true);//开启post
	if ($params)
		@curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($params)); //Post
	
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
	$res = curl_exec($curl);
	curl_close($curl);
	
	//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "url=$url, res: $res");	
	
	return $res;
}





/**
 * 通过sapi执行指定命令
 *
 * @param mixed $id This is a description
 * @return mixed This is the return value description
 * $id = 1
 *	$cmd = "shell|apache-reload.sh";
 * $id = 2
 *	$cmd = "shell|apache-restart.bat"
 * $id = 3
 *
 */
function sapi_shell($id, $options=array())
{
	$options['id'] = $id;
	$data = requestSAPI('/shell/run', $options);
	
	rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__,"sapi_shell res: $data");
	
	return $data;
}

function sapi_shell_exec($cmd, $options=array())
{
	$options['cmd'] = $cmd;
	$data = requestSAPI('/shell/exec', $options);
	
	rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__,"sapi_shell res: $data");
	
	return $data;
}



function sapi_restart_apache($options=array())
{
	return sapi_shell(1,$options);
}

/**
 * 重启 NGINX
 *
 * @return mixed This is the return value description
 *
 */
function sapi_restart_nginx($options=array())
{
	return sapi_shell(4, $options);
}


/**
 * 重启 OS
 *
 * @return mixed This is the return value description
 *
 */
function sapi_reboot($options=array())
{
	return sapi_shell(5, $options);
}

function sapi_shutdown($options=array())
{
	return sapi_shell(7, $options);
}


/**
 * 设置 webtimer
 *
 * @return mixed This is the return value description
 *
 */
function sapi_setwebtimer($options=array())
{
	return sapi_shell(6, $options);
}


/**
 * 执行vhost创建或删除命令
 *
 * @param mixed $subcmd This is a description
 * @param mixed $username This is a description
 * @return mixed This is the return value description
 *
 */
function sapi_shell_vhost($subcmd, $params)
{
	$params['subcmd'] = $subcmd;
	$params['username'] = $params['name'];
	
	$res = sapi_shell(3, $params);
	rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "sapi_shell_vhost res: ", $res);	
	
	return $res;
}


function sapi_check_server_active($sapiurl='')
{
	$params = array();
	$params['a'] = 1;
	
	$data = requestSAPI($sapiurl.'/system/checking', $params);
	
	rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "sapi res: $data");
	$res = CJson::decode($data);
	//rlog($res);	
	if (!$res) {
		rlog(RC_LOG_ERROR, __FILE__, __LINE__, "invalid json result!url=$url, data=".$data);
		return false;
	}			
	if (intval($res['status']) === 0) {
		return true;
	}	
	return false;
}


function run_local_service($cmd)
{
	$localapiurl = 'http://127.0.0.1/rc5/api';
	
	$b64cmd = base64_encode($cmd);		
	$url = $localapiurl."/runlocalservice?cmd=$b64cmd";
	
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl, CURLINFO_HEADER_OUT, true);
	curl_setopt($curl, CURLOPT_USERAGENT, "curl-rc");
	
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
	$res = curl_exec($curl);
	curl_close($curl);
	
	rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "url=$url, run_local_service res: $res");	
	
	return $res;
}


function http_request($url, &$httpCode=0)
{
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl, CURLINFO_HEADER_OUT, true);
	curl_setopt($curl, CURLOPT_USERAGENT, "curl-rc");
	curl_setopt($curl, CURLOPT_HEADER, 1);
	
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
	$res = curl_exec($curl);
	// 获得响应结果里的：头大小
	//$headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
	$httpCode = curl_getinfo($curl,CURLINFO_HTTP_CODE); 
	
	curl_close($curl);
	
	//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "url=$url, StatusCode=$httpCode, request_url res: $res, header=$header");	
	
	return $res;
}


/**
 * get_table_select
 *
 * @param mixed $value This is a description
 * @param mixed $table This is a description
 * @param mixed $id This is a description
 * @param mixed $title This is a description
 * @return mixed This is the return value description
 *
 */
function get_table_select($table, $id)
{
	$udb = get_cache_table($table);
	$t_select = "";
	foreach ($udb as $key=>$v){
		$selected = $id == $v['TREEID']? 'selected' :'';
		$t_select .= "<option value='{$v[TREEID]}' $selected >$v[title]</option>";
	}	
	return $t_select;
}

/**
 * 字段排序
 *
 * @param mixed $array 字段数组
 * @param mixed $$fieldname 排序字段名称，如：sort
 * @param mixed $desc 是否降序方向，默认为false(升序)
 * @return mixed 成功: true, 失败: false
 *
 */
function array_sort_by_field(&$array, $fieldname, $desc = false)
{
	$fieldArr = array();
	foreach ($array as $k => $v) {
		$fieldArr[$k] = isset($v[$fieldname])?$v[$fieldname]:0;
	}
	$sort = $desc == false ? SORT_ASC : SORT_DESC;
	return array_multisort($fieldArr, $sort, $array);
}


function utf8_strlen($str) 
{
	
	$count = 0;
	
	for($i = 0; $i <strlen($string); $i++){
		
		$value = ord($str[$i]);
		if ($value > 127) {
			$count++;		
			if ($value >= 192 && $value <= 223) 
				$i++;
			elseif($value >= 224 && $value <= 239) 
				$i = $i + 2;
			elseif($value >= 240 && $value <= 247) 
				$i = $i + 3;
			else
				return false;
		}
		$count++;
	}
	
	return $count;
	
}
/*
 1. 梁祝.mp4(C1 BA D7 A3 2E 6D 70 34)
符合UTF-8双字节码（110XXXXX - 10XXXXXX）
*/
function safeEncoding($string, $outEncoding = 'UTF-8', $gbcheck=false) 
{
	$encoding = "UTF-8";
	for ($i=0; $i < strlen($string); $i++) {
		$val = ord($string{$i});		
		if ($val < 128) //单字节ascii
			continue;
		
		if ($val >= 192 && $val <= 223) { // 2bytes(110x xxxx 10xx xxxx)  C0(1100 0000)-DF(1101 1111)
			$val2 = ord($string{$i+1});
			if ($val2 >= 128 && $val2 <= 191) { //
				if ($gbcheck) {
					$g1 = $val - 0xa0;
					if ($g1 >= 1 && $g1 <= 94) { // 区位码转内码CP936, 区+0xa0, 位+0xa0
						//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN4...");
						//第一个字节判断通过 
						$g2 = $val2 -0xa0;
						if ($g2 >= 1 && $g2 <= 94) {
							//第二个字节判断通过 
							$encoding = "GB2312";
							break;
						}
					}
				}
				
				$i++;
				//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN1...");
				continue;
			}
		} else if ($val >= 224 && $val <= 239) { //3bytes
			$val2 = ord($string{$i+1});
			if ($val2 >= 128 && $val2 <= 191) {
				$val3 = ord($string{$i+2});
				if ($val3 >= 128 && $val3 <= 191) {
					$i += 2;
					//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN2...");
					
					continue;
				}
			}
		} else if ($value >= 240 && $value <= 247) { //4bytes
				$val2 = ord($string{$i+1});
				if ($val2 >= 128 && $val2 <= 191) {
					$val3 = ord($string{$i+2});
					if ($val3 >= 128 && $val3 <= 191) {
						$val4 = ord($string{$i+3});
						if ($val4 >= 128 && $val4 <= 191) {
							$i += 3;
							//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN3...");
							continue;
						}
					}
				}
			}
		
		
		$g1 = ord($string{$i}) - 0xa0;
		if ($g1 >= 1 && $g1 <= 94) { // 区位码转内码CP936, 区+0xa0, 位+0xa0
			//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "IN4...");
			//第一个字节判断通过 
			$g2 = ord($string{++$i}) -0xa0;
			if ($g2 >= 1 && $g2 <= 94) {
				//第二个字节判断通过 
				$encoding = "GB2312";
				break;
			}
		}
	}
	
	//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "encoding=$encoding, outEncoding=$outEncoding");
	
	if (strtoupper($encoding) == strtoupper($outEncoding))
		return $string;
	else
		return iconv($encoding, $outEncoding, $string);
}

function s_urlencode($url) 
{
	return strtr($url, array(" "=>"%20",
				"%"=>"%25",
				"&"=>"%26",
				"<"=>"%3C",
				">"=>"%3E",
				));
}

function s_urldecode($path) 
{
	return rawurldecode($path);
}

function isWindows()
{
	//WINNT
	//rlog(PHP_OS);
	return substr(PHP_OS, 0, 3) == 'WIN';
}


function form_rule($params)
{
	//print_R($params);
	$formRule = $params["fr"];
	$thisname = $params["name"];
	$idextra = $params["idextra"];
	$namesuffix = $params["suffix"];
	if(isset($formRule[$thisname])){
		$type = $formRule[$thisname]['type'];
	}
	if ($namesuffix == '[]') { //数组类型
		$output = " name=\"{$thisname}[]\" ";
	} else {
		if ($type != 'file') {
			$output = " name=\"params[$thisname]\" ";
		} else {
			$output = " name=\"$thisname\" ";
		}
	}
	if ($idextra !="" ) {
		$output .= " id=\":f".$thisname."_".$idextra."\" ";
	} else {
		$output .= " id=\":f".$thisname."\" ";
	}		
	
	if (isset($formRule[$thisname])) {
		foreach($formRule[$thisname] as $k=>$v){
			$str = "";
			if (is_array($v)) {
				$str = join(",", $v);
			} elseif(is_bool($v)) {
				$str = $v?"true":"false";
			} else {
				$str = $v;
			}
			$output	.= " $k=\"$str\" ";			
		}
	}
	return $output;
}


function formatBytes($bytes, $precision = 2) { 
	$units = array('B', 'KB', 'MB', 'GB', 'TB'); 
	
	$bytes = max($bytes, 0); 
	$pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
	$pow = min($pow, count($units) - 1); 
	$bytes /= (1 << (10 * $pow));
	return round($bytes, $precision) . ' ' . $units[$pow]; 
}

function getFormId()
{
	return md5(date("Y-m-d H:i:s").rand(1000, 10000));
}

function blockSubmitTwice($formId)
{
	if(!isset($_SESSION['formIds'])){
		$_SESSION['formIds'] = array();
	}
	if (strlen($formId)<30) {
		return false;
	}
	if (in_array($formId, $_SESSION['formIds'])) {
		return false;
	} else {
		$_SESSION['formIds'][] = $formId;
		return true;
	}
}

function validatePasswd($passwd){
	if(strlen($passwd)<8||strlen($passwd)>88){
		return "length";
	}
	if(preg_match("/\d+/", $passwd) 
			&& preg_match("/[a-z]+/", $passwd)
			&& preg_match("/[A-Z]+/", $passwd)
			&& preg_match("/[-`=\\\[\];',\.\/~!@#$%^&\*\(\)_\+\|\{\}:\"<>\?]/", $passwd)){
		
	}else{
		return "pattn";
	}
	return "ok";
}

function validateStringLen($string, $min=-1, $max=-1){
	if($min>-1 && strlen($string)<$min){
		return false;
	}
	if($max>-1 && strlen($string)>$max){
		return false;
	}
	return true;
}

function validateForm_backup(&$formRules, $post){
	$cf = get_config();
	
	foreach($post as $k=>$v){
		if(!isset($formRules[$k])){
			continue;
		}
		if(isset($formRules[$k]["canbenull"]) && $formRules[$k]["canbenull"] && trim($v)==''){
			continue;
		}
		if(isset($formRules[$k]["except"]) && in_array($v,$formRules[$k]["except"])) {
			continue;
		}
		if($formRules[$k]["passwd"]){
			$passwd = $v;
			if(strlen($passwd)<intval($cf['min_passwd'])||strlen($passwd)>88){
				$formRules[$k]["error"] = true;
			}
			if(intval($cf['passwd_strength'])>0 ){
				if(preg_match("/\d+/", $passwd)
						&& preg_match("/[a-z]+/", $passwd)
						&& preg_match("/[A-Z]+/", $passwd)
						&& preg_match("/[-`=\\\[\];',\.\/~!@#$%^&\*\(\)_\+\|\{\}:\"<>\?]/", $passwd)){
				}else{
					$formRules[$k]["error"] = true;
				}
			}
		}
		if(isset($formRules[$k]["domain"])){
			if(!preg_match("/^([a-z0-9][a-z0-9-]*\.)+(com|net|cn|info|cc|me|tv|mobi|so|org|name|co|tel|biz|hk|asia|公司|网络|中国)$/i", $v)){
				$formRules[$k]["error"] = true;
			}
		}
		if(isset($formRules[$k]["isip"])){
			$ipreg = "/(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9]|0)\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9]|0)\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9]|0)\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[0-9])(\/\d{1,2})?/";
			if(!preg_match($ipreg, $v)){
				$formRules[$k]["error"] = true;
			}
		}
		
		if(isset($formRules[$k]["isipreg"])){
			$ipreg = "/((25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9]|0)|\*)\.((25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9]|0)|\*)\.((25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9]|0)|\*)\.((25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[0-9])(\/\d{1,2})?|\*)/";
			if(!preg_match($ipreg, $v)){
				$formRules[$k]["error"] = true;
			}
		}
		
		if(isset($formRules[$k]["len"]) && is_array($formRules[$k]["len"])){
			$minmax = $formRules[$k]["len"];
			if ( ($minmax[0]>-1 && mb_strlen($v, "UTF-8") < $minmax[0]) || ($minmax[1]>-1 && mb_strlen($v, "UTF-8")> $minmax[1]) ){
				$formRules[$k]["error"] = true;
			}
		}
		if(isset($formRules[$k]["int"]) && is_array($formRules[$k]["int"])){
			$minmax = $formRules[$k]["int"];
			if (!preg_match("/^\d+$/", $v) || ($minmax[0]>-1 && intval($v) < $minmax[0]) || ($minmax[1]>-1 && intval($v) > $minmax[1]) ){
				$formRules[$k]["error"] = true;
			}
		}
		if($formRules[$k]["type"]=="select"){
			if($v=="-1"){
				$formRules[$k]["error"] = true;
			}
		}
		if(isset($formRules[$k]["email"])){
			if(!preg_match("/^(\w)+(\.\w+)*@(\w)+((\.\w+)+)$/",$v)){
				$formRules[$k]["error"] = true;
			}
		}
		if(isset($formRules[$k]["reg"])){
			if(!preg_match($formRules[$k]["reg"], $v)){
				$formRules[$k]["error"] = true;
			}
		}
		//中文、英文、数字、下划线、中线
		if(isset($formRules[$k]["normaltext"])){
			$ipreg = "/^[\x80-\xff_a-z0-9]+$/i";
			if(!preg_match($ipreg, $v)){
				$formRules[$k]["error"] = true;
			}
		}
		
	}
	
	$validatePassed = true;
	foreach($formRules as $k=>$v){
		if(isset($v["error"]) && $v["error"]==true){
			$validatePassed = false;
			continue;
		}
	}
	return $validatePassed;
	
}


/**
 * 是不是通过验证
 * @param unknown_type $formRules
 * @param unknown_type $post
 * @return unknown
 */
function validateForm_step1(&$formRules, $k, $v){
	$cf = get_config();
	if(!isset($formRules[$k])){
		return;
	}
	if(isset($formRules[$k]["canbenull"]) && $formRules[$k]["canbenull"] && !is_array($v) && trim($v)==''){
		return;
	}
	if(isset($formRules[$k]["except"]) && in_array($v,$formRules[$k]["except"])) {
		return;
	}
	if($formRules[$k]["passwd"]){
		$passwd = $v;
		if(strlen($passwd)<intval($cf['min_passwd'])||strlen($passwd)>88){
			$formRules[$k]["error"] = true;
		}
		if(intval($cf['passwd_strength'])>0 ){
			if(preg_match("/\d+/", $passwd)
					&& preg_match("/[a-z]+/", $passwd)
					&& preg_match("/[A-Z]+/", $passwd)
					&& preg_match("/[-`=\\\[\];',\.\/~!@#$%^&\*\(\)_\+\|\{\}:\"<>\?]/", $passwd)){
			}else{
				$formRules[$k]["error"] = true;
			}
		}
	}
	if(isset($formRules[$k]["isuri"])){
		if(!preg_match("/^\/[a-z0-9-_\/\.]+[a-z0-9-_\/\.]$/i", $v)){
			$formRules[$k]["error"] = true;
		}
	}	
	
	if(isset($formRules[$k]["isuris"])){
		$v = trim($v);
		$v = trim(str_replace("\\r\\n", "\\n", $v));
		$va = explode("\\n",$v);
		foreach ($va as $kk=>$vv) {
			$vv = trim($vv);
			if(!preg_match("/^\/[a-z0-9-_\/\.]+[a-z0-9-_\/\.]$/i", $vv)){
				$formRules[$k]["error"] = true;
			}
		}
	}	
	
	if(isset($formRules[$k]["isipreg"])){
		$ipreg = "/((25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9]|0)|\*)\.((25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9]|0)|\*)\.((25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9]|0)|\*)\.((25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[0-9])(\/\d{1,2})?|\*)/";
		if(!preg_match($ipreg, $v)){
			$formRules[$k]["error"] = true;
		}
	}
	
	if(isset($formRules[$k]["domain"])){
		if(!preg_match("/^([a-z0-9][a-z0-9-]*\.)+(com|net|cn|info|cc|me|tv|mobi|so|org|name|co|tel|biz|hk|asia|公司|网络|中国)$/i", $v)){
			$formRules[$k]["error"] = true;
		}
	}
	
	if(isset($formRules[$k]["domains"])){
		$v = trim($v);
		$v = trim(str_replace("\\r\\n", "\\n", $v));
		$va = trim(explode("\\n",$v));
		foreach ($va as $kk=>$vv) {
			$vv = trim($vv);
			if(!preg_match("/^([a-z0-9][a-z0-9-]*\.)+(com|net|cn|info|cc|me|tv|mobi|so|org|name|co|tel|biz|hk|asia|公司|网络|中国)$/i", $vv)){
				$formRules[$k]["error"] = true;
			}
		}
	}		
	
	if(isset($formRules[$k]["domainalias"])){
		$v = trim($v);
		$v = trim(str_replace("\\r\\n", "\\n", $v));
		$va = trim(explode("\\n",$v));
		foreach ($va as $kk=>$vv) {
			$vv = trim($vv);
			if(!preg_match("/^([a-z0-9\*][a-z0-9-\*]*\.)+(com|net|cn|info|cc|me|tv|mobi|so|org|name|co|tel|biz|hk|asia|公司|网络|中国)$/i", $vv)){
				$formRules[$k]["error"] = true;
			}
		}
	}	
	
	if(isset($formRules[$k]["isip"])){
		$ipreg = "/(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9])\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9]|0)\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9]|0)\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[0-9])(\/\d{1,2})?/";
		if(!preg_match($ipreg, $v)){
			$formRules[$k]["error"] = true;
		}
	}	
	if(isset($formRules[$k]["isips"])){
		$v = trim($v);
		$v = trim(str_replace("\\r\\n", "\\n", $v));
		$va = trim(explode("\\n",$v));
		$ipreg = "/(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9])\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9]|0)\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9]|0)\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[0-9])(\/\d{1,2})?/";
		foreach ($va as $kk=>$vv) {
			$vv = trim($vv);
			if(!preg_match($ipreg, $vv)){
				$formRules[$k]["error"] = true;
			}
		}
	}
	if(isset($formRules[$k]["isipports"])){
		$v = trim($v);
		$v = trim(str_replace("\\r\\n", "\\n", $v));
		$va = trim(explode("\\n",$v));
		$ipreg = "/(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9])\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9]|0)\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9]|0)\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[0-9])(\/\d{1,2})?(:\d+)?/";
		$ipreg2 = "/\d+.*?:(\d+)$/";
		foreach ($va as $kk=>$vv) {
			$vv = trim($vv);
			if(!preg_match($ipreg, $vv)){
				$formRules[$k]["error"] = true;
			}
			if(preg_match($ipreg2, $vv, $itemp)){
				if($itemp[1]<1 || $itemp[1]>65535){
					$formRules[$k]["error"] = true;
				}
			}
		}
	}
	if(isset($formRules[$k]["statuscode"])){
		$v = trim($v);
		$va = trim(explode("\|",$v));
		$ipreg = "/^[\d]{3}$/";
		foreach ($va as $kk=>$vv) {
			$vv = trim($vv);
			if(!preg_match($ipreg, $vv)){
				$formRules[$k]["error"] = true;
			}
		}
	}
	if(isset($formRules[$k]["lens"]) && is_array($formRules[$k]["lens"])){
		$v = trim($v);
		$v = trim(str_replace("\\r\\n", "\\n", $v));
		$va = explode("\\n",$v);
		$minmax = $formRules[$k]["lens"];
		foreach ($va as $kk=>$vv) {
			$vv = trim($vv);
			if ( ($minmax[0]>-1 && mb_strlen($vv, "UTF-8") < $minmax[0]) || ($minmax[1]>-1 && mb_strlen($vv, "UTF-8")> $minmax[1]) ){
				$formRules[$k]["error"] = true;
			}
		}
	}	
	if(isset($formRules[$k]["len"]) && is_array($formRules[$k]["len"])){
		$minmax = $formRules[$k]["len"];
		if ( ($minmax[0]>-1 && mb_strlen($v, "UTF-8") < $minmax[0]) || ($minmax[1]>-1 && mb_strlen($v, "UTF-8")> $minmax[1]) ){
			$formRules[$k]["error"] = true;
		}
	}	 
	if(isset($formRules[$k]["int"]) && is_array($formRules[$k]["int"])){
		$minmax = $formRules[$k]["int"];
		if (!preg_match("/^\d+$/", $v) || ($minmax[0]>-1 && intval($v) < $minmax[0]) || ($minmax[1]>-1 && intval($v) > $minmax[1]) ){
			$formRules[$k]["error"] = true;
		}
	}
	if($formRules[$k]["type"]=="select"){
		if($v=="-1"){
			$formRules[$k]["error"] = true;
		}
	}
	if(isset($formRules[$k]["email"])){
		if(!preg_match("/^(\w)+(\.\w+)*@(\w)+((\.\w+)+)$/",$v)){
			$formRules[$k]["error"] = true;
		}
	}
	if(isset($formRules[$k]["reg"])){
		if(!preg_match($formRules[$k]["reg"], $v)){
			$formRules[$k]["error"] = true;
		}
	}
}


function setLastError($key,$msg=null)
{
	if (!$msg) {
		$msg = i18n($key);
		$args = func_get_args();
		$phrase = array_shift($args);
		$msg = vsprintf($msg, $args);		
	}
	$msg = "<p class='formerror '>$msg</p>";
	$app =  Factory::GetApplication();
	$app->setLastMsg($key, $msg);
}
function validateForm(&$formRules, $post){
	foreach($post as $k=>$v){
		if(is_array($v)){
			foreach($v as $k1=>$v1){
				validateForm_step1($formRules, $k, $v1);
			}
		}else{
			validateForm_step1($formRules, $k, $v);
		}
	}
	
	$validatePassed = true;
	foreach($formRules as $k=>$v){
		if (isset($v["error"]) && $v["error"] == true) {
			$validatePassed = false;
			if ($v['msg'])
				setLastError('sys_message', $v['msg']);
			continue;
		}
	}
	return $validatePassed;	
}



function run($cmd, $backend=false)
{
	ob_start();
	// 成功：最后一行，失败false
	if ($backend) {
		if (is_windows())
			$cmd = "start /min $cmd";
		else
			$cmd .= "  2>&1 > /dev/null &";
	}
	
	$res = @system($cmd, $return);
	if ($res === false) {
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "call system failed!cmd='$cmd', return=$return");
	}
	ob_end_clean();
	//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "cmd:'$cmd', return=$return");
	return($res !== false);
}

function run_output($cmd, &$return, $std=false)
{
	ob_start();
	if ($std) {
		passthru($cmd, $return);
	} else {
		passthru($cmd . " 2>&1", $return);
	}
	$msg = ob_get_contents();
	ob_end_clean();
	
	//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "cmd-output:'$cmd', return=$return");
	return($msg);
}

function isOn($v)
{
	if ($v) {
		return strtolower($v) == "on";
	}
	return false;
}

function showStatus($status, $data = array())
{
	$msg = '';
	//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "status=$status");
	if ($status === false) {
		$status = RC_E_FAILED;
		$msg = get_error();	
		if (!$msg)
			$msg = get_error_string($status);
	}
	else if ($status === true){
		$status = 0;
		$msg = get_error_string($status);
	} else if ($status > 0) {
			$status = 0;
			$msg = get_error();	
			if (!$msg)
				$msg = get_error_string($status);
		} else {
			if ($status == RC_E_FAILED)
				$msg = get_error();	
			if (!$msg)
				$msg = get_error_string($status);
		}	
	
	$status = intval($status);
	
	//返回结果
	$res = array('status'=>$status, 'msg'=>$msg, 'data'=>$data);
	
	//
	//if ($status < 0)
	//	header("HTTP/1.0 400 Error");
	
	//跨站请求
	$cf = get_config();
	if ($cf['xss_access']) {//允许		   
		header("Access-Control-Allow-Credentials: true");
		//header("Access-Control-Allow-Origin: http://localhost:8080");
		header("Access-Control-Allow-Origin: *");
		header("Access-Control-Allow-Methods: OPTIONS,GET,POST");
		header("Access-Control-Allow-Headers: x-requested-with,content-type");
	}
	
	
	CJson::encodedPrint($res);
	exit;
}


function showStatusForTV($status, $root = array())
{
	rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "status=$status");
	if ($status === false)
		$status = 404;
	else if ($status === true)
		$status = 200;
	
	$status = intval($status);
	if (isset($root['data'])) {
		$nr = count($root['data']);	
	} else {
		$nr = 0;	
		$root['data'] = array();
	}
	
	$root['status'] = $status;
	$root['code'] = $status;
	$root['totalCount'] = $nr;
	
	CJson::encodedPrint($root);
	exit;
}


function get_accesskey()
{
	if (function_exists('rkey_get_accesskey')) {
		$accesskey = rkey_get_accesskey();
	} else {
		$accesskey = md5('s.x.w.a.r.e');		
	}
	return $accesskey;	
}

function get_hashaccesskey($hash='')
{
	$accesskey = get_accesskey();
	$accesskey = md5($hash.$accesskey);
	return $accesskey;	
}


/** 加载 config/bg目录下所有图片 */
function loadgb()
{
	$fdb = array();
	
	$files  = s_readdir(RPATH_DATA.DS."bg", "files");	
	if ($files) {
		foreach ($files as $key => $value) {
			$extname = s_fileext($value);
			if (in_array($extname,array('jpg','jpeg','png','gif'))) {
				$fdb[] = array('name' => $value );
			}
		}
	}
	return $fdb;
}

function sort_tree_data(&$odb, $tdb, $pid=0)
{
	if ($tdb == null) 
		return;
	
	foreach ($tdb as $key=>$v)
	{
		if ($v['pid'] != $pid)
		{
			continue;
		}
		
		$odb[$v['id']] = $v;
		
		sort_tree_data($odb, $tdb, $v['id']);		
	}
}



function nformat_percent($val, $nr=2)
{
	$d = round($val*100, $nr);
	$d = number_format($d, $nr, '.', '');
	return $d.'%';
}

function nformat_percentpoint($val, $nr=2)
{
	
	$d = round($val*100, $nr);
	return $d;
}

function nformat_money($val, $nr=4)
{
	$d = number_format($val, $nr);
	return $d;
}

//number_format ( float $number , int $decimals = 0 , string $dec_point = "." , string $thousands_sep = "," ) 

function nformat_moneyv($val, $nr=4)
{
	$d = number_format($val, $nr, '.', '');
	return $d;
}

function nformat_moneyv4($val)
{
	return nformat_moneyv($val, 4);
}

function nformat_moneyv2($val)
{
	return nformat_moneyv($val, 2);
}


function nformat_money2($val)
{
	return nformat_money($val, 2);
}

function nformat_money3($val)
{
	return nformat_money($val, 3);
}

function nformat_moneyW($val, $nr=4)
{
	//$d = round($val, $nr);
	$val /= 10000.0;
	$d = number_format($val, $nr);
	return $d;
}

function nformat_moneyvW($val, $nr=4)
{
	$val /= 10000.0;
	$d = nformat_moneyv($val, $nr);
	return $d;
}



/**
* 将数值金额转换为中文大写金额
* @param $amount float 金额(分)
* @param $type  int   补整类型,0:到角补整;1:到元补整
* @return mixed 中文大写金额
*/
function nformat_moneyRMB($amount, $type = 1)
{
	if ($amount == 0) {
		return "零元整";
	}
	
	if (strlen($amount) > 12) {
		return "不支持万亿及更高金额";
	}
	
	// 预定义中文转换的数组
	$digital = array('零', '壹', '贰', '叁', '肆', '伍', '陆', '柒', '捌', '玖');
	// 预定义单位转换的数组
	$position = array('仟', '佰', '拾', '亿', '仟', '佰', '拾', '万', '仟', '佰', '拾', '元');
	
	// 将金额的数值字符串拆分成数组
	$amountArr = explode('.', $amount);
	
	// 将整数位的数值字符串拆分成数组
	$integerArr = str_split($amountArr[0], 1);
	// 将整数部分替换成大写汉字
	$result = '人民币';
	$integerArrLength = count($integerArr);
	$positionLength = count($position);
	for ($i=0; $i<$integerArrLength; $i++){
		$result = $result . $digital[$integerArr[$i]]. $position[$positionLength - $integerArrLength + $i];
	}
	
	// 如果小数位也要转换
	$decval = $amountArr[1];
	if ($type == 1 && $decval) {
		// 将小数位的数值字符串拆分成数组
		$decimalArr = str_split($decval, 1);
		// 将小数部分替换成大写汉字
		$result = $result . $digital[$decimalArr[0]] . '角' . $digital[$decimalArr[1]] . '分';
	} else {
		$result = $result . '整';
	}
	
	return $result;
}


function nformat_price($val, $nr=2)
{
	return round($val, $nr);
}


/**
 * nformat_money_reserve 格式化货币
 *
 * @param mixed $val 输入以','分隔的货币值，eg : 2,1000
 * @return mixed 返回去除','数值
 *
 */
function nformat_money_reserve($val)
{
	$res = floatval(str_replace(',', '', $val));
	return $res;
}

function nformat_f2y($val, $nr=2)
{
	
	$d = round($val/100.00, $nr);
	return $d;
}



function initActiveTab($nr, $force_active_id=-1)
{
	if ($force_active_id < 0) {
		$active_table_id = isset($_COOKIE['atid'])? intval($_COOKIE['atid']):0;
		if (isset($_REQUEST['atid'])) {
			$active_table_id = intval($_REQUEST['atid']);
		}
	}		
	else 
		$active_table_id = $force_active_id;
	
	if ($active_table_id >= $nr || $active_table_id <0)
		$active_table_id = 0;
	
	
	
	$navtabs = array();
	for($id=0; $id<$nr; $id++) {
		$v = array();
		
		$v['id'] = $id;
		$v['title'] = 'tab'.$id;
		if ($active_table_id == $id) {
			$v['active'] = 'active';
			$v['in'] = 'active in';				
		} else {
			$v['active'] = '';
			$v['in'] = '';		
		}
		
		$navtabs[$id] = $v;
	}	
	return $navtabs;
}

function fencrypt($infile, $outfile='')
{
	if (function_exists('rkey_fencrypt')) {
		if (!rkey_fencrypt($infile, $outfile)) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call rkey_fencrypt!");
		}
		return true;
	} else {
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "no rkey_fencrypt!");
		return false;
	}
}

function file_data_fencrypt($data)
{
	$tmpfile = RPATH_CACHE.DS."__tmp.data";
	$res = s_write($tmpfile, $data);
	if (!$res) {
		rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call s_write failed!");
		return false;
	}
	$res = fencrypt($tmpfile);
	if (!$res) {
		rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call fencrypt failed!");
		return false;
	}
	
	$data = s_read($tmpfile);
	if (!$data) {
		rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call s_read failed!");
		return false;
	}
	unlink($tmpfile);
	
	return $data;
}


function fdecrypt($infile, $outfile='')
{
	if (function_exists('rkey_fdecrypt')) {
		if (!rkey_fdecrypt($infile, $outfile)) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call rkey_fdecrypt failed! infile=$infile");
		}
		return true;
	} else {
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "no rkey_fdecrypt!");
		return false;
	}
}


function file_data_fdecrypt($data)
{
	$tmpfile = RPATH_CACHE.DS."__tmp.data";
	$res = s_write($tmpfile, $data);
	if (!$res) {
		rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call s_write failed!");
		return false;
	}
	$res = fdecrypt($tmpfile);
	if (!$res) {
		rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call fdecrypt failed!");
		return false;
	}
	
	$data = s_read($tmpfile);
	if (!$data) {
		rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call s_read failed!");
		return false;
	}
	unlink($tmpfile);
	
	return $data;
}



function fdecrypt_get_content($infile, $outfile='')
{
	if (function_exists('rkey_fdecrypt_get_content')) {
		if (!($data = rkey_fdecrypt_get_content($infile))) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call rkey_fdecrypt_get_content failed!");
		}
		return $data;
	} else {
		rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no rkey_fdecrypt_get_content!");
		return false;		
	}
}



function fencode($fname)
{
	if (function_exists('rkey_fencode')) {
		if (!rkey_fencode($fname)) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "call rkey_fencode!");
		}
		return true;
	} else {
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "no rkey_fencode!");
		return false;
	}
}

function encryptPassword($pass)
{
	$pass = trim($pass);		
	if (function_exists('rkey_encryptpassword')) {
		if (!($epass = rkey_encryptpassword($pass))) {
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "call rkey_encryptpassword!");
		}
		return true;
	} else {
		$salt="3.N.W.A.R.E";
		$epass = md5($salt.md5($pass.$salt));		
	}
	return $epass;
}

function get_guid()
{
	if (function_exists('rkey_get_guid')) {
		$guid = rkey_get_guid();
	} else {
		$guid = "066A524C-8CC5-D437-0A5D-E07C000FDBD2";
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "WARNING: no rkey_get_guid");		
	}
	return $guid;
}


function get_catalog()
{
	$m = Factory::GetModel('catalog');
	$res =  $m->getCatalog();
	
	return $res;
}

function get_upload_max_filesize()
{
	$uploadmaxsize = ini_get('upload_max_filesize'); // "200M" 
	return nformat_get_human_file_size($uploadmaxsize);
}

function get_upload_tmpfiles()
{	
	//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "IN");		
	$fdb = array();	
	foreach ($_FILES as $key => $v) {		
		if (is_array($v['name'])) {
			
			$nr = count($v['name']);
			
			for($i=0; $i<$nr; $i++) {
				$params = array();
				
				$params['name'] = $v['name'][$i];
				$params['type'] = $v['type'][$i];
				$params['tmp_name'] = $v['tmp_name'][$i];
				$params['error'] = $v['error'][$i];
				$params['size'] = $v['size'][$i];
				$params['need_size'] = $params['size'];
				$fdb[] = $params;
			}
		} else {
			$params = $v;		
			$fdb[] = $params;
		}
	}
	//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, $fdb);
	//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "OUT");
	return $fdb;
}



function is_install($appname)
{
	$apps = Factory::GetApps();
	return isset($apps[$appname]);
}

function get_video_info($file) 
{
	$file = str_replace(DS, '/', $file);
	if (!file_exists($file)){
		rlog(RC_LOG_ERROR, __FILE__, __LINE__, "no file '$file'!");
		return false;	
	}
	
	ob_start();	
	passthru(sprintf('ffprobe -of json -show_format -show_streams "%s" ', $file));
	$info = ob_get_contents();
	ob_end_clean();
	
	$vinfo = json_decode($info, true);
	
	if (!$vinfo) {
		rlog(RC_LOG_ERROR, __FILE__, __LINE__, "invalid json!info=".$info);
		return false;
	}
	
	//$ret['width'] = $a[0];
	//$ret['height'] = $a[1];
	
	$vinfo['width'] = $vinfo['streams'][0]['width'];
	$vinfo['height'] = $vinfo['streams'][0]['height'];
	$vinfo['codec_name'] = $vinfo['streams'][0]['codec_name'];
	
	$vinfo['start_time'] = $vinfo['format']['start_time'];
	$vinfo['duration'] = $vinfo['format']['duration'];
	$vinfo['size'] = $vinfo['format']['size'];
	$vinfo['bit_rate'] = $vinfo['format']['bit_rate'];
	
	//rlog($vinfo);
	
	return $vinfo;	
}

function is_h5mp4($videofile)
{
	$vinfo = get_video_info($videofile);
	if (!$vinfo) {
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "Unknown video info!");
		return true;
	}
	
	$nb_streams = $vinfo['format']['nb_streams'];
	if ($nb_streams <= 0)
		return false;
	
	$format_name = $vinfo['format']['format_name'];
	$codec_name = $vinfo['streams'][0]['codec_name'];
	
	//format_name
	//格式1：string(23) "mov,mp4,m4a,3gp,3g2,mj2"	
	//格式2：string(13) "matroska,webm"
	
	//codec_name
	//编码格式1： string(4) "h264"
	//编码格式2： string(4) "mpeg4"
	
	if ($codec_name != 'h264') 
		return false;
	
	if (!strstr($format_name, 'mp4')) 
		return false;
	
	
	return true;
}


function parseEthinfo2($ethinfo)
{
	//var_dump($ethinfo); exit;
	/*
	array(22) {
	 [0]=>
	 string(487) "ens38     Link encap:Ethernet  HWaddr 00:0c:29:bd:45:5a  
	         inet addr:192.168.10.220  Bcast:192.168.10.255  Mask:255.255.255.0
	         inet6 addr: fe80::20c:29ff:febd:455a/64 Scope:Link
	         UP BROADCAST RUNNING MULTICAST  MTU:1500  Metric:1
	         RX packets:98931 errors:0 dropped:0 overruns:0 frame:0
	         TX packets:14754 errors:0 dropped:0 overruns:0 carrier:0
	         collisions:0 txqueuelen:1000 
	         RX bytes:137184173 (137.1 MB)  TX bytes:10759576"
	 [1]=>
	 string(5) "ens38"
	 [2]=>
	 string(17) "00:0c:29:bd:45:5a"
	 [3]=>
	 string(3) "45:"
	 [4]=>
	 string(14) "192.168.10.220"
	 [5]=>
	 string(13) "255.255.255.0"
	 [6]=>
	 string(50) "inet6 addr: fe80::20c:29ff:febd:455a/64 Scope:Link"
	 [7]=>
	 string(32) "UP BROADCAST RUNNING MULTICAST  "
	 [8]=>
	 string(5) "98931"
	 [9]=>
	 string(1) "0"
	 [10]=>
	 string(1) "0"
	 [11]=>
	 string(1) "0"
	 [12]=>
	 string(1) "0"
	 [13]=>
	 string(5) "14754"
	 [14]=>
	 string(1) "0"
	 [15]=>
	 string(1) "0"
	 [16]=>
	 string(1) "0"
	 [17]=>
	 string(1) "0"
	 [18]=>
	 string(1) "0"
	 [19]=>
	 string(4) "1000"
	 [20]=>
	 string(9) "137184173"
	 [21]=>
	 string(8) "10759576"
	}
	*/
	
	
	//$pattern = '#(\w+)\s+Link\s+encap:\w+\s+HWaddr\s+(([A-Fa-f0-9]{2}:){5}[A-Fa-f0-9]{2})\s+inet\s+addr:(\d{1,3}.\d{1,3}.\d{1,3}.\d{1,3})\s+.*mask:(\d{1,3}.\d{1,3}.\d{1,3}.\d{1,3})\s+(.*)\s+(.*)MTU:\d+\s+Metric:\d+\s+RX\s+packets:(\d+)\s+errors:(\d+)\s+dropped:(\d+)\s+overruns:(\d+)\s+frame:(\d+)\s+TX\s+packets:(\d+)\s+errors:(\d+)\s+dropped:(\d+)\s+overruns:(\d+)\s+carrier:(\d+)\s+collisions:(\d+)\s+txqueuelen:(\d+)\s+RX\s+bytes:(\d+)\s+.*TX\s+bytes:(\d+)#im';
	$pattern = '#(\w+)\s+Link\s+encap:\w+\s+HWaddr\s+(([A-Fa-f0-9]{2}:){5}[A-Fa-f0-9]{2})\s+.*MTU:\d+\s+Metric:\d+\s+RX\s+packets:(\d+)\s+errors:(\d+)\s+dropped:(\d+)\s+overruns:(\d+)\s+frame:(\d+)\s+TX\s+packets:(\d+)\s+errors:(\d+)\s+dropped:(\d+)\s+overruns:(\d+)\s+carrier:(\d+)\s+collisions:(\d+)\s+txqueuelen:(\d+)\s+RX\s+bytes:(\d+)\s+.*TX\s+bytes:(\d+)#im';
	if (preg_match($pattern, $ethinfo, $matches)) {
		//var_dump($matches);
		
		$nif = array();
		
		$name = $matches[1];
		
		$nif['name'] = $name;		
		$nif['hwaddr'] = $matches[2];
		$nif['ip'] = $matches[4];
		$nif['netmask'] = $matches[5];
		$nif['status'] = 0;
		$nif['link'] = 0;
		
		
		//status
		if (strstr($matches[7], "UP")) {
			$nif['status'] = 1;
		}
		//link
		if (strstr($matches[7], "RUNNING")) {
			$nif['link'] = 1;
		}
		
		//RX packets		
		$nif['rx_packets'] = $matches[8];
		$nif['rx_errors'] = $matches[9];
		$nif['rx_dropped'] = $matches[10];
		$nif['rx_overruns'] = $matches[11];
		$nif['rx_frame'] = $matches[12];
		
		
		$nif['tx_packets'] = $matches[13];
		$nif['tx_errors'] = $matches[14];
		$nif['tx_dropped'] = $matches[15];
		$nif['tx_overruns'] = $matches[16];
		$nif['tx_carrier'] = $matches[17];
		
		$nif['collisions'] = $matches[18];
		$nif['txqueuelen'] = $matches[19];
		$nif['rx_bytes'] = $matches[20];
		$nif['tx_bytes'] = $matches[21];
	}
	
	return $nif; 
}


function parseEthinfoLine($line, &$nif)
{
	//var_dump($line);
	$udb = array();
	$tdb = explode(" ", $line);	
	foreach ($tdb as $key=>$v) {
		$v = trim($v);
		if ($v === '') 
			continue;
		$udb[] = $v;
	}
	
	$nr = count($udb);	
	
	for($i=0; $i<$nr; $i++) {
		$val = $udb[$i];
		//var_dump($val);
		switch ($val) {	
			//ens35:2   Link encap:Ethernet  HWaddr 00:0c:29:68:f2:aa 
			case 'Link': //"eno4      Link encap:Ethernet  HWaddr 34:73:79:18:33:06"
				$name = $udb[$i-1];
				if (($pos = strpos($name, ':')) !== false) //子接口
					$name = substr($name, 0, $pos);
				
				$nif['name'] = $name;				
				//encap:Ethernet
				$nif['type'] = substr($udb[++$i], 6);
				break;
			case 'ether': //ether 00:0c:29:5b:cd:8a  txqueuelen 1000  (Ethernet)
			case 'HWaddr':
				$nif['hwaddr'] = $udb[++$i];
				break;
			case 'inet':
				/*
				"inet addr:28.69.92.206  Bcast:28.69.92.255  Mask:255.255.255.0"
				inet"
				"addr:28.69.92.206"
				"Bcast:28.69.92.255"
				"Mask:255.255.255.0"*/
				//inet 192.168.0.152  netmask 255.255.255.0  broadcast 192.168.0.255
				
				if (!isset($nif['inet']))
					$nif['inet'] = array();
				
				$ip = array();
				if (strstr($udb[$i+1], 'addr:')) {
					$ip['ip'] = substr($udb[$i+1], 5);
					$ip['netmask'] =substr($udb[$i+3], 5);
					$i += 3;
				} else {
					$ip['ip'] = $udb[$i+1];
					$ip['netmask'] =$udb[$i+3];
					$i += 5;	
				}				
				$nif['inet'][] = $ip;
				
				break;
			case 'inet6':
				/*
				 inet6 addr: fe80::20c:29ff:fe68:f2aa/64 Scope:Link
				 inet6 addr: 240e:360:95c:c101:20c:29ff:fe68:f2aa/64 Scope:Global
				*/
				/*
				inet6 240e:360:95c:c101::1004  prefixlen 128  scopeid 0x0<global>
				inet6 240e:360:95c:c101:20c:29ff:fe5b:cd80  prefixlen 64  scopeid 0x0<global>
				inet6 fe80::20c:29ff:fe5b:cd80  prefixlen 64  scopeid 0x20<link>
				*/
				if (!isset($nif['inet6']))
					$nif['inet6'] = array();
				
				$ip = array();
				if (strstr($udb[$i+1], 'addr:')) {
					$ip['ip'] = $udb[$i+2];
					$i += 3;
				} else {
					$ip['ip'] = $udb[$i+1];
					$ip['prefixlen'] =$udb[$i+3];
					$i += 5;	
				}				
				$nif['inet6'][] = $ip;
				
				break;
			case 'UP':
				/*
				 "UP BROADCAST RUNNING MULTICAST  MTU:1500  Metric:1"
				"UP"
				"BROADCAST"
				"RUNNING"
				"MULTICAST"
				"MTU:1500"
				"Metric:1"
				*/
				$nif['status'] = 1;
				break;
			case 'BROADCAST':
				break;
			case 'RUNNING':
				$nif['link'] = 1;
				break;
			case 'MULTICAST':
				break;
			case 'RX':
				
				/*
				 "RX packets:237714 errors:0 dropped:0 overruns:0 frame:0"
				"RX"
				 "packets:237714"
				"errors:0"
				"dropped:0"
				 "overruns:0"
				"frame:0"
							
					"RX bytes:346664808 (346.6 MB)  TX bytes:10115509 (10.1 MB)"
					RX"
					"bytes:346664808"
					(346.6"
					MB)"
					
				*/
				
				/*
				ether 00:0c:29:5b:cd:80  txqueuelen 1000  (Ethernet)
					RX packets 20957  bytes 1428066 (1.4 MB)
					RX errors 0  dropped 0  overruns 0  frame 0
					TX packets 1186  bytes 80346 (80.3 KB)
					TX errors 0  dropped 0 overruns 0  carrier 0  collisions 0
				*/
				
				if (strstr($udb[$i+1],"packets:")) {
					list($name, $sz) = explode(':', $udb[$i+1]);
					if ($name == 'packets') {
						$nif['rx_packets'] = $sz;
						for ($j=0; $j<4; $j++) {
							list($name, $sz) = explode(':', $udb[$i+2+$j]);
							$nif['rx_'.$name] = $sz;
						}
						$i += 4;
					} else {
						$nif['rx_bytes'] =$sz;
						$i += 1;
					}
				} else if (strstr($udb[$i+1],"bytes:")) { //RX bytes:16465911 (16.4 MB)  TX bytes:16465911 (16.4 MB)
						list($name, $sz) = explode(':', $udb[$i+1]);
						$nif['rx_bytes'] =$sz;
						$i += 1;					
					}else {
						if ($udb[$i+1] == "packets") {
							$nif['rx_packets'] = $udb[$i+2];
							$nif['rx_bytes'] = $udb[$i+4];
							$i += 4;
						} else if ($udb[$i+1] == "errors") {
								$nif['rx_errors'] = $udb[$i+2];
								$nif['rx_dropped'] = $udb[$i+4];
								$nif['rx_overruns'] = $udb[$i+6];
								$nif['rx_frame'] = $udb[$i+8];
								
								$i += 8; 
							}
					}
				
				break;
			case 'TX':
				
				if (strstr($udb[$i+1],"packets:")) {
					list($name, $sz) = explode(':', $udb[$i+1]);
					if ($name == 'packets') {
						$nif['tx_packets'] = $sz;
						for ($j=0; $j<4; $j++) {
							list($name, $sz) = explode(':', $udb[$i+2+$j]);
							$nif['tx_'.$name] = $sz;
						}	
						$i += 4;			
					} else {
						$nif['tx_bytes'] =$sz;
						$i += 1;
					}
				}  else if (strstr($udb[$i+1],"bytes:")) { //RX bytes:16465911 (16.4 MB)  TX bytes:16465911 (16.4 MB)
						list($name, $sz) = explode(':', $udb[$i+1]);
						$nif['tx_bytes'] =$sz;
						$i += 1;					
					} else {
						if ($udb[$i+1] == "packets") {
							$nif['tx_packets'] = $udb[$i+2];
							$nif['tx_bytes'] = $udb[$i+4];					
							$i += 4;
						} else if ($udb[$i+1] == "errors") {
								$nif['tx_errors'] = $udb[$i+2];
								$nif['tx_dropped'] = $udb[$i+4];
								$nif['tx_overruns'] = $udb[$i+6];
								$nif['tx_carrier'] = $udb[$i+8];
								$nif['tx_collisions'] = $udb[$i+10];							
								$i += 10; 
							}
					}
				
				break;
			default:
				//ens33: flags=4163<UP,BROADCAST,RUNNING,MULTICAST>  mtu 1500
				//ens33:1: flags=4163<UP,BROADCAST,RUNNING,MULTICAST>  mtu 1500
				if ($i == 0 && (strstr($udb[$i+1],"Link") || strstr($udb[$i+1],"flags"))) {
					$pos = strpos($val, ':');
					if ($pos !== false ) {
						$nif['name'] = substr($val, 0, $pos);
						//var_dump($nif['name']);
					}				
				}
				if (strstr($val,"flags")) {//flags=4163<UP,BROADCAST,RUNNING,MULTICAST>
					$pos = strpos($val, '<');
					if ($pos !== false) {
						$flagsnames = substr($val, $pos+1, -1);
						$fndb = explode(',', $flagsnames);
						foreach ($fndb as $k2=>$v2) {
							switch ($v2) {
								case 'UP':
									$nif['status'] = 1;
									break;
								case 'RUNNING':
									$nif['link'] = 1;
									break;
								default:
									break;
								
							}
						}
					} 					
				}
				
				
				break;
		}
	}
}

function parseEthinfo($ethinfo, &$ifdb=array())
{
	$nif = array();
	$nif['status'] = 0;
	$nif['link'] = 0;
	
	$lines = explode("\n", $ethinfo);
	foreach($lines as $key=>$v) {
		$line = trim($v);
		if (!$line)
			continue;
		parseEthinfoLine($line, $nif);
	}
	
	if (!isset($ifdb[$nif['name']])) {
		$ifdb[$nif['name']] = $nif;
	} else {//多IP, eg: eth1:1
		foreach ($nif['inet'] as $key=>$v) 
			$ifdb[$nif['name']]['inet'][] = $v;			
	}
	return $nif;
}

/**
 * 全局配置显示
 *
 * @return mixed This is the return value description
 *
 */
function ifconfig($ifname='')
{
	$ipinfo = array();
	
	/*
	# ifconfig eth0
	eth0      Link encap:Ethernet  HWaddr 00:0C:29:A7:1C:F9  
	         inet addr:192.168.24.228  Bcast:192.168.24.255  Mask:255.255.255.0
	         inet6 addr: 2001:470:19:d8f:20c:29ff:fea7:1cf9/64 Scope:Global
	         inet6 addr: fe80::20c:29ff:fea7:1cf9/64 Scope:Link
	         UP BROADCAST RUNNING MULTICAST  MTU:1500  Metric:1
	         RX packets:6168 errors:0 dropped:0 overruns:0 frame:0
	         TX packets:110 errors:0 dropped:0 overruns:0 carrier:0
	         collisions:0 txqueuelen:1000 
	         RX bytes:492520 (480.9 KiB)  TX bytes:13841 (13.5 KiB)
	         Interrupt:18 Base address:0x2000
	*/
	$cmd = "ifconfig -a $name";
	$data = run_output($cmd, $return);
	if (is_windows()) {
		$data = <<<EOT
ens33     Link encap:Ethernet  HWaddr 00:0c:29:b3:57:a0  
          inet addr:192.168.75.133  Bcast:192.168.75.255  Mask:255.255.255.0
          inet6 addr: fe80::20c:29ff:feb3:57a0/64 Scope:Link
          UP BROADCAST RUNNING MULTICAST  MTU:1500  Metric:1
          RX packets:1226 errors:0 dropped:0 overruns:0 frame:0
          TX packets:670 errors:0 dropped:0 overruns:0 carrier:0
          collisions:0 txqueuelen:1000 
          RX bytes:1046545 (1.0 MB)  TX bytes:132596 (132.5 KB)

ens38     Link encap:Ethernet  HWaddr 00:0c:29:b3:57:aa  
          inet addr:192.168.10.238  Bcast:192.168.10.255  Mask:255.255.255.0
          inet6 addr: fe80::20c:29ff:feb3:57aa/64 Scope:Link
          UP BROADCAST RUNNING MULTICAST  MTU:1500  Metric:1
          RX packets:9479 errors:0 dropped:0 overruns:0 frame:0
          TX packets:10077 errors:0 dropped:0 overruns:0 carrier:0
          collisions:0 txqueuelen:1000 
          RX bytes:7222021 (7.2 MB)  TX bytes:8090473 (8.0 MB)

ens39     Link encap:Ethernet  HWaddr 00:0c:29:b3:57:b4  
          inet addr:192.168.2.238  Bcast:192.168.2.255  Mask:255.255.255.0
          BROADCAST MULTICAST  MTU:1500  Metric:1
          RX packets:97 errors:0 dropped:0 overruns:0 frame:0
          TX packets:146 errors:0 dropped:0 overruns:0 carrier:0
          collisions:0 txqueuelen:1000 
          RX bytes:8412 (8.4 KB)  TX bytes:24832 (24.8 KB)

ens39:0   Link encap:Ethernet  HWaddr 00:0c:29:b3:57:b4  
          inet addr:28.69.92.238  Bcast:28.69.92.255  Mask:255.255.255.0
          BROADCAST MULTICAST  MTU:1500  Metric:1

lo        Link encap:Local Loopback  
          inet addr:127.0.0.1  Mask:255.0.0.0
          inet6 addr: ::1/128 Scope:Host
          UP LOOPBACK RUNNING  MTU:65536  Metric:1
          RX packets:16239 errors:0 dropped:0 overruns:0 frame:0
          TX packets:16239 errors:0 dropped:0 overruns:0 carrier:0
          collisions:0 txqueuelen:1 
          RX bytes:4772558 (4.7 MB)  TX bytes:4772558 (4.7 MB)
EOT;
		
		
		$data = <<<EOT
eno1      Link encap:Ethernet  HWaddr 34:73:79:18:33:03  
          UP BROADCAST MULTICAST  MTU:1500  Metric:1
          RX packets:0 errors:0 dropped:0 overruns:0 frame:0
          TX packets:0 errors:0 dropped:0 overruns:0 carrier:0
          collisions:0 txqueuelen:1000 
          RX bytes:0 (0.0 B)  TX bytes:0 (0.0 B)

eno2      Link encap:Ethernet  HWaddr 34:73:79:18:33:04  
          UP BROADCAST MULTICAST  MTU:1500  Metric:1
          RX packets:0 errors:0 dropped:0 overruns:0 frame:0
          TX packets:0 errors:0 dropped:0 overruns:0 carrier:0
          collisions:0 txqueuelen:1000 
          RX bytes:0 (0.0 B)  TX bytes:0 (0.0 B)

eno3      Link encap:Ethernet  HWaddr 34:73:79:18:33:05  
          inet addr:28.69.92.206  Bcast:28.69.92.255  Mask:255.255.255.0
          inet6 addr: fe80::c920:7607:283e:887c/64 Scope:Link
          UP BROADCAST RUNNING MULTICAST  MTU:1500  Metric:1
          RX packets:237714 errors:0 dropped:0 overruns:0 frame:0
          TX packets:44511 errors:0 dropped:0 overruns:0 carrier:0
          collisions:0 txqueuelen:1000 
          RX bytes:346664808 (346.6 MB)  TX bytes:10115509 (10.1 MB)

eno4      Link encap:Ethernet  HWaddr 34:73:79:18:33:06  
          UP BROADCAST MULTICAST  MTU:1500  Metric:1
          RX packets:0 errors:0 dropped:0 overruns:0 frame:0
          TX packets:0 errors:0 dropped:0 overruns:0 carrier:0
          collisions:0 txqueuelen:1000 
          RX bytes:0 (0.0 B)  TX bytes:0 (0.0 B)
		
lo        Link encap:Local Loopback  
          inet addr:127.0.0.1  Mask:255.0.0.0
          inet6 addr: ::1/128 Scope:Host
          UP LOOPBACK RUNNING  MTU:65536  Metric:1
          RX packets:1745 errors:0 dropped:0 overruns:0 frame:0
          TX packets:1745 errors:0 dropped:0 overruns:0 carrier:0
          collisions:0 txqueuelen:1000 
          RX bytes:1445822 (1.4 MB)  TX bytes:1445822 (1.4 MB)


EOT;
		
		$data = <<<EOT
ens33: flags=4163<UP,BROADCAST,RUNNING,MULTICAST>  mtu 1500
        inet 192.168.0.152  netmask 255.255.255.0  broadcast 192.168.0.255
        inet6 240e:360:95c:c101::1004  prefixlen 128  scopeid 0x0<global>
        inet6 240e:360:95c:c101:20c:29ff:fe5b:cd80  prefixlen 64  scopeid 0x0<global>
        inet6 fe80::20c:29ff:fe5b:cd80  prefixlen 64  scopeid 0x20<link>
        ether 00:0c:29:5b:cd:80  txqueuelen 1000  (Ethernet)
        RX packets 2788  bytes 186939 (186.9 KB)
        RX errors 0  dropped 0  overruns 0  frame 0
        TX packets 224  bytes 15974 (15.9 KB)
        TX errors 0  dropped 0 overruns 0  carrier 0  collisions 0

ens33:1: flags=4163<UP,BROADCAST,RUNNING,MULTICAST>  mtu 1500
        inet 28.69.92.203  netmask 255.0.0.0  broadcast 28.255.255.255
        ether 00:0c:29:5b:cd:80  txqueuelen 1000  (Ethernet)

ens38: flags=4163<UP,BROADCAST,RUNNING,MULTICAST>  mtu 1500
        inet 192.168.10.203  netmask 255.255.255.0  broadcast 192.168.10.255
        inet6 fe80::20c:29ff:fe5b:cd8a  prefixlen 64  scopeid 0x20<link>
        ether 00:0c:29:5b:cd:8a  txqueuelen 1000  (Ethernet)
        RX packets 577  bytes 51506 (51.5 KB)
        RX errors 0  dropped 0  overruns 0  frame 0
        TX packets 516  bytes 75927 (75.9 KB)
        TX errors 0  dropped 0 overruns 0  carrier 0  collisions 0

ens39: flags=4098<BROADCAST,MULTICAST>  mtu 1500
        ether 00:0c:29:5b:cd:94  txqueuelen 1000  (Ethernet)
        RX packets 0  bytes 0 (0.0 B)
        RX errors 0  dropped 0  overruns 0  frame 0
        TX packets 0  bytes 0 (0.0 B)
        TX errors 0  dropped 0 overruns 0  carrier 0  collisions 0

lo: flags=73<UP,LOOPBACK,RUNNING>  mtu 65536
        inet 127.0.0.1  netmask 255.0.0.0
        inet6 ::1  prefixlen 128  scopeid 0x10<host>
        loop  txqueuelen 1000  (Local Loopback)
        RX packets 84  bytes 6352 (6.3 KB)
        RX errors 0  dropped 0  overruns 0  frame 0
        TX packets 84  bytes 6352 (6.3 KB)
        TX errors 0  dropped 0 overruns 0  carrier 0  collisions 0
EOT;
		
	}
	
	$ifdb = array();
	$ndb = explode("\n\n", $data);
	
	foreach($ndb as $key=>$v) {
		$ethinfo = trim($v);
		if (!$ethinfo)
			continue;
		
		parseEthinfo($ethinfo, $ifdb);		
	}
	ksort($ifdb);
	
	if (isset($ifdb[$ifname]))
		return $ifdb[$ifname];
	
	return $ifdb;	
}

function parseIpAddressLine($line, &$nif)
{
	$udb = array();
	
	//1: lo: <LOOPBACK,UP,LOWER_UP> mtu 65536 qdisc noqueue state UNKNOWN group default qlen 1
	$tdb = explode(" ", $line);
	foreach ($tdb as $key => $v) {
		$val = trim($v);
		$udb[] = $val;
	}
	$nr = count($tdb);
	
	$i=0;
	if ($udb[$i] == $nif['id']) {//第1行
		$i++;
		
		//name
		$name = rtrim($udb[$i++], ':');
		$nif['name'] = $name;
		
		//<LOOPBACK,UP,LOWER_UP>
		$flags = $udb[$i++];
		$flags = ltrim($flags, '<');
		$flags = rtrim($flags, '>');
		
		$fndb = explode(',', $flags);
		foreach ($fndb as $k2=>$v2) {
			switch ($v2) {
				case 'UP':
					$nif['status'] = 1;
					break;
				case 'LOOPBACK':
					$nif['LOOPBACK'] = 1;
					break;
				default:
					break;
				
			}
		}
	}
	
	
	for(;$i<$nr; $i++) {
		$val = $udb[$i];
		switch($val){
			case 'link/ether':
				$nif['hwaddr'] = $udb[++$i];
				break;
			case 'state':
				$state = $udb[++$i];//UNKNOWN|UP|DOWN
				$nif['link'] = ($state == 'UP' || ($nif['LOOPBACK'] == 1 && $state == 'UNKNOWN'))?1:0;
				break;
			case 'inet6':
			case 'inet':
				if (!isset($nif[$val]))
					$nif[$val]= array();
				list($ip, $netmask) = explode('/', $udb[++$i]);
				$nif[$val][] = array('ip'=>$ip, 'netmask'=>$netmask);					
				break;
			default:
				//var_dump($val);
				break;
		}
	}
}

/*
ip address
1: lo: <LOOPBACK,UP,LOWER_UP> mtu 65536 qdisc noqueue state UNKNOWN group default qlen 1
    link/loopback 00:00:00:00:00:00 brd 00:00:00:00:00:00
    inet 127.0.0.1/8 scope host lo
       valid_lft forever preferred_lft forever
    inet6 ::1/128 scope host 
       valid_lft forever preferred_lft forever
2: ens33: <BROADCAST,MULTICAST,UP,LOWER_UP> mtu 1500 qdisc pfifo_fast state UP group default qlen 1000
    link/ether 00:0c:29:8a:82:ca brd ff:ff:ff:ff:ff:ff
    inet 192.168.189.129/24 brd 192.168.189.255 scope global ens33
       valid_lft forever preferred_lft forever
    inet6 fe80::20c:29ff:fe8a:82ca/64 scope link 
       valid_lft forever preferred_lft forever
3: ens38: <BROADCAST,MULTICAST,UP,LOWER_UP> mtu 1500 qdisc pfifo_fast state UP group default qlen 1000
    link/ether 00:0c:29:8a:82:d4 brd ff:ff:ff:ff:ff:ff
    inet 192.168.10.249/24 brd 192.168.10.255 scope global ens38
       valid_lft forever preferred_lft forever
    inet6 fe80::20c:29ff:fe8a:82d4/64 scope link 
       valid_lft forever preferred_lft forever
4: ens39: <BROADCAST,MULTICAST,UP,LOWER_UP> mtu 1500 qdisc pfifo_fast state UP group default qlen 1000
    link/ether 00:0c:29:8a:82:de brd ff:ff:ff:ff:ff:ff
    inet 28.69.92.20/24 brd 28.69.92.255 scope global ens39
       valid_lft forever preferred_lft forever
    inet 192.168.0.20/24 brd 192.168.0.255 scope global ens39:2
       valid_lft forever preferred_lft forever
    inet 192.168.0.249/24 brd 192.168.0.255 scope global secondary ens39:3
       valid_lft forever preferred_lft forever
    inet6 240e:360:95c:c101:20c:29ff:fe8a:82de/64 scope global mngtmpaddr dynamic 
       valid_lft 85952sec preferred_lft 13952sec
    inet6 fe80::20c:29ff:fe8a:82de/64 scope link 
       valid_lft forever preferred_lft forever
root@erm:~# 
*/
function ip_address()
{
	$ipinfo = array();
	
	$cmd = "ip address";
	$data = run_output($cmd, $return);
	if (is_windows()) {
		$data = <<<EOT
1: lo: <LOOPBACK,UP,LOWER_UP> mtu 65536 qdisc noqueue state UNKNOWN group default qlen 1
    link/loopback 00:00:00:00:00:00 brd 00:00:00:00:00:00
    inet 127.0.0.1/8 scope host lo
       valid_lft forever preferred_lft forever
    inet6 ::1/128 scope host 
       valid_lft forever preferred_lft forever
2: ens33: <BROADCAST,MULTICAST,UP,LOWER_UP> mtu 1500 qdisc pfifo_fast state UP group default qlen 1000
    link/ether 00:0c:29:8a:82:ca brd ff:ff:ff:ff:ff:ff
    inet 192.168.189.129/24 brd 192.168.189.255 scope global ens33
       valid_lft forever preferred_lft forever
    inet6 fe80::20c:29ff:fe8a:82ca/64 scope link 
       valid_lft forever preferred_lft forever
3: ens38: <BROADCAST,MULTICAST,UP,LOWER_UP> mtu 1500 qdisc pfifo_fast state UP group default qlen 1000
    link/ether 00:0c:29:8a:82:d4 brd ff:ff:ff:ff:ff:ff
    inet 192.168.10.249/24 brd 192.168.10.255 scope global ens38
       valid_lft forever preferred_lft forever
    inet6 fe80::20c:29ff:fe8a:82d4/64 scope link 
       valid_lft forever preferred_lft forever
4: ens39: <BROADCAST,MULTICAST,UP,LOWER_UP> mtu 1500 qdisc pfifo_fast state UP group default qlen 1000
    link/ether 00:0c:29:8a:82:de brd ff:ff:ff:ff:ff:ff
    inet 28.69.92.20/24 brd 28.69.92.255 scope global ens39
       valid_lft forever preferred_lft forever
    inet 192.168.0.20/24 brd 192.168.0.255 scope global ens39:2
       valid_lft forever preferred_lft forever
    inet 192.168.0.249/24 brd 192.168.0.255 scope global secondary ens39:3
       valid_lft forever preferred_lft forever
    inet6 240e:360:95c:c101:20c:29ff:fe8a:82de/64 scope global mngtmpaddr dynamic 
       valid_lft 85952sec preferred_lft 13952sec
    inet6 fe80::20c:29ff:fe8a:82de/64 scope link 
       valid_lft forever preferred_lft forever
EOT;
		
	}
	
	$nr = 1;
	$id1 = $id2 = "$nr:";
	
	$ifdb = array();
	$ndb = explode("\n", $data);
	
	foreach($ndb as $key=>$v) {
		$v = trim($v);
		if (!$v)
			continue;
		
		if (is_start_with($v, $id2)) {
			$ifdb[$id2] = array('id'=>$id2,'status'=>0, 'link'=>0);
			$id1 = $id2;
			$nr ++;
			$id2 = "$nr:";
		} 
		
		parseIpAddressLine($v, $ifdb[$id1]);	
		
	}
	ksort($ifdb);
	
	if (isset($ifdb[$ifname]))
		return $ifdb[$ifname];
	
	return $ifdb;
}


function ifconfig2($ifname='')
{
	$ipdb = ip_address();
	$ifdb = ifconfig($ifname);
	
	foreach ($ipdb as $key => $v) {
		$name = $v['name'];
		if (isset($ifdb[$name])) {
			$ifdb[$name]['inet'] = $v['inet'];
			$ifdb[$name]['inet6'] = $v['inet6'];
		}
	}
	
	return $ifdb;
}


function createAliasDir($cfgfile, $uri, $dir)
{
	
	$data = "Alias $uri \"$dir\"\n";
	$data .= "<Directory \"$dir\">\n";
	$data .= "Options FollowSymLinks \n";
	$data .= "AddDefaultCharset UTF-8\n";
	$data .= "Order allow,deny\n";
	$data .= "Allow from all\n";
	$data .= "</Directory>\n";
	$data .= "\n";
	
	//$data .= "LoadModule h264_streaming_module modules/mod_h264_streaming.so\n";
	//$data .= "AddHandler h264-streaming.extensions .mp4\n";
	
	$data .= "\n\n";
	
	
	@s_write($cfgfile, $data);
	
	//重启 server
	sapi_restart_apache();
	
	return true;
}


function sm_set_config($params)
{
	$m = Factory::GetModel('sm_params');
	return $m->setParams($params);
}

function sm_get_config()
{
	$m = Factory::GetModel('sm_params');
	return $m->getParams();
}

function s_filesize($file) 
{
	$size = filesize($file);
	if ($size <= 0) {
		//https://www.php.net/filesize
		//WIN32 超2G filesize返回<0
		if (is_windows()) {
			exec('for %I in ("'.$file.'") do @echo %~zI', $output);
			$size = $output[0];
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "size=".$size);
			
			/*$a = fopen($file, 'r');
			fseek($a, 0, SEEK_END);
			$size = ftell($a);
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "size=".$size);
						
			fclose($a); */
		}
	}
	return $size;
} 


function curlGET($url, $params=array())
{
	
	$sd = curl_init();
	
	curl_setopt($sd, CURLOPT_URL, $url);
	
	$params[] = "Content-type: application/x-www-form-urlencoded";
	$user_agent = "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/33.0.1750.146 Safari/537.36";
	
	//rlog($params);
	//if (isset($params['referer']))
	//	curl_setopt($sd, CURLOPT_REFERER, $params['referer']);
	
	
	curl_setopt($sd, CURLOPT_HTTPHEADER, $params);	
	curl_setopt($sd, CURLOPT_USERAGENT, $user_agent);
	
	// 返回 response_header, 该选项非常重要,如果不为 true, 只会获得响应的正文
	curl_setopt($sd, CURLOPT_HEADER, false);	
	curl_setopt($sd, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($sd, CURLINFO_HEADER_OUT, true);
	// 是否不需要响应的正文,为了节省带宽及时间,在只需要响应头的情况下可以不要正文
	curl_setopt($sd, CURLOPT_NOBODY, false);
	curl_setopt($sd, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($sd, CURLOPT_SSL_VERIFYHOST, FALSE);
	$res = curl_exec($sd);
	
	curl_close($sd);
	
	//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "url=$url, res: $res");	
	
	return $res;
}



function curlPOST($url, $params=array(), $file=null, $json=false)
{
	
	$curl = curl_init();
	if ($file) 
		$params['file'] =  curl_file_create($file);
	
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl, CURLINFO_HEADER_OUT, true);
	curl_setopt($curl, CURLOPT_USERAGENT, "curl-rc");
	if (isset($params['ssid']))
		curl_setopt($curl, CURLOPT_HTTPHEADER, array("Cookie:ssid=".$params['ssid'])); //把ssid放到header中发送	
	
	curl_setopt($curl, CURLOPT_POST, true);//开启post
	//if ($params)
	//	@curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($params)); //Post
	
	if ($params) {
		if ($json) {
			curl_setopt($curl, CURLOPT_HTTPHEADER, array(
						'Content-Type: application/json',
						'Accept: application/json'
						));
			if (is_array($params)) {
				$params = json_encode($params);
			}
		} 
		
		@curl_setopt($curl, CURLOPT_POSTFIELDS, $params); //Post
	}
	
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
	$res = curl_exec($curl);
	curl_close($curl);
	
	rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "url=$url, res: $res");	
	
	return $res;
}
function curlJSON($url, $params=array())
{
	return curlPOST($url, $params, null, true);
}

//

function curlProxy($url, $params=array())
{
	$udb = parse_url($url);
	//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, $udb);	
	
	
	
	$host = $udb['host'];
	if (isset($udb['port']))
		$host .= ':'.$udb['port'];
	
	/*
	Array
	(
	   [scheme] => http
	   [host] => hostname
	   [user] => username
	   [pass] => password
	   [path] => /path
	   [query] => arg=value
	   [fragment] => anchor
	)
	*/
	
	
	$curl = curl_init();
	
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl, CURLOPT_HEADER, 1);
	curl_setopt($curl, CURLINFO_HEADER_OUT, 1);
	curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $_SERVER['REQUEST_METHOD']);
	
	$headers = array();
	foreach (getallheaders() as $name=> $value) {
		//hostname
		//[0] => Host: localhost
		//if ($name == 'Content-Type')
		//	continue;
		if ($name == 'Content-Length' ||$name == 'Content-Type' )
			continue;
		
		if ($name == 'Host') {
			$headers[] = "$name: $host";
		} else {
			$headers[] = "$name: $value";
		}
	}
	if ($_SERVER['REQUEST_METHOD'] == 'POST' || $_SERVER['REQUEST_METHOD'] == 'PUT') {
		$request_body = file_get_contents('php://input');
		//{"username":"13783338287927","password":"test666","remember":0}
		//$params = CJson::decode($request_body);
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, $request_body);	
		curl_setopt($curl, CURLOPT_POST, true);//开启post
		//curl_setopt($curl, CURLOPT_POSTFIELDS, $params);	
		curl_setopt($curl, CURLOPT_POSTFIELDS, $request_body);		
		//$headers[] = 'Content-Length: '.strlen($request_body);
	}
	curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
	
	
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
	
	$res = curl_exec($curl);
	
	rlog(RC_LOG_DEBUG, __FILE__, __LINE__, $headers, "url=$url, res: $res");	
	
	if ($res === false) {
		http_response_code(500);
		echo 'BAD Request';
		exit;
	}
	
	$header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
	
	$headers = substr($res, 0, $header_size);
	
	//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, $headers);	
	
	$body = substr($res, $header_size);
	//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, $body);	
	
	$response_header = array('content-type', 'cookie', 'set-cookie');	
	
	foreach (explode("\n", $headers) as $header) {
		$header = trim($header);
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, '$header='.$header);	
		//if ($header)
		//	header($header);
		
		$tdb = explode(':', $header, 2);
		$name = strtolower(trim($tdb[0]));
		if (in_array($name, $response_header)) {
			header($header);
		}
	}
	echo $body;	
	
	exit;
	
}


function ssl_pm_load($sslfile)
{
	$data = file_get_contents($sslfile);
	
	$mdb = array("-----BEGIN RSA PRIVATE KEY-----", "\n", "-----END RSA PRIVATE KEY-----");
	$ndb = array('', '', '');	
	$data = str_replace($mdb, $ndb, $data);
	
	$mdb = array("-----BEGIN PUBLIC KEY-----", "\n", "-----END PUBLIC KEY-----");
	$ndb = array('', '', '');	
	$data = str_replace($mdb, $ndb, $data);
	
	//rlog($data);
	return $data;	
}

function parseArgsParams($argv, &$ioparams=array())
{
	$nr = count($argv);
	$udb = array();
	for($i=1; $i<$nr; $i++) {
		switch($argv[$i]) {
			case '-c': 
				$cname = $argv[++$i];
				break;
			case '-t': 
				$tname = $argv[++$i];
				break;
			default:
				if (is_numeric($argv[$i]))
					$id = $argv[$i];
				elseif (!$tname)
					$tname = $argv[$i];
				break;
		}
	}
	
	if ($cname)
		$ioparams['cname'] = $cname;
	if ($tname)
		$ioparams['tname'] = $tname;
	if ($id)
		$ioparams['id'] = $id;
}


/**
 * parseVersionId 把version字串转换成整数 version_id
 *
 * @param mixed $version 版本，如：1.0.1
 * @return mixed This is the return value description
 *
 */
function parseVersionId($version, &$params=array())
{
	$vdb = explode('.', $version);	
	/*
	主(major)	字节7
	次(minor)	字节6
	补丁(patch)	字节5
	字节4
	*/
	$nr = count($vdb);
	
	$major = intval($vdb[0]);
	$minor = intval($vdb[1]);
	$patch = intval($vdb[2]);
	$svn =  ($nr > 3) ? $vdb[3]: 0;
	
	$id = 0;
	$id |= $major << 27;
	$id |= $minor << 22;
	$id |= $patch << 13;
	$id |= $svn;
	
	$params['major'] = $major;
	$params['minor'] = $minor;
	$params['patch'] = $patch;
	$params['svn'] = $svn;
	//rlog('version='.$version.',svn='.$svn.',id='.$id);
	$params['version_id'] = $id;
	
	return $params;
}

function compareAppVersion($ver1, $ver2)
{
	$v1 = parseVersionId($ver1);
	$v2 = parseVersionId($ver2);
	
	if ($v1['major'] > $v2['major']) {
		return 1;
	} elseif ($v1['minor'] > $v2['minor']) {
		return 2;
	} elseif ($v1['patch'] > $v2['patch']) {
		return 3;
	} else {
		return 0;
	}
}


/**
 * This is function getAppInfo
 *
 * @param mixed $name APP名称
 * @param mixed $type 类型，4-app, 5-tpl, 6-the, 默认:app, 
 * @return mixed This is the return value description
 *
 */
function getAppInfo($name, $type=4)
{
	$apppdir = RPATH_APPS;
	switch($type) {
		case 5:
			$apppdir = RPATH_TEMPLATES;
			break;
		case 6:
			$apppdir = RPATH_THEMES;
			break;
		default:
			break;
	}
	$appcfgfile = $apppdir.DS.$name.DS.'config.php';
	if (!file_exists($appcfgfile))
		return false;
	
	include_once $appcfgfile;
	return $appcfg;
}

function randnum($n=6)
{
	$strs = '1234567890';
	$res = '';
	$len = strlen($strs);
	for ($i=0; $i<$n; $i++) {
		if ($i === 0) {
			$idx = rand()%($len-1);
		} else {
			$idx = rand()%$len;
		}	
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, '$idx='.$idx);	
		$res .= $strs[$idx];
	}
	
	return $res;
}

function randstr($n=8)
{
	$strs = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789_!@#$%^&*';
	$res = '';
	$len = strlen($strs);
	for ($i=0; $i<$n; $i++) {
		$idx = rand()%$len;	
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, '$idx='.$idx);	
		$res .= $strs[$idx];
	}
	
	return $res;
}


function randName($n=8)
{
	$strs = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz_1234567890';
	$res = '';
	$len = strlen($strs);
	for ($i=0; $i<$n; $i++) {
		if ($i === 0) {
			$idx = rand()%($len-10);
		} else {
			$idx = rand()%$len;
		}		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, '$idx='.$idx);	
		$res .= $strs[$idx];
	}
	
	return $res;
}

function sign($key, $params)
{
	//对待签名参数数组排序
	ksort($params);
	reset($params);
	
	$string = '';
	foreach ($params as $k => $v) {
		if ('sign' !== $k && 'sign_type' !== $k) {
			$string .= $k . '=' . $v . '&';
		}
	}
	$string .= $key;
	
	//如果存在转义字符，那么去掉转义
	if (get_magic_quotes_gpc()) {
		$string = stripslashes($string);
	}
	
	return md5($string);
}

function enString($plaintext, $key, $iv = '', $aad = '')
{
	$cipher = 'aes-256-gcm';
	$ciphertext = openssl_encrypt($plaintext, $cipher, $key, OPENSSL_RAW_DATA, 
			$iv, $tag, $aad, 16);
	
	if (false === $ciphertext) {
		rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, 'Encrypting the input $plaintext failed, please checking your $key and $iv whether or nor correct.');
		return false;
	}
	$res = base64_encode($ciphertext . $tag);
	
	$res = urlencode($res);
	
	return $res;
}

function deString($ciphertext, $key, $iv = '', $aad = '')
{
	//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, 'in1', $ciphertext);
	
	//$ciphertext = urldecode($ciphertext);	
	//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, 'in2', $ciphertext);
	
	$ciphertext = base64_decode($ciphertext);
	
	
	$authTag = substr($ciphertext, $tailLength = 0 - 16);
	$tagLength = strlen($authTag);
	
	if ($tagLength > 16 || ($tagLength < 12 && $tagLength !== 8 && $tagLength !== 4)) {
		rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "The inputs  incomplete, the bytes length must be one of 16, 15, 14, 13, 12, 8 or 4.");
		return false;
	}
	$cipher = 'aes-256-gcm';
	$plaintext = openssl_decrypt(substr($ciphertext, 0, $tailLength), $cipher, $key, OPENSSL_RAW_DATA, 
			$iv, $authTag, $aad);
	
	if (false === $plaintext) {
		rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "Decrypting the input failed, please checking your key '$key' and iv '$iv' whether or nor correct.");
	}
	
	return $plaintext;
}


function enParams($params)
{
	$cf = get_config();
	$key = $cf['accesskey'];
	$iv = md5($cf['accesskey']);
	$aad = 's.x.w.a.r.e';
	
	$sign = sign($key, $params);
	$params['sign'] = $sign;
	
	$plaintext = serialize($params);
	
	
	return enString($plaintext, $key, $iv, $aad);
}

function deParams($ciphertext)
{
	$cf = get_config();
	$key = $cf['accesskey'];
	$iv = md5($cf['accesskey']);
	$aad = 's.x.w.a.r.e';
	
	$plaintext = deString($ciphertext, $key, $iv, $aad);
	if (!$plaintext) {
		rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, 'deString failed!');
		return false;
	}
	
	$params = unserialize($plaintext);
	
	$sign = sign($key, $params);
	if ($sign != $params['sign']) {
		rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, 'invalid sign');
		return false;
	}
	
	return $params;
}


function enSimpleString($data, $key='3.n.w.a.r.e')
{
	$iv = md5($key);
	$aad = 's.x.w.a.r.e';
	
	$res = enString($data, $key, $iv, $aad);
	
	return $res;
}


function deSimpleString($ciphertext, $key='3.n.w.a.r.e')
{
	$iv = md5($key);
	$aad = 's.x.w.a.r.e';
	
	
	$ciphertext = urldecode($ciphertext);	
	
	$plaintext = deString($ciphertext, $key, $iv, $aad);
	if (!$plaintext) {
		rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, 'deString failed!');
		return false;
	}
	
	return $plaintext;
}

function formatColorTitle($value, $title='')
{
	switch ($value) {
		default:
		case '0':
			$label = 'default';
			break;			
		case '1':
			$label = 'success';
			break;			
		case '2':
			$label = 'warning';
			break;			
		case '3':
			$label = 'danger';
			break;		
	}	
	return "<span class='label label-sm label-$label'>$title</span>";		
}

function genOID($id, $subsysid=1)
{
	$ts = time();
	return sprintf("%02d%10ld%06d", $subsysid, $ts, $id);
}

function is_version($val)
{
	$udb = explode('.', $val);
	$nr = count($udb);
	if ($nr >= 3) {
		if (is_numeric($udb[0]) 
				&& is_numeric($udb[1]) 
				&& is_numeric($udb[0]))
			return true;
	}
	return false;
}


function parseUname($uname)
{
	//Linux sxware.net 4.4.0-186-generic #216-Ubuntu SMP Wed Jul 1 05:34:05 UTC 2020 x86_64 x86_64 x86_64 GNU/Linux
	//Linux cde 2.6.32-431.el6.i686 #1 SMP Fri Nov 22 00:26:36 UTC 2013 i686 i686 i386 GNU/Linux
	
	$tdb = explode(' ', $uname);
	
	$nr = count($tdb);
	
	$tdb['name'] = $tdb[0]; //system name
	$tdb['hostname'] = $tdb[1]; 
	$tdb['kernel_version'] = $tdb[3]; 
	$tdb['arch'] = $tdb[$nr-2]; 
	$tdb['sysname'] = $tdb[$nr-1]; 
	
	return $tdb;
}


function s_hidestr($string, $start=0, $length=0, $re ='*')
{
	if (empty($string)) 
		return false;
	
	$strarr = array();
	$mb_strlen = mb_strlen($string);
	while ($mb_strlen) {
		$strarr[] = mb_substr($string, 0, 1, 'utf8');
		$string = mb_substr($string, 1, $mb_strlen, 'utf8');
		$mb_strlen = mb_strlen($string);
	}
	
	$strlen = count($strarr);
	$begin = $start >= 0?$start:($strlen - abs($start));
	$end = $last = $strlen - 1;
	
	if ($length > 0) {
		$end = $begin + $length - 1; 
	} elseif ($length < 0) {
		$end -= abs($length);
	}
	
	$res = '';
	for($i=0; $i<$begin; $i++) {
		$res .= $strarr[$i];
	}
	
	for($i=$begin; $i<=$end; $i++) {
		$res .= $re;
	}
	
	$end_start = $end+1;
	if ($last - $end > $start)
		$end_start = $last - $start;
	
	for ($i=$end_start; $i < $last; $i++) { 
		$res .= $strarr[$i];
	}
	
	
	return $res;
}


function cutstr($str, $nr=32)
{
	return substr($str, 0, $nr);
}

function equalf($a, $b, $n=6)
{
	return round($a - $b,$n) == 0;
}

function is_model($name)
{
	$res = false;
	$filename = RPATH_APPMODELS.DS.$name.'.php';
	if (file_exists($filename)) {//本地应用目录下的models优先
		$res = true;
	} else {				
		$modpathinfo = Factory::GetModelPathInfo($name);
		if ($modpathinfo)  {
			$filename = $modpathinfo['modpath'];
			$appname  = $modpathinfo['appname'];
			if (file_exists($filename)) {
				$res = true;					
			}
		} else {				
			$filename = RPATH_MODELS.DS.$name.'.php';
			if (file_exists($filename))
				$res = true;		
		}
	}
	return $res;
}