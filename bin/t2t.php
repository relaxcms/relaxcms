<?php

/* 
 * eg: php <WWWDIR>/bin/t2t.php <TPLNAME> 
 */

define('APPNAME', 'system');
define('RPATH_BASE', dirname(__FILE__) );
require_once (RPATH_BASE.'/../lib/base.php');

$name =isset($argv[1])?$argv[1]:'aitv';

$res = t2t($name);
if (!$res) {
	exit("convert theme to TPL failed");
}

echo "OK";


function t2t($name)
{
	if (!$name) {
		rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "no name!");
		return false;
	}
		
	$dir = RPATH_THEME.DS.$name;
	if (!is_dir($dir)) {
		rlog(RC_LOG_ERROR, __FILE__, __LINE__, __FUNCTION__, "no TPL dir '$dir'!");
		return false;
	}
	
	$tdir = RPATH_TEMPLATES.DS.$name;
	if (!is_dir($tdir))
		mkdir($tdir);

	//清理缓存
	$file = $tdir.DS.'catalogdb.php';
	file_exists($file) && unlink($file);
	$file = $tdir.DS.'contentdb.php';
	file_exists($file) && unlink($file);

	$modules = array();
			
	$fdb = s_readdir($dir, "files");
	foreach ($fdb as $key=>$v) {
		
		$data = s_read($dir.DS.$v);
		
		$data = t2tData($name, $data, $modules);
		
		//include
		$data = t2tParseIncludeHeader($v, $tdir, $data);
		
		$tname = ($v == $name.'.html')?'index.htm':str_replace('html', 'htm', $v);
				
		$tplfile = $tdir.DS.$tname;	
		s_write($tplfile, $data);		
	}

	$_modules = implode(' ', $modules);
	
	//config
	$config = <<<EOT
<?php
\$appcfg = array (
	'name' => '$name',
	'title' => '$name',	
	'description' => '$name',	
	'dmodules'=>'$_modules',
	'version' => '0.1.0',
	'copyright' => 'RC',
	'website' => 'https://www.relaxcms.com',
	'uninstall' => true,
);
EOT;
	$cfgfile = $tdir.DS.'config.php';
	
	s_write($cfgfile, $config);	
	
	return true;
}


