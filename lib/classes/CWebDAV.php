<?php

require_once RPATH_SUPPORTS.DS."webdav".DS."_parse_propfind.php";
require_once RPATH_SUPPORTS.DS."webdav".DS."_parse_proppatch.php";
require_once RPATH_SUPPORTS.DS."webdav".DS."_parse_lockinfo.php";



defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

class CWebDAV
{
	protected $_name;
	protected $_dav_powered_by = "RC WebDAV";
	
	public function __construct($name, $options= array())
	{
		$this->_name = $name;
	}	
	
	public function CWebDAV($name, $options= array()) 
	{
		$this->__construct($name, $options);
	}
	
	static function &GetInstance($name, $options=array())
	{
		static $instances;
		if (!$name)
			$name = '';		
		if (!isset( $instances )) 
			$instances = array();					
		if (empty($instances[$name]))	{			
			$instance = new CWebDAV($name, $options);			
			$instances[$name] = &$instance;
		}
		return $instances[$name];
	}
	
	protected function showStatus($status, $desc='') 
	{
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, 'status='.$status);
		
		// simplified success case
		if ($status === true) {
			$status = 200;
		} else if ($status === false) {
			$status  = 404;
		}
		
		switch ($status) {
			case 200:
				$statusMsg = '200 OK';
				break;
			case 400:
				break;			
			case 401:
				$statusMsg = '401 Authorization Required';
				break;
			case 404:				
			default:
				$statusMsg = "404 Not Found";
				break;
		}
				
