<?php

class CRequest extends CObject
{
	/**
	 * 请求方法
	 *
	 * @var mixed 
	 *
	 */
	protected $_method;
	
	/**
	 * URI
	 *
	 * @var mixed 
	 *
	 */
	protected $_uri;
	
	
	/**
	 * 应用程序调用者
	 *
	 * @var mixed 
	 *
	 */
	protected $_appname = null;
	/**
	 * web根目录参考点为 lib 所在目录的上级目录
	 *
	 * @var mixed 
	 *
	 */
	protected $_webroot_path;

	/**
	* the script name with out path, eg : 
	* SCRIPT_NAME = '/a1/index.php' 
	* $_basename = '/index.php'
	* 
	* @var mixed 
	*
	*/
	protected $_basename;
	
	/**
	 * 去掉文件名称后剩下部分
	 * 
	 * 如：basename = '/rc5/admin.php
	 * 则：baseroot = '/rc5
	 * 注：不含'/'
	 *
	 * @var mixed 
	 *
	 */
	protected $_baseroot;
	
	/**
	 * WEB相对根路径，格式如：/rc5,如果直接部署在wwwroot下，则 webroot= "" 
	 * 
	 * SCRIPT_NAME = '/rc5/index.php' 
	 * $_webroot = '/rc5'
	 */
	protected $_webroot;
	
	
	/**
	 * libs路径
	 * $_webroot.'/libs'
	 * @var mixed 
	 *
	 */
	protected $_libroot = null;
	
	/**
	 * 配置路径
	 */
	protected $_cfgbase = null;
	
	
	/**
	 * URI解析路径定位
	 *
	 * @var mixed 
	 *
	 */
	protected $_path = null;
	protected $_vpath = array();
	
	
	/**
	 * 浏览器相关信息
	 *
	 * @var mixed 
	 *
	 */
	protected $_schema;
	protected $_client;
	protected $_remote_addr;
	protected $_host;
	
	protected $_domain;
	protected $_port;
	protected $_is_ssl;
	protected $_useragent_browser;
	protected $_useragent_browser_ver;
	protected $_useragent_os;
	protected $_useragent_os_ver;
	protected $_uainfo;
	protected $_language;
	
	
	/**
	 * 格式如：http[s]://192.168.10.238/rc5
	 *
	 * @var mixed 
	 *
	 */
	protected $_weburl;
	
	protected $_uribase;
	
	public function __construct($options=array())
	{
		$this->_options = $options;
		$this->init();		
	}
	
	public function CRequest($options=array())
	{
		$this->__construct($options);		
	}
	
	static function &GetInstance($options=array())
	{
		static $instance;		
		if (!is_object($instance)) {
			$instance	= new CRequest($options);
		}		
		return $instance;
	}
	
	/**
		 * eg :
		 * SCRIPT_FILENAME = J:/dvlp/icloud/trunk/src/icloud/index.php
		 * WEB_PATH=J:/dvlp/icloud/trunk/src/icloud
		 * WEB_PATH=J:/dvlp/icloud/trunk/src/icloud/lib
				
		 * suburi = /index.php
		 * 
		 * [SCRIPT_URL] => /webdav/123
    [SCRIPT_URI] => http://192.168.10.108/webdav/123
    [HTTP_HOST] => 192.168.10.108
    [HTTP_USER_AGENT] => Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:99.0) Gecko/20100101 Firefox/99.0
    [HTTP_ACCEPT] => text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*;q=0.8
    [HTTP_ACCEPT_LANGUAGE] => zh-CN,zh;q=0.8,zh-TW;q=0.7,zh-HK;q=0.5,en-US;q=0.3,en;q=0.2
    [HTTP_ACCEPT_ENCODING] => gzip, deflate
    [HTTP_CONNECTION] => keep-alive
    [HTTP_COOKIE] => PHPSESSID=b31n1mlrus2d904l3ngm20u7d5
    [HTTP_UPGRADE_INSECURE_REQUESTS] => 1
    [HTTP_CACHE_CONTROL] => max-age=0
    [PATH] => /usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin:/usr/games:/usr/local/games
    [SERVER_SIGNATURE] => 
    [SERVER_SOFTWARE] => Apache/2.2.31 (Unix) mod_ssl/2.2.31 OpenSSL/1.0.2u DAV/2 SVN/1.8.5 PHP/5.6.40
    [SERVER_NAME] => 192.168.10.108
    [SERVER_ADDR] => 192.168.10.108
    [SERVER_PORT] => 80
    [REMOTE_ADDR] => 192.168.10.1
    [DOCUMENT_ROOT] => /opt/crab/var/www
    [SERVER_ADMIN] => admin@relaxcms.com
    [SCRIPT_FILENAME] => /home/jonny/dvlp/rc6/trunk/src/web/test/test_webdav.php
    [REMOTE_PORT] => 60448
    [GATEWAY_INTERFACE] => CGI/1.1
    [SERVER_PROTOCOL] => HTTP/1.1
    [REQUEST_METHOD] => GET
    [QUERY_STRING] => t=PROPFIND
    [REQUEST_URI] => /webdav/123?t=PROPFIND
    [SCRIPT_NAME] => /rc6/test/test_webdav.php
    [PATH_INFO] => /123
    [PATH_TRANSLATED] => /opt/crab/var/www/123
    [PHP_SELF] => /rc6/test/test_webdav.php/123
    [REQUEST_TIME_FLOAT] => 1651563221.973
    [REQUEST_TIME] => 1651563221


		 * 
		
	*/
	protected function init()
	{
		$method =  $_SERVER['REQUEST_METHOD'];
		$scriptName = $_SERVER['SCRIPT_NAME'];// [SCRIPT_NAME] => /rc6/test/test_webdav.php
		$scriptFileName = $_SERVER['SCRIPT_FILENAME'];
		$docroot = $_SERVER['DOCUMENT_ROOT'];
		$uri = $_SERVER['REQUEST_URI'];
		$this->_uri = $uri;
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, 'request URI='.$uri.', $scriptName='.$scriptName);
		