function t2tData($name, $data, &$modules=array())
{
	//catalog
	t2tParseCatalog($name, $data);
	//content
	t2tParseContent($name, $data);
	
	
	//title
	//Ì½²â img
	$matches = array();
	$res = preg_match_all("/<title>(.+)<\/title>/i", $data, $matches);
	if ($res && count($matches[1]) == 1) {
		$data = str_replace($matches[0], "<title>\$scf[title] \$_catalog_title \$_content_title</title><meta name=\"_dstroot\" content=\"\$_dstroot\">", $data);
	}  
	//img src
	$matches = array();
	$res = preg_match_all("/src\b\s*=\s*[\s]*[\'\"]?([^\'\"]*)[\'\"]?/i", $data, $matches);
	if ($res && count($matches[1]) > 0) {
		$olddb = array();
		$newdb = array();
		for($i=0; $i<count($matches[1]); $i++) {
			$src = $old = $matches[1][$i];
			$ext = s_fileext($src);
			switch($ext) {
				case 'js':
					break;
				default:
					$file = RPATH_THEME.DS.$name.DS.$src;
					if (file_exists($file)) {
						$dname = dirname($src);
						if (is_start_with($dname,'../../static/')) {
							$src = '$_dstroot/'.str_replace($dname, '', $src);	
						} else {
							//copy
							$thedir = RPATH_STATIC_THEMES.DS.$name.DS.$dname;
							!is_dir($thedir) && s_mkdir($thedir);
							copy($file, RPATH_STATIC_THEMES.DS.$name.DS.$src);				
							$src = '$_theroot/'.$name.'/'.$src;		
						}						
					} else {
						$src = '$_dstroot/img/no.png';	
					}					
					break;
			}
			if (!in_array($src, $newdb)) {
				$olddb[] = $old;			
				$newdb[] = $src;	
			}
		}
		$data = str_replace($olddb, $newdb, $data);
	}
	//url : url("img/banner2.jpg")
	$matches = array();
	$res = preg_match_all("/url\(\s*[\'\"]?([^\'\"]*)[\'\"]?\)/i", $data, $matches);
	if ($res && count($matches[1]) > 0) {
		$olddb = array();
		$newdb = array();
		for($i=0; $i<count($matches[1]); $i++) {
			$src = $old = $matches[1][$i];
			$file = RPATH_THEME.DS.$name.DS.$src;
			if (file_exists($file)) {
				//$src = '$_theroot/'.$name.'/'.$src;	

				$dname = dirname($src);
				if (is_start_with($dname,'../../static/')) {
					$src = '$_dstroot/'.str_replace($dname, '', $src);	
				} else {
					//copy
					$thedir = RPATH_STATIC_THEMES.DS.$name.DS.$dname;
					!is_dir($thedir) && s_mkdir($thedir);
					copy($file, RPATH_STATIC_THEMES.DS.$name.DS.$src);				
					$src = '$_theroot/'.$name.'/'.$src;		
				}
			} else {
				$src = '$_dstroot/img/no.png';	
			}					
	
			if (!in_array($src, $newdb)) {
				$olddb[] = $old;			
				$newdb[] = $src;	
			}
		}
		
		$data = str_replace($olddb, $newdb, $data);
	}
	
	 
	//CSS
	$matches = array();
	$res = preg_match_all("/<!--\s*[\s]*BEGIN CSS\s*([\w+\s*=('|\"|?)[^(\1)].+(\1)?]*)?\s*[\s]*-->(.+)<!--\s*[\s]*END CSS\s*[\s]*-->/isU", $data, $matches);
	if ($res && count($matches[0]) == 1) {
		$_name = $name;
		$nr = count($matches);
		if ($nr > 1 && $matches[1][0]) {
			$args = attr2array2(strtolower($matches[1][0]));
			foreach ($args as $k2 => $v2) {
				if ($k2 == 'name') {
					$_name = trim($v2);
					break;
				}
			}
		}
		$cssinclude_data = '<link href="$_dstroot/css/'.$_name.'.css" rel="stylesheet">';
		$cssinclude_data .= '<!--# foreach($cssdb as $key=>$v) { #-->';
		$cssinclude_data .= '<link href="$v" rel="stylesheet" type="text/css" id="$key" />';
		$cssinclude_data .= '<!--# } #-->';
		$data = str_replace($matches[$nr-1], $cssinclude_data, $data);
	} 

	//THEMECSS
	$matches = array();
	$res = preg_match_all("/<!--\s*[\s]*BEGIN THEMECSS\s*[\s]*-->(.+)<!--\s*[\s]*END THEMECSS\s*[\s]*-->/isU", $data, $matches);
	if ($res && count($matches[1]) == 1) {
		$cssinclude_data = '<link href="$_dstroot/css/'.$name.'_theme_$scf[theme].css" rel="stylesheet">';
		$data = str_replace($matches[1], $cssinclude_data, $data);
	} 
	
	
	//JS
	$matches = array();
	$res = preg_match_all("/<!--\s*[\s]*BEGIN JS\s*([\w+\s*=('|\"|?)[^(\1)].+(\1)?]*)?\s*[\s]*-->(.+)<!--\s*[\s]*END JS\s*[\s]*-->/isU", $data, $matches);
	if ($res && count($matches[1]) == 1) {
		$_name = $name;
		$nr = count($matches);
		if ($nr > 1 && $matches[1][0]) {
			$args = attr2array2(strtolower($matches[1][0]));
			foreach ($args as $k2 => $v2) {
				if ($k2 == 'name') {
					$_name = trim($v2);
					break;
				}
			}
		}

		$jsinclude_data = '$sys_JS_G';
		$jsinclude_data .= '<script src="$_dstroot/js/'.$_name.'.js" type="text/javascript"></script>';
		$jsinclude_data .= '<!--# foreach($jsdb as $key=>$v) { #-->';
		$jsinclude_data .= '<script src="$v" type="text/javascript" id="$key"></script>';
		$jsinclude_data .= '<!--# } #-->';

		$data = str_replace($matches[$nr-1], $jsinclude_data, $data);
	}  
	
	//js
	$data = str_replace("../../static/", '$_dstroot/', $data);

	//index.html
	$data = str_replace("index.html", '$_webroot', $data);

	
	//navbar
	$data = t2tParseNavbar($data);
	//module
	$data = t2tParseModule($name, $data, $modules);
	//var
	$data = t2tParseVar($data);

	
	return $data;
}

