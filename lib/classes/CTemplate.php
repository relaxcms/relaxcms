<?php
/**
 * @file
 *
 * @brief 
 * 
 * 模板类
 *
 */

class CTemplate extends CObject
{
	var $_template;
	var $_data = array();
	
	function __construct($tpl = "default")
	{
		$this->_template = $tpl;
	}
	
	function CTemplate($tpl="default")
	{
		$this->__construct();
	}
	
	static function &GetInstance( $tpl = 'default' )
	{
		static $instance;
		if (!is_object($instance)) {
			$instance = new CTemplate($tpl);
		}
		return $instance;
	}
		
	protected function __parseTemplateInclude($data, $ioparams)
	{
		$tdir = $ioparams['tdir'];
		$app_tdir = $ioparams['app_tdir'];
		$def_tdir = RPATH_TEMPLATE_DEFAULT;

		$replace = array();
		$matches = array();
		if (preg_match_all('#<rdoc:include\s+file=(.*)\s*(/?>|(\s*>(.*)</rdoc:include>))#isU', $data, $matches))
		{
			$tpls = $matches[1];
			$i = 0;
			//开头与结尾的' " 定界符去掉
			foreach($tpls as $key=>$v) {
				$tplfile = trimfilename($v);
				$tplpathname  = $tdir.DS.$tplfile; //app template dir
				if (!file_exists($tplpathname)) {
					//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "no '$tplpathname'!");
					$tplpathname  = $app_tdir.DS.$tplfile; // current template dir
					if (!file_exists($tplpathname)) {
						$tplpathname  = $def_tdir.DS.$tplfile; // default tempatel dir						
					}
				}
				
				if (!file_exists($tplpathname)) {
					$tmp = "NOT FOUND template : $tplpathname";
					rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "NOT FOUND TPL '$tplpathname'!");
				} else {
					$tmp = s_read($tplpathname);
					$tmp = strim_bom($tmp);
				}		
				$replace[$i++] = $tmp;
			}	
			$data = str_replace($matches[0], $replace, $data);	
			$data = $this->__parseTemplateInclude($data, $ioparams);
		}	
		return $data;
	}
	
	
	
	
	protected function initCatalog($name)
	{
		$m = Factory::GetModel('catalog');
		$res = $m->getOne(array('name'=>$name));
		if ($res) 
			return $res['id'];
			
		$params = array();
		$params['name'] = $name;
		
		$res = $m->set($params);
		
		if ($res) {
			return $params['id'];
		}
		return false;		
	}
	
	protected function autoCreateTplModule($params)
	{
		$m = Factory::GetModel('module');
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, $params);
		$res = $m->getOne(array('mid'=>$params['mid']));
		if ($res)
			$params['id'] = $res['id'];
		
		//content
		//$content = $params['content'];
		//str_replace('\'', '"', $content);
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $content);
		
		$params['content'] = htmlspecialchars($params['content']);
		
		$res = $m->set($params);										
		if (!$res) {
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "set TPL module failed!", $params);
			return false;
		}
		
		return $params;
	}
	
	//解析
	private function parseTemplate($tplfile, $data, $ioparams)
	{
		$cdir = dirname($tplfile);

		//$tplname
		$tplname = s_filename($cdir);
		
		//INCLUDE FILE
		$data = $this->__parseTemplateInclude($data, $ioparams);
				
		//{form_rule fr=$formRules name='curpassword'}
		$replace = array();
		$matches = array();
		if (preg_match_all('#{(\w+)\s+(.*)}#isU', $data, $matches)) {
			$count = count($matches[1]);	
			for ($i=0; $i<$count; $i++) {
				$params = str_replace('=', '=>', $matches[2][$i]);
				$params = trim($params);
				$params = str_replace(' ', ',', $params);
				
				$fn  = $matches[1][$i].'(array('.$params.'))';				
				$replace[$i] = "\r\nEOT;\r\necho $fn;\r\nprint <<<EOT\r\n";
			}
			$data = str_replace($matches[0], $replace, $data);
		}		
		
		//function
		$s = array("{@", "@}", "<!--#", "#-->");			
		$e = array("\r\nEOT;\r\necho ", ";\r\nprint <<<EOT\r\n", "\nEOT;\n", "\nprint <<<EOT\n");
		
		if (function_exists('str_ireplace')){
			$data = str_ireplace($s, $e, $data);
		} else {
			$data = str_replace($s, $e, $data);
		}
		
		//language
		$t = $ioparams['_i18ndb'];
		
		//t
		$replace = array();
		$matches = array();		
		if (preg_match_all('#@t\[(.*)\]#isU', $data, $matches)) {
			$nr = count($matches[1]);
			
			for ($i=0; $i<$nr; $i++) {
				$key = trim($matches[1][$i], '\'\"');			
				if (isset($t[$key])) {
					//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "$i : found key '$key'");
					$replace[$i] = $t[$key]; //'{$t[\''.$key.'\']}'; //格式化：{$t['$key']}
				} /*else if (isset($T[$key])) {
					//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "$i : found key '$key'");
					$replace[$i] = '{$T[\''.$key.'\']}'; //格式化：{$T['$key']}
				}*/  else {
					rlog(RC_LOG_DEBUG, __FILE__, __LINE__, "WARNING: $i : no key '$key'!");
					$replace[$i] = $key;
				}
			}
			$data = str_replace($matches[0], $replace, $data);
		}
		
		$m = Factory::GetModel('module');
		$m2 = Factory::GetModel('module_params');
		$m3 = Factory::GetModel('module2tplfile');
		
		//links
		if (($links = parseTplLinkData($data))) {
			//matchs
			$mdb = $links['mdb'];
			$nr = count($mdb);
			$olddb = array();
			$newdb = array();
			
			for ($i=0; $i<$nr; $i++) {
				$params = $mdb[$i];
				
				$mid = $params['mid'];
				$res = $m->getOne(array('mid'=>$mid));
				if (!$res) {
					rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "WARNING: no mid '$mid' auto create!", $params);					
					$res = $this->autoCreateTplModule($params);
					if (!$res) {
						rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "set TPL module failed!", $params);
						continue;
					}
				}
				
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $params);
				
				$content = $params['content'];
				$url = $params['url'];				
				$src = $params['src'];
				$title = $params['title'];
				
				$olddb[$i] = $content;
				
				if ($res) {
					//content
					$mid = $res['id'];
					//查询 module_params
					$newurl = $res['url'];
					$newsrc = $res['src'];
					$newtitle = $res['title'];
					
					$res2 = $m2->getOne(array('mid'=>$mid));
					if ($res2) {
						//
					}
					
					//replace content
					if ($url != $newurl)
						$content = str_replace($url, $newurl, $content);
					if ($src != $newsrc) {
						$content = str_replace($src, $newsrc, $content);
					} else {
						//check src
						/*if ($newsrc && !is_url($newsrc) && !is_start_slash($newsrc) ) {
							$newsrc = $ioparams['_theroot']."/$tplname/$newsrc";
							$content = str_replace($src, $newsrc, $content);
						}*/
					}
					
					if ($title != $newtitle)
						$content = str_replace($title, $newtitle, $content);
					
					//
					$_params = array();
					$_params['mid'] = $mid;
					$_params['tplfile'] = $tplfile;					
					$m3->set($_params);
				}
				$newdb[$i] = $content;	
			}
			rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $olddb, $newdb);
			
			if ($newdb) {
				$data = str_replace($olddb, $newdb, $data);
			}
		}
		
		//modules
		//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, '$tplfile='.$tplfile);
		if (($modules = parseTplModuleData($data))) {
			//matchs
			$mdb = $modules['mdb'];
						
			$nr = count($mdb);
			
			$olddb = array();		
			$newdb = array();		
			
			for ($i=0; $i<$nr; $i++) {
				$params = $mdb[$i];
								
				$mid = $params['mid'];
				
				$res = $m->getOne(array('mid'=>$mid));
				if (!$res) {
					rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "WARNING: no mid '$mid' auto create!", $params);					
					$res = $this->autoCreateTplModule($params);
					if (!$res) {
						rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "set TPL module failed!", $params);
						continue;
					}
				}
				
				$content = $params['content'];
				$old_attribs = $params['attribs'];
				$attribs = attr2array($old_attribs);
				$attribs['mid'] = $mid;				
				$olddb[$i] = $content;
				
				if ($res) {
					//content
					$mid = $res['id'];
					
					//check cid
					if (isset($attribs['cid']) && !is_numeric($attribs['cid']) 
							&& !is_start_with($attribs['cid'], '$')) {
						$modcatalog = Factory::GetModel('catalog');
						$cid = $modcatalog->genCatalog(array('name'=>$attribs['cid']));
						if ($cid) {
							$attribs['cid'] = $cid;
						}
					}
					
					//查询 module_params
					$res2 = $m2->getOne(array('mid'=>$mid));
					if ($res2) {
						//cid
						if ($res2['cid'] > 0) {
							$attribs['cid'] = $res2['cid'];
						}
						//flags
						if ($res2['flags'] > 0) {
							$attribs['flags'] = $res2['flags'];
						}
						//tags
					}
					
					//title
					if ( !isset($attribs['title']) ) {
						$attribs['title'] = $res['title']?$res['title']:$res['name'];
					}
					
					
					$newattrs = array2attr($attribs);
					$content = str_replace($old_attribs, $newattrs, $content);
					
					
					//
					$_params = array();
					$_params['mid'] = $mid;
					$_params['tplfile'] = $tplfile;					
					$m3->set($_params);
				}
				
				rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $content);
				
				$newdb[$i] = $content;	
			}
						
			$data = str_replace($olddb, $newdb, $data);						
		}
		
		
		//clean
		$data = preg_replace ("#print <<<EOT\s+EOT;#", '', $data);
		$data = "<?php\r\nprint <<<EOT\r\n".$data."\r\nEOT;\n?>";
				
		return $data;
	}
	
	public function compileTemplate($tpl, $ioparams, $tplfilename='')
	{
		$dir = RPATH_TEMPLATE_CPL;
		!is_dir($dir) && mkdir($dir);
		$lang = $ioparams['_lang'];	
		$cache_file = $dir.DS.$tplfilename.'_'.md5($tpl.$ioparams['_appname']).$lang.'.tpl';
		$mt = 0;
		if (!file_exists($tpl)) {
			$data = "NO TPL '$tplfilename'!";			
			rlog(RC_LOG_ERROR, __FILE__, __LINE__, $data);
		} else {
			$mt = filemtime($tpl);
		}
		
		if (!file_exists($cache_file) || !$mt || $mt > filemtime($cache_file)) {
			$mt && $data = s_read($tpl);
			$data = $this->parseTemplate($tpl, $data, $ioparams);
			s_write($cache_file, $data);			
		}
		return $cache_file;
	}
}