		//basename				
		$_basename = $scriptName; //str_replace("\\", "/", substr(realpath($scriptFile), strlen($this->_webroot_path)));
		$this->_basename = $_basename;
		
		//baseroot
		$pos = strrpos( $_basename, '/');
		$baseroot = substr( $_basename, 0, $pos);		
		$this->_baseroot = $baseroot;
		
		//读取第一个目录
		/*$udb = explode('/', $baseroot);
		$nr = count($udb);
		if ($nr > 1) {
			$name = '';
			$i = 0;
			foreach($udb as $key=>$v) {
				$i++;
				if ($v == '/')
					continue;
				$name = $v;				
				break;				
			}
			if ($name) {
				if (!is_dir($docroot.DS.$name)) {//alias or location, eg: /rc7/
					$webroot = '/'.$name;
					$i++;
				}
				//查static,themes标记
				
			}
		}*/
		
		
		$filenamelen = strlen($scriptFileName);
		$pathnamelen = strlen(RPATH_BASE);
		
		if ($filenamelen < $pathnamelen) { //相对目录 ./index.php
			$len = 0;
		} else {
			$len = strlen($baseroot) - ($pathnamelen - strlen(RPATH_PUBLIC));
		}
		$_webroot = substr($baseroot, 0, $len);		
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, 'len='.$len.', webroot='.$_webroot);
		$this->_webroot = $_webroot;
		
		/*//libroot
		$_libroot = str_replace("\\", "/", substr(RPATH_LIB, strlen(RPATH_ROOT)+1));
		$this->_libroot = $_webroot.'/'.$_libroot;
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, 'libbase='.$this->_libroot);
		
		//config
		$cfgbase = str_replace("\\", "/", substr(RPATH_CONFIG, strlen(RPATH_ROOT)+1));
		$this->_cfgbase = $_webroot.'/'.$cfgbase;
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, 'cfgbase='.$this->_cfgbase);
		*/
		//URI
		//path
		$pos = strpos($uri, $scriptName);
		if ($pos !== false) {
			$path = substr($uri, strlen($scriptName));		
		} else {
			if (($len = strlen($_webroot)) > 0 && strncmp($uri, $_webroot, $len) == 0) {
				$path = substr($uri, $len+1);
			} else {				
				$path = $uri;
			}
		}
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, 'path1='.$path);
		
		if (isset($_SERVER['PATH_INFO'])) 		
			$path = $_SERVER['PATH_INFO'];
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, 'path2='.$path, $_SERVER);
		
		
		$pos = strpos($path, '?');
		if ($pos !== false) {//'?'及后面的query_string去掉
			$this->_path = substr($path, 0, $pos);
		} else {
			$this->_path = $path;
		}
		
		if ($this->_path ) {
			$this->_path = safeEncoding($this->_path);
			$vpath = ltrim($this->_path, '/');			
			$this->_vpath = explode('/', $vpath);
		}	
		
		//uriroot
		// /webdav/a/123?t=PROPFIND
		// /rc6/test/test_webdav.php/a/123
		// /webdav/a/123

		$uribase = $_SERVER['SCRIPT_URL'];
		if ($path) {
			$pos = strpos($uribase, $path);			
		}	else {
			$pos = strpos($uribase, '?');
		}		
		if ($pos !== false) {//'?'及后面的query_string去掉
			$this->_uribase = substr($uribase, 0, $pos);
		} else {
			$this->_uribase = $uribase;
		}
				
		//schema
		$schema = '';
		if (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) 
				&& (strtolower($_SERVER['HTTPS']) != 'off')) {
			$schema = 'https://';
			$this->_is_ssl =true; 
		} else {
			$schema = 'http://';
		}
		$this->_schema = $schema;
		
		//client
		$remote_addr = isset($_SERVER['REMOTE_ADDR'])?$_SERVER['REMOTE_ADDR']:'127.0.0.1';
		if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$client = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} elseif(isset($_SERVER['HTTP_CLIENT_IP'])) {
			$client = $_SERVER['HTTP_CLIENT_IP'];
		} else {
			$client = $_SERVER['REMOTE_ADDR'];
		}		
		$client = preg_match("/^[\d]([\d\.]){5,13}[\d]$/", $client) ? $client : 'unknown';
		//rlog('client='.$client);	
		$this->_client = $client;		
		$this->_remote_addr = $remote_addr;		
		
		
		//useragent
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, $_SERVER["HTTP_USER_AGENT"]);
		
		//Mozilla/5.0 (Linux; Android 9; Redmi Note 8 Build/PKQ1.190616.001; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/86.0.4240.99 XWEB/4317 MMWEBSDK/20220505 Mobile Safari/537.36 MMWEBID/8179 MicroMessenger/8.0.23.2160(0x28001759) WeChat/arm64 Weixin NetType/WIFI Language/zh_CN ABI/arm64
		//
		// Mozilla/5.0 (iPhone; CPU iPhone OS 15_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148 MicroMessenger/8.0.29(0x18001d30) NetType/WIFI Language/zh_CN
		
		
		$this->_useragent = $_SERVER["HTTP_USER_AGENT"];
		//firefox : Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:63.0) Gecko/20100101 Firefox/63.0
		//ie8: Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; WOW64; Trident/4.0; SLCC2; .NET CLR 2.0.50727;
		// .NET CLR 3.5.30729; .NET CLR 3.0.0729; Media Center PC 6.0; .NET4.0C; .NET4.0E)
		if (isset($_SERVER["HTTP_USER_AGENT"])) {
			$agent = strtolower($_SERVER["HTTP_USER_AGENT"]);
			$pos = strpos($agent, "msie");
			if ($pos !== false) {
				$msie = substr($agent, $pos, 20);
				$q = strpos($msie, ';');
				$len = $q - 5;
				$ver = substr($msie, 5, $len);
				$ver = floor($ver);
				//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, $ver);				
				$this->_useragent_browser_ver = $ver;
				
			}		
		}
		$uainfo = $this->parseUserAgent($_SERVER["HTTP_USER_AGENT"]);
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, '$agent='.$agent);
		
		//url角
		$host = $_SERVER['HTTP_HOST'];
		$port = $_SERVER['SERVER_PORT'];
		$domain = $_SERVER['SERVER_NAME'];
		$this->_host =  $host;		
		$this->_weburl =  $schema.$host.$_webroot;	
		$this->_rooturl =  $schema.$host;	
		
		//method
		$this->_method = $method;
			
		
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "$method $uri");
		
	}
	
	protected function parseUserAgent($useragent)
	{
		//firefox
		//Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:63.0) Gecko/20100101 Firefox/63.0
		
		//ie8
		//Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; WOW64; Trident/4.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.0729; Media Center PC 6.0; .NET4.0C; .NET4.0E)
		
		//Mozilla/5.0 (Linux; Android 9; Redmi Note 8 Build/PKQ1.190616.001; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/86.0.4240.99 XWEB/4317 MMWEBSDK/20220505 Mobile Safari/537.36 MMWEBID/8179 MicroMessenger/8.0.23.2160(0x28001759) WeChat/arm64 Weixin NetType/WIFI Language/zh_CN ABI/arm64
		
		//Mozilla/5.0 (iPhone; CPU iPhone OS 15_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148 MicroMessenger/8.0.29(0x18001d30) NetType/WIFI Language/zh_CN
		
		//Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/106.0.0.0 Safari/537.36
		
		
		$this->_useragent = $useragent;
		
		$useragent = trim($useragent);
		if (!$useragent)
			return false;
		
		$uainfo = array();
		$uainfo['useragent'] = $useragent;
		
		$udb = explode(' ', $useragent);
		$nr = count($udb);
	
		for ($i=0; $i<$nr; $i++) {
			$name = trim($udb[$i]);
			$val = true;
			if (($pos = strpos($name, '/')) !== false) {
				$val = substr($name, $pos+1);	
				$name = substr($name, 0, $pos);
			}
			
			$name = str_replace(array('(', ')',';'), array('', ''), $name);
			$name = strtolower($name);
			
			switch($name) {
				case 'firefox':
				case 'chrome':
				case 'msie':
					$val = $udb[++$i];
					$this->_useragent_browser = $name;
					$this->_useragent_browser_ver = $val;
					break;
				case 'iphone':
				case 'windows':
					$os_name = $name;
					break;
				case 'android':
					$os_name = $name;
				case 'nt':
					$os_ver = $udb[++$i];
					break;
				case 'language':
					$this->_language = $udb[++$i];
					break;
				default:
					break;
			}
			
			$uainfo[$name] = $val;			
		}
		
		//rlog($uainfo);
		$this->_uainfo = $uainfo;

		//rlog('$os_name='.$os_name.',$os_ver='.$os_ver);
		
		$this->_useragent_os = $os_name;
		$this->_useragent_os_ver = $os_ver;
		
		
	}
	
	
	/**
	 * APP
	 *
	 * @param mixed $app This is a description
	 * @return mixed This is the return value description
	 *
	 */
	public function getRequestParams(&$ioparams=array())
	{
		$ioparams['_uri'] = $this->_uri;
		$ioparams['_uribase'] = $this->_uribase;
		$ioparams['_base'] = $this->_basename.$this->_path;
		$ioparams['_basename'] = $this->_basename;
		$ioparams['_baseroot'] = $this->_baseroot;
		$ioparams['_path'] = $this->_path;
		
		$ioparams['_webroot'] = $this->_webroot;
		$ioparams['_weburl'] = $this->_weburl;
		$ioparams['_rooturl'] = $this->_rooturl;
		$ioparams['_baseurl'] = $this->_rooturl.$this->_basename.$this->_path;
		$ioparams['_basenameurl'] = $this->_rooturl.$this->_basename;
		$ioparams['_url'] = $this->_rooturl.$this->_uri;
		
		$ioparams['_host'] = $this->_host;
		$ioparams['_dstroot'] = $this->_webroot.'/static';		
		$ioparams['_theroot'] = $this->_webroot.'/themes';
		$ioparams['_dataroot'] = $this->_webroot.'/data';
		$ioparams['_cfgbase'] = $this->_cfgbase;
		
		$ioparams['_schema'] = $this->_schema;
		$ioparams['_host'] = $this->_host;
		$ioparams['_port'] = $this->_port;
		$ioparams['_client'] = $this->_client;
		$ioparams['_remote_addr'] = $this->_remote_addr;
		$ioparams['_useragent'] = $this->_useragent;
		//browser
		$ioparams['_browser'] = $this->_useragent_browser;
		$ioparams['_browser_ver'] = $this->_useragent_browser_ver;
		$ioparams['_ismobile'] = $this->isMobile();
		
		
		$ioparams['vpath'] = $this->_vpath;
		$ioparams['vpath_offset'] = 0;
		$ioparams['method'] = $this->_method;
		
		
		//解析<PLG>/<COMPONENT>/<TASK>?
		$aname = $_REQUEST['app'];
		$cname = $_REQUEST['component'];
		$tname = $_REQUEST['task'];	
		$oname = $_REQUEST['output'];	
		
		!$aname && $aname = $_REQUEST['a'];
		!$cname && $cname = $_REQUEST['c'];
		!$tname && $tname = $_REQUEST['t'];
		!$oname && $oname = $_REQUEST['o'];
					
		$ioparams['aname'] = $aname;
		$ioparams['cname'] = $cname;
		$ioparams['tname'] = $tname;
		$ioparams['oname'] = $oname;
				
		
		//rlog($ioparams);
		
		return true;
	}
	
	public function getWebroot()
	{
		return $this->_webroot;
	}
	
	public function getWeburl()
	{
		return $this->_weburl;
	}
	
	
	public function isIE8()
	{
		if (!$this->isIE())
			return false;
			
		return floor($this->_useragent_browser_ver) ==  8;
	}	

	public function isLeIE9()
	{
		if (!$this->isIE())
			return false;		
		return floor($this->_useragent_browser_ver) <= 9;
	}
	
	public function isIE()
	{
		return $this->_useragent_browser == 'msie';
	}	
	
	
	public function isChrome()
	{
		return $this->_useragent_browser == 'chrome';
	}	
	
	public function isChrome_49_0_2623_110()
	{
		if (!$this->isChrome())
			return false;	
		return $this->_useragent_browser == '49.0.2623.110';
	}
	
	public function isMobile()
	{
		
		return $this->_useragent_os == 'android' || $this->_useragent_os == 'iphone';
	}
	
	
	public function isPost()
	{
		return $this->_method == "POST";
	}
	
	public function isDelete()
	{
		return $this->_method == "DELETE";
	}
	public function isGet()
	{
		return $this->_method == "GET";
	}
	
	public function uri()
	{
		return $_SERVER['REQUEST_URI'];
	}
		
	public function schema()
	{
		return $this->_schema;
	}
	
	public function baseroot()
	{
		return $this->_baseroot;
	}
	
	public function baserooturl()
	{
		return $this->_schema.$_SERVER['HTTP_HOST'].$this->_baseroot;
	}
			
	public function basename()
	{
		return $this->_basename;
	}
	
	public function baseurl()
	{
		$url = $this->_schema.$_SERVER['HTTP_HOST'].$this->_basename;	
		return $url;	
	}
	
	public function webroot()
	{
		return $this->_webroot;
	}
	
	public function libroot()
	{
		return $this->_webroot.$this->_libroot;
	}
	
	public function webrooturl()
	{
		return $this->_schema.$_SERVER['HTTP_HOST'].$this->_webroot;		
	}
	
	public function weburl()
	{
		return $this->_schema.$_SERVER['HTTP_HOST'].$this->_webroot.'/index.php';		
	}
	
		
	public function getPathInfo()
	{
		return $this->_path;
	}

	
	public function client()
	{
		return $this->_client;		
	}
	
	public function url()
	{
		$url = "";
		$schema = $this->_schema;
		
		if (!empty ($_SERVER['PHP_SELF']) && !empty ($_SERVER['REQUEST_URI'])) {
			$url = $schema . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		}
		else
		{
			$url = $schema . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'];			
			if (isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING'])) {
				$url .= '?' . $_SERVER['QUERY_STRING'];
			}
		}		
		return $url;
	}
	
	
	public function host()
	{
		return $_SERVER['HTTP_HOST'] ;
	}
	
	public function domain()
	{
		$h = explode(":", $_SERVER['HTTP_HOST']);
		return array_shift($h);
	}
	
	public function port()
	{
		return $_SERVER['SERVER_PORT'] ;
	}
	
	
	public function sbase()
	{
		$schema = $this->_schema;
		
		$port = $_SERVER['SERVER_PORT'];
		if ($port == "80")
		{
			return $schema.$_SERVER["SERVER_NAME"].$this->baseroot();
		}
		else
		{
			return $schema.$_SERVER["SERVER_NAME"].":".$port.$this->baseroot();
		}
	}
	
	public function sbaseurl($domain=null)
	{
		
		$url = $this->url();
		if ($domain === null) 
			return $url;
		
		$host = $_SERVER['HTTP_HOST'];
		$port = $_SERVER['SERVER_PORT'];
		if ($port != 80)
		{
			$domain = $domain.":".$port;
		}
		
		return str_replace($host, $domain, $url);
	}
	
	
	public function surl($domain)
	{
		$surl =  $this->_schema.$_SERVER['HTTP_HOST'].$this->_webroot;
		$host = $_SERVER['HTTP_HOST'];	
		if (strpos($host, ':')) {
			$port = $_SERVER['SERVER_PORT'];
			$domain = $domain.":".$port;
		}
	
		
		return str_replace($host, $domain, $surl);
	}
	
	public function sbasename()
	{
		$url = "";
		$schema = $this->_schema;
		$baseurl = $schema. $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'];	
		return $baseurl;
	}
	
	
	protected function __clean_value_html($value, $type)
	{
		switch (strtoupper($type))
		{
			case 'INT' :
			case 'INTEGER' :
				$result = intval($value);
				break;
			case 'FLOAT' :
			case 'DOUBLE' :
				$result = floatval($value);
				break;
			case 'BOOL' :
			case 'BOOLEAN' :
				$result = (bool) $value;
				break;			
			case 'STRING' :
				$result = (string) $this->__remove($this->__decode((string) $value));
				break;
			
			case 'ARRAY' :
				$result = (array) $value;
				break;			
			case 'PATH' :
				$pattern = '/^[A-Za-z0-9_-]+[A-Za-z0-9_\.-]*([\\\\\/][A-Za-z0-9_-]+[A-Za-z0-9_\.-]*)*$/';
				preg_match($pattern, (string) $value, $matches);
				$result = @ (string) $matches[0];
				break;			
			case 'USERNAME' :
				$result = (string) preg_replace( '/[\x00-\x1F\x7F<>"\'%&]/', '', $value );
				break;
			default :
				break;
		}
		return $result;
	}
	
	protected static function __clean_value_array_item(&$value) {
		if (is_string($value))
			$value = trim($value);
	}
	
	
	protected function __clean_value_nohtml($value, $type)
	{
		if (is_array($value)) {
			array_walk_recursive($value, array('CRequest', '__clean_value_array_item'));
		}
		
		return $value;
	}
	
	
	protected function __clean_value($value, $mask = 0, $type=null)
	{
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $value);
		
		if (!($mask & 1) && is_string($value)) 
		{
			$value = trim($value);
		}
		
		if ($mask & 2)
		{
			//ARRAY			
		}
		elseif ($mask & 4)
		{
			$value = $this->__clean_value_html($value, $type);
		}
		else
		{
			$value = $this->__clean_value_nohtml($value, $type);
		}
		return $value;
	}
	
	protected function __clean_tags($source)
	{
		/*
		 * In the beginning we don't really have a tag, so everything is
		 * postTag
		 */
		$preTag		= null;
		$postTag	= $source;
		$currentSpace = false;
		$attr = '';	 // moffats: setting to null due to issues in migration system - undefined variable errors
		
		// Is there a tag? If so it will certainly start with a '<'
		$tagOpen_start	= strpos($source, '<');
		
		while ($tagOpen_start !== false)
		{
			// Get some information about the tag we are processing
			$preTag			.= substr($postTag, 0, $tagOpen_start);
			$postTag		= substr($postTag, $tagOpen_start);
			$fromTagOpen	= substr($postTag, 1);
			$tagOpen_end	= strpos($fromTagOpen, '>');
			
			// Let's catch any non-terminated tags and skip over them
			if ($tagOpen_end === false) {
				$postTag		= substr($postTag, $tagOpen_start +1);
				$tagOpen_start	= strpos($postTag, '<');
				continue;
			}
			
			// Do we have a nested tag?
			$tagOpen_nested = strpos($fromTagOpen, '<');
			$tagOpen_nested_end	= strpos(substr($postTag, $tagOpen_end), '>');
			if (($tagOpen_nested !== false) && ($tagOpen_nested < $tagOpen_end)) {
				$preTag			.= substr($postTag, 0, ($tagOpen_nested +1));
				$postTag		= substr($postTag, ($tagOpen_nested +1));
				$tagOpen_start	= strpos($postTag, '<');
				continue;
			}
			
			// Lets get some information about our tag and setup attribute pairs
			$tagOpen_nested	= (strpos($fromTagOpen, '<') + $tagOpen_start +1);
			$currentTag		= substr($fromTagOpen, 0, $tagOpen_end);
			$tagLength		= strlen($currentTag);
			$tagLeft		= $currentTag;
			$attrSet		= array ();
			$currentSpace	= strpos($tagLeft, ' ');
			
			// Are we an open tag or a close tag?
			if (substr($currentTag, 0, 1) == '/') {
				// Close Tag
				$isCloseTag		= true;
				list ($tagName)	= explode(' ', $currentTag);
				$tagName		= substr($tagName, 1);
			} else {
				// Open Tag
				$isCloseTag		= false;
				list ($tagName)	= explode(' ', $currentTag);
			}
			
			
			/*
			 * Time to grab any attributes from the tag... need this section in
			 * case attributes have spaces in the values.
			 */
			while ($currentSpace !== false)
			{
				$attr			= '';
				$fromSpace		= substr($tagLeft, ($currentSpace +1));
				$nextSpace		= strpos($fromSpace, ' ');
				$openQuotes		= strpos($fromSpace, '"');
				$closeQuotes	= strpos(substr($fromSpace, ($openQuotes +1)), '"') + $openQuotes +1;
				
				// Do we have an attribute to process? [check for equal sign]
				if (strpos($fromSpace, '=') !== false) {
					/*
					 * If the attribute value is wrapped in quotes we need to
					 * grab the substring from the closing quote, otherwise grab
					 * till the next space
					 */
					if (($openQuotes !== false) && (strpos(substr($fromSpace, ($openQuotes +1)), '"') !== false)) {
						$attr = substr($fromSpace, 0, ($closeQuotes +1));
					} else {
						$attr = substr($fromSpace, 0, $nextSpace);
					}
				} else {
					/*
					 * No more equal signs so add any extra text in the tag into
					 * the attribute array [eg. checked]
					 */
					if ($fromSpace != '/') {
						$attr = substr($fromSpace, 0, $nextSpace);
					}
				}
				
				// Last Attribute Pair
				if (!$attr && $fromSpace != '/') {
					$attr = $fromSpace;
				}
				
				// Add attribute pair to the attribute array
				$attrSet[] = $attr;
				
				// Move search point and continue iteration
				$tagLeft		= substr($fromSpace, strlen($attr));
				$currentSpace	= strpos($tagLeft, ' ');
			}
			
			// Is our tag in the user input array?
			$tagFound = in_array(strtolower($tagName), $this->tagsArray);
			
			// If the tag is allowed lets append it to the output string
			if ((!$tagFound && $this->tagsMethod) || ($tagFound && !$this->tagsMethod)) {
				
				// Reconstruct tag with allowed attributes
				if (!$isCloseTag) {
					// Open or Single tag
					$attrSet = $this->_cleanAttributes($attrSet);
					$preTag .= '<'.$tagName;
					for ($i = 0; $i < count($attrSet); $i ++)
					{
						$preTag .= ' '.$attrSet[$i];
					}
					
					// Reformat single tags to XHTML
					if (strpos($fromTagOpen, '</'.$tagName)) {
						$preTag .= '>';
					} else {
						$preTag .= ' />';
					}
				} else {
					// Closing Tag
					$preTag .= '</'.$tagName.'>';
				}
			}
			
			// Find next tag's start and continue iteration
			$postTag		= substr($postTag, ($tagLength +2));
			$tagOpen_start	= strpos($postTag, '<');
		}
		
		// Append any code after the end of tags and return
		if ($postTag != '<') {
			$preTag .= $postTag;
		}
		return $preTag;
	}
	
	
	
	protected function __remove($value)
	{
		
		$loopCounter = 0;
		
		// Iteration provides nested tag protection
		while ($value != $this->_clean_tags($value))
		{
			$value = $this->__clean_tags($value);
			$loopCounter ++;
		}
		return $source;
	}
	
	protected function __decode($source)
	{
		// entity decode
		$trans_tbl = get_html_translation_table(HTML_ENTITIES);
		foreach($trans_tbl as $k => $v) 
		{
			$ttr[$v] = utf8_encode($k);
		}
		
		$source = strtr($source, $ttr);
		
		// convert decimal
		$source = preg_replace('/&#(\d+);/me', "utf8_encode(chr(\\1))", $source); // decimal notation
		
		// convert hex
		$source = preg_replace('/&#x([a-f0-9]+);/mei', "utf8_encode(chr(0x\\1))", $source); // hex notation
		return $source;
	}
	
	/**
	 * 取变量值
	 *
	 * @param mixed $name 变量名
	 * @param mixed $default 默认值
	 * @param mixed $hash 提定全局hash 表，如GET, POST, COOKIE等
	 * @param mixed $type This is a description
	 * @param mixed $mask This is a description
	 * @return mixed This is the return value description
	 *
	 */
	function get_var($name, $default = null, $hash = 'default', $type = 'none', $mask = 0)
	{
		$hash = strtoupper( $hash );
		if ($hash == 'METHOD') 
			$hash = strtoupper($_SERVER['REQUEST_METHOD']);
		
		$type	= strtoupper( $type );
		switch ($hash)
		{
			case 'GET' :
				$input = &$_GET;
				break;
			case 'POST' :
				$input = &$_POST;
				break;
			case 'FILES' :
				$input = &$_FILES;
				break;
			case 'COOKIE' :
				$input = &$_COOKIE;
				break;
			case 'ENV'    :
				$input = &$_ENV;
				break;
			case 'SERVER'    :
				$input = &$_SERVER;
				break;
			default:
				$input = &$_REQUEST;
				$hash = 'REQUEST';
				break;
		}
		
		if (!isset($input[$name]) || $input[$name] === null)
			return $default;
			
		$var = $this->__clean_value($input[$name], $mask, $type);	
		return $var;
		
	}
	
	public function set_var($name, $value = null, $hash = 'method', $overwrite = true)
	{
		if (!$overwrite && array_key_exists($name, $_REQUEST)) 
		{
			return $_REQUEST[$name];
		}
		
		$hash = strtoupper($hash);
		if ($hash === 'METHOD') 
			$hash = strtoupper($_SERVER['REQUEST_METHOD']);
		
		$previous	= array_key_exists($name, $_REQUEST) ? $_REQUEST[$name] : null;
		
		switch ($hash)
		{
			case 'GET' :
				$_GET[$name] = $value;
				$_REQUEST[$name] = $value;
				break;
			case 'POST' :
				$_POST[$name] = $value;
				$_REQUEST[$name] = $value;
				break;
			case 'COOKIE' :
				$_COOKIE[$name] = $value;
				$_REQUEST[$name] = $value;
				break;
			case 'FILES' :
				$_FILES[$name] = $value;
				break;
			case 'ENV':
				$_ENV['name'] = $value;
				break;
			case 'SERVER':
				$_SERVER['name'] = $value;
				break;
		}
		
		return $previous;
	}
	
	public function getComponent($default=null)
	{
		if ($this->component)
			return $this->component;
		if ($default)
			$this->component = $default;
		
		return $default;
	}
	
	public function setComponent($cname)
	{
		$this->component = $cname;		
	}
	
	public function getTask($default=null)
	{
		if ($this->task)
			return $this->task;
		if ($default)
			$this->task = $default;		
		return $default;
	}
	
	public function setTask($tname)
	{
		$this->task = $tname;		
	}
	
	//前缀
	protected function getCookiePre()
	{
		$cf = get_config();
		$hash = $cf["hash"];
		return substr(md5($hash),0,5);
	}	
	
	
	//COOKIE
	//设置
	
	/**
	 * setCookie
	 * 设置
	 *
	 * @param mixed $ck_var This is a description
	 * @param mixed $ck_value This is a description
	 * @param mixed $ck_time This is a description
	 * @return mixed This is the return value description
	 *
	 */
	public function setCookie($ck_var, $ck_value, $ck_time = 0)
	{
		$cf = get_config();
		
		$ts = time();
		$ssl = $_SERVER['SERVER_PORT'] == '443' ? 1:0;
		
		$ckdomain = $cf['ckdomain'];
		!$ckdomain && $ckdomain = "";
		
		$ckpath = s_slashify($this->_webroot);
		$ckpath2 = s_slashify($this->_baseroot);
		
		if ($ck_value) {
			if (is_array($ck_value)) {
				$res = serialize($ck_value);
			} else {
				$res = $ck_value;
			}
			$e = Factory::GetEncrypt();
			$ck_value = $e->mcrypt_des_encode($cf['ckey'], $res);
		}				
		
		session_start();
		$ckname = $this->getCookiePre().'_'.$ck_var;
		
		if (!$ck_value)
		{
			$res = setcookie($ckname, $ck_value, 0);
			$res = setcookie($ckname, $ck_value, time()-30*3600*24, $ckpath, $ckdomain, $ssl);
			$res = setcookie($ckname, $ck_value, time()-30*3600*24, $ckpath2, $ckdomain, $ssl);
		}
		elseif ($ck_time === 0)
		{
			$res = setcookie($ckname, $ck_value);
		}
		else
		{
			$ck_time += $ts;
			$res = setcookie($ckname, $ck_value, $ck_time, $ckpath, $ckdomain, $ssl);			
		}
		
		return true;
	}
	
	public function getCookie($ck_var)
	{
		$e = Factory::GetEncrypt();
		$sid = $_COOKIE[$this->getCookiePre().'_'.$ck_var];
		
		$cf = get_config();		
		//$old = base64_decode($sid);
		//$str = des($old, $cf['ckey'], 1);
		$str = $e->mcrypt_des_decode($cf['ckey'], $sid);
		
		//解密		
		return $str;
	}
	
	public function viewtype()
	{
		return $_SERVER['HTTP_VIEWTYPE'];
	}
}