/*
 Array
(
    [0] => Array
        (
            [0] => catalog cid=1>特色</a>
            [1] => catalog cid=2>下载</a>
            [2] => catalog cid=3>联系</a>
            [3] => catalog cid=1 description=1 >匠心设计，简木易用，基础框架<em class="blue">一键安装</em>
        )

    [1] => Array
        (
            [0] => cid=1
            [1] => cid=2
            [2] => cid=3
            [3] => cid=1 description=1
        )

    [2] => Array
        (
            [0] =>
            [1] =>
            [2] =>
            [3] =>
        )

    [3] => Array
        (
            [0] => 特色
            [1] => 下载
            [2] => 联系
            [3] => 匠心设计，简木易用，基础框架<em class="blue">一键安装
        )

    [4] => Array
        (
            [0] => a
            [1] => a
            [2] => a
            [3] => em
        )

)

*/
function t2tParseContent($tplname, $data)
{
	//rlog(RC_LOG_DEBUG, __FUNCTION__, $name, $data);
	
	$matches = array();	
	$_content = stripslashes($data);
	$res = preg_match_all("/content\s+([\w+\s*=('|\"|?)[^(\1)].+(\1)?]*)?\s*[\s]*>(.+)<\/(\w+)>/isU", $_content, $matches);
	if (!$res) {
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "no match tag!");
		return false;
	}

	$file = RPATH_TEMPLATES.DS.$tplname.DS.'contentdb.php';
	$contentdb = get_cache_array('contentdb', $file);

	$nr = count($matches[0]);
	for ($i=0; $i<$nr; $i++) {
		$val = $matches[3][$i];
		$attr = $matches[1][$i];

		$attrdb = attr2array2($attr);
		$params = $attrdb;
		$name = $val;

		$tid = isset($attrdb['tid'])?$attrdb['tid']:0;
		

		$desc = false;
		if (isset($attrdb['description']) && $attrdb['description']) {
			$tid = $attrdb['description']; 
			$desc = true;
		}

		if (isset($attrdb['desc']) && $attrdb['desc']) {
			$tid = $attrdb['desc']; 
			$desc = true;			
		} 

		$photo = false;
		if (isset($attrdb['photo']) && $attrdb['photo']) {
			$tid = $attrdb['photo']; 
			$photo = true;
		}
		if ($tid == 0) {
			rlog(RC_LOG_DEBUG, __FUNCTION__, "UNKNOWN content!");			
			continue;
		}

		foreach ($contentdb as $key => $v) {
			if ($tid > 0 && $tid == $v['tid']) {
				$params = $v;
				$name = $key;
				break;
			}
		}

		if ($tid > 0 && $desc) {
			$params['description'] = $val;	
		}

		if ($tid > 0 && $photo) {
			$params['photo'] = $attrdb['src'];	
		}


		$params['name'] = $name;
		$contentdb[$name] = $params;
	}

	rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $contentdb);
	cache_array('contentdb', $contentdb, $file);
}


function t2tParseCatalog($tplname, $data)
{
	//rlog(RC_LOG_DEBUG, __FUNCTION__, $name, $data);
	
	$matches = array();	
	$_content = stripslashes($data);
	$res = preg_match_all("/catalog\s+([\w+\s*=('|\"|?)[^(\1)].+(\1)?]*)?\s*[\s]*>(.+)<\/(\w+)>/isU", $_content, $matches);
	if (!$res) {
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "no match tag!");
		return false;
	}

	$file = RPATH_TEMPLATES.DS.$tplname.DS.'catalogdb.php';
	$catalogdb = get_cache_array('catalogdb', $file);
	$nr = count($matches[0]);
	for ($i=0; $i<$nr; $i++) {
		$val = $matches[3][$i];
		$attr = $matches[1][$i];

		$attrdb = attr2array2($attr);
		$params = $attrdb;
		$name = $val;

		$cid = isset($attrdb['cid'])?$attrdb['cid']:0;

		$desc = false;
		if (isset($attrdb['description']) && $attrdb['description']) {
			$cid = $attrdb['description']; 
			$desc = true;
		}

		if (isset($attrdb['desc']) && $attrdb['desc']) {
			$cid = $attrdb['desc']; 
			$desc = true;			
		}


		foreach ($catalogdb as $key => $v) {
			if ($cid > 0 && $cid == $v['cid']) {
				$params = $v;
				$name = $key;
				break;
			}
		}

		if ($cid > 0 && $desc) {//description
			$params['description'] = $val;
		} 

		//href
		if (isset($attrdb['href'])) {
			$params['linkurl'] = $attrdb['href'];
			$pos = strrpos($attrdb['href'], '#');
			if ($pos !== false) {
				$params['target'] = substr($attrdb['href'], $pos);
			} 			
		}
		if (isset($attrdb['class'])) {
			$params['class'] = $attrdb['class'];
		}
		if (isset($attrdb['target'])) {
			$params['target'] = $attrdb['target'];
		}

		$params['name'] = $name;
		$catalogdb[$name] = $params;
	}

	rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $catalogdb);

	cache_array('catalogdb', $catalogdb, $file);

}