		// generate HTTP status response
		header("HTTP/1.1 $statusMsg");		
	}
	
	
	protected function newUUID() 
	{
		// use uuid extension from PECL if available
		if (function_exists("uuid_create")) {
			return uuid_create();
		}
		
		// fallback
		$uuid = md5(microtime().getmypid());    // this should be random enough for now
		
		// set variant and version fields for 'true' random uuid
		$uuid{12} = "4";
		$n = 8 + (ord($uuid{16}) & 3);
		$hex = "0123456789abcdef";
		$uuid{16} = $hex{$n};
		
		// return formated uuid
		return substr($uuid,  0, 8)."-"
			.  substr($uuid,  8, 4)."-"
			.  substr($uuid, 12, 4)."-"
			.  substr($uuid, 16, 4)."-"
			.  substr($uuid, 20);
	}
	
	/**
	 * create a new opaque lock token as defined in RFC2518
	 *
	 * @param  void
	 * @return string  new RFC2518 opaque lock token
	 */
	protected function newLockToken() 
	{
		return "opaquelocktoken:".$this->newUUID();
	}
	
	
	protected function webdav_LOCK($ioparams)
	{
		//$this->httpStatus("412 Precondition failed");
		
		if (isset($_SERVER['HTTP_DEPTH'])) {
			$ioparams["depth"] = $_SERVER["HTTP_DEPTH"];
		} else {
			$ioparams["depth"] = 0;
		} 		
		
		
		// extract lock request information from request XML payload
		$lockinfo = new _parse_lockinfo("php://input");
		if (!$lockinfo->success) {
				$this->httpStatus("400 bad request"); 
				return false; 
		}
		
		rlog($lockinfo);
			
		// new lock 
		$options["scope"]     = $lockinfo->lockscope;
		$options["type"]      = $lockinfo->locktype;
		$options["owner"]     = $lockinfo->owner;            
		$options["locktoken"] = $this->newLockToken();
			
		$this->httpStatus("200 OK");
		
		header('Content-Type: text/xml; charset="utf-8"');
		header("Lock-Token: <$options[locktoken]>");
		echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
		echo "<D:prop xmlns:D=\"DAV:\">\n";
		echo " <D:lockdiscovery>\n";
		echo "  <D:activelock>\n";
		echo "   <D:lockscope><D:$options[scope]/></D:lockscope>\n";
		echo "   <D:locktype><D:$options[type]/></D:locktype>\n";
		echo "   <D:depth>1</D:depth>\n";
		echo "   <D:owner>$options[owner]</D:owner>\n";
		echo "   <D:timeout>10000</D:timeout>\n";
		echo "   <D:locktoken><D:href>$options[locktoken]</D:href></D:locktoken>\n";
		echo "  </D:activelock>\n";
		echo " </D:lockdiscovery>\n";
		echo "</D:prop>\n\n";
		
		return true;
	}
	
	protected function webdav_UNLOCK($ioparams) 
	{
		
		$this->httpStatus("200 UNLOCK");		
		return true;
	}

	protected function webdav_GET($ioparams) 
	{
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "IN", $ioparams);
				
		$m = Factory::GetFile();	
		
		$res = $m->http($ioparams);
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, "call http GET failed!", $ioparams);
			return false;
		}	
				
		return $res;
	}
		
	
	protected function webdav_PROPFIND($ioparams) 
	{
		
		if (isset($_SERVER['HTTP_DEPTH'])) {
			$ioparams["depth"] = $_SERVER["HTTP_DEPTH"];
		} else {
			$ioparams["depth"] = 0;
		} 		
		
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "IN", $ioparams, $_SERVER);
		
		$m = Factory::GetFile();
		$_uribase = $ioparams['_uri'];
		
		//$ioparams['_path'] = s_hslash(ltrim($ioparams['_path'], '/webdav'));
		$files = $m->webdav_PROPFIND($ioparams);
		if (!$files) {
			$this->httpStatus("404 Not Found");
			return false;
		}
		
		$ns_defs = "xmlns:ns0=\"urn:uuid:c2f41010-65b3-11d1-a29f-00aa00c14882/\" xmlns:ns1=\"DAV:\" ";
		
		$this->httpStatus("207 Multi-Status");		
		header('Content-Type: text/xml; charset="utf-8"');
		// ... and payload
		echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
		echo "<D:multistatus xmlns:D=\"DAV:\">\n";
		
		foreach ($files as $key=>$v) {
			
			echo " <D:response $ns_defs>\n";
			
			$href = $_uribase.'/'.$v['name'];			
			
			/* minimal urlencoding is needed for the resource path */
			$href = s_urlencode($href);
			echo "  <D:href>$href</D:href>\n";
			
			echo "  <D:propstat>\n";
			echo "   <D:prop>\n";			
						
			echo "<D:displayname>";
			echo htmlspecialchars($v['name']);
			echo "</D:displayname>\n";  
			
			echo "<ns1:creationdate ns0:dt=\"dateTime.tz\">"
				. gmdate("Y-m-d\\TH:i:s\\Z", $v['ctime'])
				. "</ns1:creationdate>\n";
				
			echo "<ns1:getlastmodified ns0:dt=\"dateTime.rfc1123\">"
				. gmdate("D, d M Y H:i:s ", $v['ts'])
				. "GMT</ns1:getlastmodified>\n";
				
			
			if ($v['isdir']) {
				echo "<ns1:resourcetype><D:collection/></ns1:resourcetype>\n";
				echo "<ns1:getcontenttype>httpd/unix-directory</ns1:getcontenttype>\n";
			} else {
				echo "<ns1:resourcetype></ns1:resourcetype>\n";
				echo "<D:getcontenttype>$v[mimetype]</D:getcontenttype>\n";
				echo "<D:getcontentlength>$v[size]</D:getcontentlength>\n";				
			}
			
			echo "<ns1:lastaccessed ns0:dt=\"dateTime.rfc1123\">"
				. gmdate("D, d M Y H:i:s ", $v['ts'])
				. "GMT</ns1:lastaccessed>\n";
				
			echo "<D:ishidden>false</D:ishidden>\n";
			
			//RC
			echo "<D:id>$v[id]</D:id>\n";
			
			//lock
			/*
			<D:supportedlock>
<D:lockentry>
<D:lockscope><D:exclusive/></D:lockscope>
<D:locktype><D:write/></D:locktype>
</D:lockentry>
<D:lockentry>
<D:lockscope><D:shared/></D:lockscope>
<D:locktype><D:write/></D:locktype>
</D:lockentry>
</D:supportedlock>
<D:lockdiscovery/>
*/
			# (mvlcek) begin modification
			/*echo "     <D:supportedlock>";
			echo "<D:lockentry>";
			echo "<D:lockscope><D:exclusive/></D:lockscope>";
			echo "<D:locktype><D:write/></D:locktype>";
			echo "</D:lockentry>";
			
			
			echo "<D:lockentry>";
			echo "<D:lockscope><D:shared/></D:lockscope>";
			echo "<D:locktype><D:write/></D:locktype>";
			echo "</D:lockentry>";
			
			
			echo "</D:supportedlock>\n";
			echo "     <D:supportedlock/>\n";*/
			
			echo "   </D:prop>\n";
			echo "   <D:status>HTTP/1.1 200 OK</D:status>\n";
			echo "  </D:propstat>\n";
			
			echo " </D:response>\n";
		}	
		
		echo "</D:multistatus>\n";
		
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "OUT");
		
		
		
		return true;
	}
	
	protected function webdav_PROPPATCH($ioparams) 
	{
		$propinfo = new _parse_proppatch("php://input");
		rlog($propinfo);
		/*
		2022-05-03 18:31:25 : client 192.168.10.1 : 0/0x00000000: _parse_proppatch Object
(
    [success] => 1
    [props] => Array
        (
            [0] => Array
                (
                    [name] => Win32CreationTime
                    [ns] => urn:schemas-microsoft-com:
                    [status] => 200
                    [val] => Tue, 03 May 2022 10:31:19 GMT
                )

            [1] => Array
                (
                    [name] => Win32LastAccessTime
                    [ns] => urn:schemas-microsoft-com:
                    [status] => 200
                    [val] => Tue, 03 May 2022 10:31:19 GMT
                )

            [2] => Array
                (
                    [name] => Win32LastModifiedTime
                    [ns] => urn:schemas-microsoft-com:
                    [status] => 200
                    [val] => Tue, 03 May 2022 10:31:19 GMT
                )

            [3] => Array
                (
                    [name] => Win32FileAttributes
                    [ns] => urn:schemas-microsoft-com:
                    [status] => 200
                    [val] => 00000010
                )

        )

    [depth] => 0
    [mode] => set
)
*/
			$this->httpStatus("207 Multi-Status");
			header('Content-Type: text/xml; charset="utf-8"');
			
			echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
			
			echo "<D:multistatus xmlns:D=\"DAV:\">\n";
			echo " <D:response>\n";
			
			$href = '/webdav'.$ioparams['_path'];			
			/* minimal urlencoding is needed for the resource path */
			$href = s_urlencode($href);
			echo "  <D:href>$href</D:href>\n";
			
			foreach ($propinfo["props"] as $prop) {
				echo "   <D:propstat>\n";
				echo "    <D:prop><$prop[name] xmlns=\"$prop[ns]\"/></D:prop>\n";
				echo "    <D:status>HTTP/1.1 $prop[status]</D:status>\n";
				echo "   </D:propstat>\n";
			}
			
			
			
			echo " </D:response>\n";
			echo "</D:multistatus>\n";


			return true;
	}


	
	protected function webdav_MKCOL($ioparams) 
	{
		// for compatibility test (litmus)
		$stream = fopen("php://input", "r");
		$body = fread($stream, 4096);
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, $body);
		if ($body !== false && $body !== '') 
			return $this->httpStatus("415 Unsupported Media Type");
		
		$m = Factory::GetFile();
		
		$res = $m->createDirectory($ioparams['_path'], $ioparams); 
		if ($res) {
			return $this->httpStatus("201 Created");
		} else {
			return $this->httpStatus("500 Server Error");			
		} 
	}
	
	
	public function __checkAuth($auth_type, $auth_user, $auth_pw)
	{
		$params = array();
		$params['type'] = $auth_type;
		$params['username'] = $auth_user;
		$params['password'] = $auth_pw;
		
		if (!$auth_user) {
			rlog(RC_LOG_DEBUG,__FILE__, __LINE__, "no auth user!", $params);	
			return false;
		}
		
		$m = Factory::GetUser();
		$res = $m->login($params);
			
		rlog(RC_LOG_DEBUG,__FILE__, __LINE__, "OUT res=$res!");	
		
		return $res;
	}
	
	
	protected function checkAuth() 
	{
		$auth_type = isset($_SERVER["AUTH_TYPE"]) 
			? $_SERVER["AUTH_TYPE"] 
			: null;
		
		$auth_user = isset($_SERVER["PHP_AUTH_USER"]) 
			? $_SERVER["PHP_AUTH_USER"] 
			: null;
		
		$auth_pw   = isset($_SERVER["PHP_AUTH_PW"]) 
			? $_SERVER["PHP_AUTH_PW"] 
			: null;
					
		return $this->__checkAuth($auth_type, $auth_user, $auth_pw);		
	}
	
	
	protected function webdav_HEAD($ioparams)
	{
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "IN");
		
		$m = Factory::GetFile();
		
		$res = $m->http($ioparams);
		
		//rlog($ioparams);
		
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "OUT");
		
		return $res;
		
	}
	
	protected function webdav_PUT($ioparams)
	{
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "IN");
		
		$m = Factory::GetFile();
		
		$res = $m->webdav_PUT($ioparams);
		
		if ($res) {
			return $this->httpStatus("201 Created");
		} else {
			return $this->httpStatus("501 Server Error");			
		} 
				
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "OUT");
		
		return $res;
		
	}
	
	protected function webdav_DELETE($ioparams)
	{
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "IN");
		
		$m = Factory::GetFile();
		
		$res = $m->webdav_DELETE($ioparams);
		
		if ($res) {
			return $this->httpStatus("204 Deleted OK");
		} else {
			return $this->httpStatus("500 Server Error");			
		} 
		
		
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "OUT");
		
		return $res;
		
	}
	
	/**
	 * check for implemented HTTP methods
	 *
	 * @param  void
	 * @return array something
	 */
	protected function getAllow() 
	{
		// OPTIONS is always there
		$allow = array("OPTIONS" =>"OPTIONS");
		$allow = array("GET" =>"GET");
		$allow = array("DELETE" =>"DELETE");
		$allow = array("PROPFIND" =>"PROPFIND");
		$allow = array("PUT" =>"PUT");
		
		
		// we can emulate a missing HEAD implemetation using GET
		if (isset($allow["GET"]))
			$allow["HEAD"] = "HEAD";
		
		// no LOCK without checklok()
		if (!method_exists($this, "checkLock")) {
			unset($allow["LOCK"]);
			unset($allow["UNLOCK"]);
		}
		
		return $allow;
	}
	
	
	
	protected function webdav_OPTIONS($ioparams)
	{
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "IN");
		
		// Microsoft clients default to the Frontpage protocol 
		// unless we tell them to use WebDAV
		header("MS-Author-Via: DAV");
		
		// get allowed methods
		$allow = $this->getAllow();
		
		// dav header
		$dav = array(1);        // assume we are always dav class 1 compliant
		if (isset($allow['LOCK'])) {
			$dav[] = 2;         // dav class 2 requires that locking is supported 
		}
		
		header("DAV: "  .join(", ", $dav));
		header("Allow: ".join(", ", $allow));
		
		header("Content-length: 0");
		
		
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "OUT");
		
		return true;
		
	}
	
	
	public function run($tname='')
	{	
		// identify ourselves
		header("X-Dav-Powered-By: ".$this->_dav_powered_by);
		
		$ioparams = Factory::GetParams();
		
		//UID
		// check authentication
		if ((!(($ioparams['method'] == 'OPTIONS') && ($ioparams['_path'] == "/")))
				&& (!$this->checkAuth())) {
			// RFC2518 says we must use Digest instead of Basic
			// but Microsoft Clients do not support Digest
			// and we don't support NTLM and Kerberos
			// so we are stuck with Basic here
			header('WWW-Authenticate: Basic realm="'.($this->_dav_powered_by).'"');
			
			// Windows seems to require this being the last header sent
			// (changed according to PECL bug #3138)
			$this->showStatus(401);
			return false;
		}
		
		$res = 404;
		
		
		$method  = "webdav_".($tname?$tname:$ioparams['method']);		
		if (method_exists($this, $method)) 
			$res = $this->$method($ioparams);
					
		$this->showStatus($res);	
		
		return $res;	
	}		
}