//提取整值
function get_int($name, $default = 0, $hash = 'default')
{
	if (!isset($_REQUEST[$name]))
		return $default;
	return intval($_REQUEST[$name]);
}

//提取浮点值
function get_float($name, $default = 0.0, $hash = 'default')
{
	$r = Factory::GetRequest();
	return $r->get_var($name, $default, $hash, 'float');
}

//提取BOOL值
function get_bool($name, $default = false, $hash = 'default')
{
	$r = Factory::GetRequest();
	return $r->get_var($name, $default, $hash, 'bool');
}

//提取Word类型值
function get_word($name, $default = '', $hash = 'default')
{
	$r = Factory::GetRequest();
	return $r->get_var($name, $default, $hash, 'word');
}

//提取cmd
function get_cmd($name, $default = '', $hash = 'default')
{
	$r = Factory::GetRequest();
	return $r->get_var($name, $default, $hash, 'cmd');
}

//提取字串
function get_string($name, $default = '', $hash = 'default', $mask = 0)
{
	$r = Factory::GetRequest();
	return $r->get_var($name, $default, $hash, 'string', $mask);
}

function get_var_raw($name, $default = '')
{
	$r = Factory::GetRequest();
	return $r->get_var($name, $default, $hash, 'default', 2);
}

function get_var($name, $default = null, $hash = 'default', $type = 'none', $mask = 0)
{
	$r = Factory::GetRequest();
	return $r->get_var($name, $default, $hash, $type, $mask);
}

function get_array($name)
{
	$arr = get_var($name);
	if (!$arr)
		return array();
	return $arr;
}

function get_vars()
{
	return $_REQUEST;
}

function set_var($name, $value = null, $method = 'POST', $overwrite = true)
{
	$r = Factory::GetRequest();
	return $r->set_var($name, $value, $method, $overwrite);
}