function t2tParseIncludeHeader($name, $tdir, $data)
{
	$is_index = $name == 'index.html'?true:false;

	//<!-- BEGIN PAGECONTENT -->
	//<!-- END PAGECONTENT -->
	$matches = array();
	$res = preg_match_all("/<!--\s*[\s]*BEGIN PAGECONTENT\s*([\w+\s*=('|\"|?)[^(\1)].+(\1)?]*)?\s*[\s]*-->(.+)<!--\s*[\s]*END PAGECONTENT\s*[\s]*-->/isU", $data, $matches);
	

	
	if ($res && count($matches[1]) == 1) {
		$prefix = 'i';
		$isdefault = false;
		$nr = count($matches);
		if ($nr > 1 && $matches[1][0]) {
			$args = attr2array2(strtolower($matches[1][0]));
			foreach ($args as $k2 => $v2) {
				if ($k2 == 'prefix') {
					$prefix = trim($v2);
				}
				if ($k2 == 'default' && intval($v2) == 1) {
					$isdefault = true;
				}
			}
		}

		$head = $prefix.'head';
		$foot = $prefix.'foot';
		
		$pagecontent_data = $matches[3][0];
		$len = strlen($pagecontent_data);
		
		$pos = strpos($data, $pagecontent_data);
		
		$head_data = substr($data, 0, $pos);
		$foot_data = substr($data, $pos+$len);
		
		//header.htm
		$file = $tdir.DS."$head.htm";
		if ((!file_exists($file) || $is_index) && !$isdefault) {
			s_write($file, $head_data);
		}
		
		//footer.htm
		$file = $tdir.DS."$foot.htm";
		if ((!file_exists($file) || $is_index) && !$isdefault) {
			s_write($file, $foot_data);
		}
		
		//replace
		$data = str_replace($head_data, '<rdoc:include file="'.$head.'.htm" />', $data);
		$data = str_replace($foot_data, '<rdoc:include file="'.$foot.'.htm" />', $data);
	}
	
	return $data;
	
	
}

function t2tParseVar($data)
{
	//BEGIN SYSVAR MYPROFILE

	//CSS
	$matches = array();
	$res = preg_match_all("/<!--\s*[\s]*BEGIN SYSVAR (\w+)\s*[\s]*-->(.+)<!--\s*[\s]*END SYSVAR\s*[\s]*-->/isU", $data, $matches);

	if ($res && count($matches[1]) > 0) {
		$nr = count($matches[1]);

		$new = array();
		for($i=0; $i<$nr; $i++) {
			$name = strtolower($matches[1][$i]);
			$new[] = "\$sys_$name";
		}
		
		//
		$data = str_replace($matches[0], $new, $data);
	}  
	return $data;
}

