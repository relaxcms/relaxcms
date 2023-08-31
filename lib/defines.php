<?php

defined( 'RMAGIC' ) or die( 'Request Forbbiden' );

define( 'RPATH_CLASS',				RPATH_LIB.DS.'classes');
define( 'RPATH_DATABASE',			RPATH_LIB.DS.'database');
define( 'RPATH_SESSION',			RPATH_LIB.DS.'session');
define( 'RPATH_OAUTH',				RPATH_LIB.DS.'oauth');
define( 'RPATH_PAY',				RPATH_LIB.DS.'pay');
define( 'RPATH_CONFIGS',			RPATH_LIB.DS.'configs');
define( 'RPATH_MODELS',				RPATH_LIB.DS.'models');
define( 'RPATH_COMMON',				RPATH_LIB.DS.'common');
define( 'RPATH_COMPONENTS',			RPATH_LIB.DS.'components');
define( 'RPATH_I18N', 				RPATH_LIB.DS."i18n" );
define( 'RPATH_INCLUDES', 			RPATH_LIB.DS."includes" );


define( 'RPATH_APPS',				RPATH_ROOT.DS.'apps');
define( 'RPATH_MODULES',			RPATH_ROOT.DS.'modules');
define( 'RPATH_TEMPLATES',			RPATH_ROOT.DS.'templates');
define( 'RPATH_APPDIR',				RPATH_APPS.DS.APPNAME);
define( 'RPATH_FRONT',				RPATH_APPS.DS.'front');
define( 'RPATH_CONFIG', 			RPATH_ROOT.DS.'config');
define( 'RPATH_CONFIG_SSL', 		RPATH_CONFIG.DS.'ssl');
define( 'RPATH_SHELL',				RPATH_ROOT.DS.'bin');
define( 'RPATH_DOCUMENT',	 		RPATH_ROOT.DS.'docs' );

//default template dir
define( 'RPATH_TEMPLATE_DEFAULT',	RPATH_TEMPLATES.DS.'default');

//public name
$pdir = RPATH_BASE;
while(1) {
	if (is_dir($pdir.DS.'static') && is_file($pdir.DS.'static'.DS.'js'.DS.'admin.js')){
		break;		
	}
	if (is_dir($pdir.DS.'lib') && is_file($pdir.DS.'lib'.DS.'base.php')) {
		$pdir = RPATH_ROOT.DS.'public';//默认
		break;		
	}
	$pdir = dirname($pdir);
	if (!$pdir) {
		$pdir = RPATH_BASE;//未知
		break;
	}
}

define( 'RPATH_PUBLIC',				$pdir);
define( 'RPATH_DIST',				RPATH_PUBLIC);
define( 'RPATH_STATIC',				RPATH_PUBLIC.DS.'static');
define( 'RPATH_THEME',				RPATH_PUBLIC.DS.'themes');
define( 'RPATH_DATA',				RPATH_PUBLIC.DS.'data');


define( 'RPATH_CACHE',				RPATH_ROOT.DS.'cache');
define( 'RPATH_PREVIEW',			RPATH_CACHE.DS.'previews');
define( 'RPATH_TEMPLATE_CPL',		RPATH_CACHE.DS.'ctpl');
define( 'RPATH_TMPDIR',				RPATH_CACHE.DS.'tmp');
define( 'RPATH_CACHE_TABLE',		RPATH_CONFIG.DS.'tables');
define( 'RPATH_APPMODELS',			RPATH_APPDIR.DS.'models');
define( 'RPATH_APPCOMPONENTS',		RPATH_APPDIR.DS.'components');

define( 'RPATH_SUPPORTS',			RPATH_ROOT.DS.'supports');
define( 'RPATH_WEBDAV',				RPATH_SUPPORTS.DS.'webdav');
define( 'RPATH_PHPEXCEL',			RPATH_SUPPORTS.DS.'phpexcel');


define('FM_CJ', 1);
define('FM_DJ', -1);