/*
Array
(
    [0] => Array
        (
            [0] => <i class="fa  fa-video-camera"></i>
        )

    [1] => Array
        (
            [0] =>  class="fa  fa-video-camera"
        )

    [2] => Array
        (
            [0] =>
        )

)
*/
function t2tParseModuleSingleMainMenuName(&$params, $innerData)
{
	$name = trim($innerData);

	//rlog(RC_LOG_DEBUG, __FUNCTION__, $name);

	$matches = array();	
	$_content = stripslashes($innerData);
	$res = preg_match_all("/<i\b\s*([\w+\s*=('|\"|?)[^(\1)].+(\1)?]*)?\s*[\s]*>.*<\/i>/isU", $_content, $matches);
	
	//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $matches);
	$nr = count($matches[0]);
	if ($nr >= 1)	{
		for($i=0; $i<$nr; $i++) {
			$args = attr2array2(strtolower($matches[1][$i]));
			foreach ($args as $k2 => $v2) {
				if ($k2 == 'class') {
					$params['icon'] = trim($v2);
					break;
				}
			}
		}
		$name = trim(str_replace($matches[0], '', $name));
	}
	$params['name'] = $name;
}


/*
 <ul class="nav navbar-nav mainmenu">
    <li class="nav-index ">
        <a href="#">首 页</a>
    </li>
    <li class="nav-feature ">
        <a href="#feature" class="pageScroll" >特色</a>
    </li>
    <li class="nav-download ">
        <a href="#download" class="pageScroll">下载</a>
    </li>
    <li class="nav-contact">
        <a href="#contact" class="pageScroll">联系</a>
    </li>
    <li>
        <span class="nav-icon-line"></span>
    </li>
</ul>

 t2tParseModuleSingleMainMenu: Array
(
    [0] => Array
        (
            [0] => <a href="#">首 页</a>
            [1] => <a href="#feature" class="pageScroll" >特色</a>
            [2] => <a href="#download" class="pageScroll">下载</a>
            [3] => <a href="#contact" class="pageScroll">联系</a>
        )

    [1] => Array
        (
            [0] =>  href="#"
            [1] =>  href="#feature" class="pageScroll"
            [2] =>  href="#download" class="pageScroll"
            [3] =>  href="#contact" class="pageScroll"
        )

    [2] => Array
        (
            [0] =>
            [1] =>
            [2] =>
            [3] =>
        )

    [3] => Array
        (
            [0] => 首 页
            [1] => 特色
            [2] => 下载
            [3] => 联系
        )

)


 t2tParseModuleSingleMainMenu: Array
(
    [0] => Array
        (
            [0] => <a href="index.html" class="dropdown-toggle" >
                                                                <i class="fa fa-home"></i>
                                                                首页
                                                            </a>
            [1] => <a href="list.html" class="dropdown-toggle" >
                                                                <i class="fa fa-film"></i>
                                                                点播
                                                                                </a>
            [2] => <a href="live.html" class="dropdown-toggle" >
                                                           <i class="fa  fa-video-camera"></i>
                                                                直播
                                                        </a>
        )

    [1] => Array
        (
            [0] =>  href="index.html" class="dropdown-toggle"
            [1] =>  href="list.html" class="dropdown-toggle"
            [2] =>  href="live.html" class="dropdown-toggle"
        )

    [2] => Array
        (
            [0] =>
            [1] =>
            [2] =>
        )

    [3] => Array
        (
            [0] =>
                                                                <i class="fa fa-home"></i>
                                                                首页

            [1] =>
                                                                <i class="fa fa-film"></i>
                                                                点播

            [2] =>
                                                           <i class="fa  fa-video-camera"></i>
                                                                直播

        )

)

*/
function t2tParseModuleSingleMainMenu($dirname, $name, $innerData)
{
	rlog(RC_LOG_DEBUG, __FUNCTION__, $name, $innerData);

	//t
	$replace = array();
	$matches = array();	
	$_content = stripslashes($innerData);
	$res = preg_match_all("/<a\b\s*([\w+\s*=('|\"|?)[^(\1)].+(\1)?]*)?\s*[\s]*>(.+)<\/a>/isU", $_content, $matches);
	if (!$res) {
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "no match tag!");
		return false;
	}

	rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $matches);

	$nr = count($matches[0]);
	$cdb = array();		
		
	for($i=0; $i<$nr; $i++) {
		$params = attr2array2($matches[1][$i]);
		t2tParseModuleSingleMainMenuName($params, $matches[3][$i]);
		$cdb[] = $params;
	}

	//rlog($cdb);

	//存储
	$file = RPATH_TEMPLATES.DS.$dirname.DS.'catalog.php';
	cache_array('catalog', $cdb, $file);


}



function getTags( $dom, $tagName, $attrName, $attrValue ){
    $html = '';
    $domxpath = new DOMXPath($dom);
    $newDom = new DOMDocument;
    $newDom->formatOutput = true;

    $filtered = $domxpath->query("//$tagName" . '[@' . $attrName . "='$attrValue']");
    // $filtered =  $domxpath->query('//div[@class="className"]');
    // '//' when you don't know 'absolute' path

    // since above returns DomNodeList Object
    // I use following routine to convert it to string(html); copied it from someone's post in this site. Thank you.
    $i = 0;
    while( $myItem = $filtered->item($i++) ){
        $node = $newDom->importNode( $myItem, true );    // import node
        $newDom->appendChild($node);                    // append node
    }
    $html = $newDom->saveHTML();
    return $html;
}



/** 
 * 解析动画模块 

<div class="feature tc" id="feature">
        <div class="c-title animated fadeInUp wow">特点</div>
        <div class="c-sub-title animated fadeInDown wow">匠心设计，简木易用，基础框架<em class="blue">一键安装</em>，发布平台包含丰富的应用与模板在线即装即用，构建新业务线下线上同服务</div>


        <div class="row grid">
          <div class="col-sm-3 animated fadeInLeft wow">
              <h2>分层架构</h2>
              <p><i class="icon"><img src="$_theroot/relaxcms5/img/arch.png" /></i></p>
              <p>系统采用分层架构设计，从下至上可分为：系统层、服务层、架构层及应用层。</p>
          </div>

          <div class="col-sm-3 animated fadeInDown wow">
              <h2>CMT设计</h2>
              <p><i class="icon"><img src="$_theroot/relaxcms5/img/CMT.png" /></i></p>
              <p>框架采用CMT（Model模型、 Component组件、 Template模板）设计，数据、控制与UI显示分离，类MVC，逻辑清晰，易于扩展，并可在框架内置CMT基础上继承。</p>
          </div>
          <div class="col-sm-3 animated fadeInDown wow">
              <h2>动态内嵌模块</h2>
              <p><i class="icon"><img src="$_theroot/relaxcms5/img/module.png" /> </i></p>
              <p>支持功能模块嵌入到模板中独立运行调用并在模板不同的布局块内呈现，支持模块参数动配置。</p>
          </div>
          <div class="col-sm-3 animated fadeInRight wow">
              <h2>更安全</h2>
              <p><i class="icon"><img src="$_theroot/relaxcms5/img/security.jpg" /></i></p>
              <p>支持敏感数据安全加密存储，如：数据库配置，管理员配置加密安全存储; PHP源代码保护以及存储加密。</p>
          </div>
      </div>

        <div class="row grid">
                <div class="col-sm-3 animated fadeInLeft wow">
                    <h2>多数据库</h2>
                    <p><i class="icon"><img src="$_theroot/relaxcms5/img/multidb.png" /></i></p>
                    <p>支持MYSQL、POSTGRESQL、SQLITE、MONGODB、MSSQL、ORACLE等数据库管理系统连接。</p>
                </div>
                <div class="col-sm-3 animated fadeInUp wow">
                    <h2>多语言</h2>
                    <p><i class="icon"><img src="$_theroot/relaxcms5/img/i18n.png" /></i></p>
                    <p>支持简体中文、繁体中文、英文等国际化多语言界面。</p>
                </div>
          <div class="col-sm-3 animated fadeInUp wow">
              <h2>多应用</h2>
              <p><i class="icon"><img src="$_theroot/relaxcms5/img/multiapp.png" /></i></p>
              <p>系统支持多应用功能扩展，支持应用在线即安即用，支持本地或远程应用安装、支持第三方应用安装。</p>
          </div>

                <div class="col-sm-3 animated fadeInRight wow">
                    <h2>多模板主题</h2>
                    <p><i class="icon"><img src="$_theroot/relaxcms5/img/responsive.png" /> </i></p>
                    <p>支持自适应不同大小屏访问的响应式模板下载、安装、升级及切换；支持一套模板多种风格主题，实现一键更新风格主题切换。</p>
                </div>
            </div>
</div>


 */
function t2tParseModuleSingleContent($dirname, $name, $innerData)
{
	rlog(RC_LOG_DEBUG, __FUNCTION__, $name, $innerData);
	
	$matches = array();	
	$_content = stripslashes($innerData);
	//$res = preg_match_all("/<(\w+)\b\s*([\w+\s*=('|\"|?)[^(\1)].+(\1)?]*)?\s*[\s]*>(.+)<\/(\w+)>/isU", $_content, $matches);
	$res = preg_match_all("/catalog([\w+\s*=('|\"|?)[^(\1)].+(\1)?]*)?\s*[\s]*>(.+)<\/(\w+)>/isU", $_content, $matches);

	//$res = preg_match_all("/content\s*[\s]*.*([\w+\s*=('|\"|?)[^(\1)].+(\1)?]*)?\s*[\s]*>(.+)<\/(\w+)>/isU", $_content, $matches);
	//var_dump($matches); exit;
	if (!$res) {
		rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, "no match tag!");
		return false;
	}
	

	rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $matches);
	
	//定位内容列表
	//$dom = new DOMDocument();
	//$dom->loadXML($innerData);
	//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $dd2->saveXML());

	//$xml = new SimpleXMLElement($innerData);
	//rlog($xml);
	//$tagName = 'div';
	//$attrName = 'id';
	//$attrValue = 'feature';

	//$html = getTags( $dom, $tagName, $attrName, $attrValue );

	//rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $html);

	$cdb = array();
	$nr = count($matches[0]);

	for($i=0; $i<$nr; $i++){

		$tagname = $matches[5][$i]; //tag Name
		$val = $matches[4][$i]; //tag Name
		$attrs = $matches[2][$i]; //tag Name
		
		$params = array();

		$params['tagname'] = $tagname;
		$params['attrs'] = $attrs;
		$params['value'] = $val;

		$cdb[] = $params;
	}

	rlog(RC_LOG_DEBUG, __FILE__, __LINE__, __FUNCTION__, $cdb);

}



function t2tParseModuleSingle($dirname, $name, $args, $innerData)
{
	//rlog(RC_LOG_DEBUG, __FUNCTION__, $name, $args, $innerData);
	/*switch($name){
		case 'mainmenu'://主菜单
			t2tParseModuleSingleMainMenu($dirname, $name, $innerData);
			break;
		case 'animated'://动画宫格
			//t2tParseModuleSingleContent($dirname, $name, $innerData);
			break;
		default:
			break;
	}*/
	$dir = RPATH_MODULES.DS.$name;
	$file = $dir.DS.$name.'.htm';
	if (!is_dir($dir)) {//模块不存在，创建一个
		s_mkdir($dir);
		s_write($file, $innerData) ;

$tpl_module = <<<EOT
<?php
class %sModule extends CModule
{
	function __construct(\$name, \$attribs)
	{
		parent::__construct(\$name, \$attribs);
	}
	
	function %sModule(\$name, \$attribs)
	{
		\$this->__construct(\$name, \$attribs);
	}
}
EOT;
		$cname = ucfirst($name);
		$module_data = sprintf($tpl_module, $cname, $cname);
		s_write($dir.DS.$name.'.php', $module_data);
	}
}

function t2tParseModule($dirname, $data, &$modules=array())
{
	//BEGIN MODULE MYPROFILE

	//CSS
	$matches = array();
	$res = preg_match_all("/<!--\s*[\s]*BEGIN MODULE (\w+)\b\s*([\w+\s*=('|\"|?)[^(\1)].+(\1)?]*)?\s*[\s]*-->(.+)<!--\s*[\s]*END MODULE (\w+)\s*[\s]*-->/isU", $data, $matches);
	//var_dump($matches);
	if ($res && count($matches[1]) > 0) {
		$nr = count($matches[1]);

		$new = array();
		for($i=0; $i<$nr; $i++) {
			$name = strtolower($matches[1][$i]);
			$args = attr2array2(strtolower($matches[2][$i]));
			$args = array2attr($args);

			$new[] = "<rdoc:include type='module' name='$name' $args />";


			//单个解析
			t2tParseModuleSingle($dirname, $name, $args, $matches[4][$i]);
			
			$modules[] = $name;
		}
		
		//
		$data = str_replace($matches[4], $new, $data);
	}  
	return $data;
}

function t2tParseNavbar($data)
{
	//BEGIN MODULE MYPROFILE

	//onepage
	return $data;
}